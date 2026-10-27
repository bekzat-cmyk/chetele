<?php
declare(strict_types=1);

/*
 * Minimal standards-based Web Push sender for PHP hosting.
 * Requires the usual PHP OpenSSL and cURL extensions; no Composer package is needed.
 */

function pushB64Encode(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function pushB64Decode(string $value): string {
    $value = strtr($value, '-_', '+/');
    $padding = strlen($value) % 4;
    if ($padding !== 0) $value .= str_repeat('=', 4 - $padding);
    $decoded = base64_decode($value, true);
    if ($decoded === false) throw new RuntimeException('Некорректный ключ Web Push.');
    return $decoded;
}

function pushPem(string $der, string $label): string {
    return "-----BEGIN {$label}-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END {$label}-----\n";
}

function pushPemDer(string $pem): string {
    $body = preg_replace('/-----(BEGIN|END) [^-]+-----|\s+/', '', $pem);
    $der = is_string($body) ? base64_decode($body, true) : false;
    if ($der === false) throw new RuntimeException('Не удалось прочитать ключ Web Push.');
    return $der;
}

function pushPrivateKey(string $raw) {
    if (strlen($raw) !== 32) throw new RuntimeException('Неверный приватный VAPID-ключ.');
    $der = "\x30\x31\x02\x01\x01\x04\x20" . $raw . "\xA0\x0A\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
    $key = openssl_pkey_get_private(pushPem($der, 'EC PRIVATE KEY'));
    if ($key === false) throw new RuntimeException('OpenSSL не смог открыть приватный VAPID-ключ.');
    return $key;
}

function pushPublicKey(string $raw) {
    if (strlen($raw) !== 65 || $raw[0] !== "\x04") throw new RuntimeException('Неверный публичный ключ подписки.');
    $algorithm = "\x30\x13\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
    $der = "\x30\x59" . $algorithm . "\x03\x42\x00" . $raw;
    $key = openssl_pkey_get_public(pushPem($der, 'PUBLIC KEY'));
    if ($key === false) throw new RuntimeException('OpenSSL не смог открыть публичный ключ подписки.');
    return $key;
}

function pushAsn1Length(string $value, int &$offset): int {
    $first = ord($value[$offset++]);
    if (($first & 0x80) === 0) return $first;
    $count = $first & 0x7f;
    if ($count === 0 || $count > 4 || $offset + $count > strlen($value)) throw new RuntimeException('Некорректная ECDSA-подпись.');
    $length = 0;
    for ($i = 0; $i < $count; $i++) $length = ($length << 8) | ord($value[$offset++]);
    return $length;
}

function pushDerSignatureToRaw(string $der): string {
    $offset = 0;
    if (($der[$offset++] ?? '') !== "\x30") throw new RuntimeException('Некорректная ECDSA-подпись.');
    pushAsn1Length($der, $offset);
    $numbers = [];
    for ($i = 0; $i < 2; $i++) {
        if (($der[$offset++] ?? '') !== "\x02") throw new RuntimeException('Некорректная ECDSA-подпись.');
        $length = pushAsn1Length($der, $offset);
        $number = substr($der, $offset, $length);
        $offset += $length;
        $number = ltrim($number, "\x00");
        if (strlen($number) > 32) throw new RuntimeException('Некорректная ECDSA-подпись.');
        $numbers[] = str_pad($number, 32, "\x00", STR_PAD_LEFT);
    }
    return $numbers[0] . $numbers[1];
}

function pushHkdfExpand(string $prk, string $info, int $length): string {
    $output = '';
    $previous = '';
    for ($counter = 1; strlen($output) < $length; $counter++) {
        $previous = hash_hmac('sha256', $previous . $info . chr($counter), $prk, true);
        $output .= $previous;
    }
    return substr($output, 0, $length);
}

function pushHkdf(string $salt, string $ikm, string $info, int $length): string {
    return pushHkdfExpand(hash_hmac('sha256', $ikm, $salt, true), $info, $length);
}

function pushVapidJwt(string $endpoint, array $vapid): array {
    $private = pushB64Decode((string)($vapid['private_key'] ?? ''));
    $public = pushB64Decode((string)($vapid['public_key'] ?? ''));
    if (strlen($public) !== 65 || $public[0] !== "\x04") throw new RuntimeException('Неверный публичный VAPID-ключ.');
    $parsed = parse_url($endpoint);
    if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) throw new RuntimeException('Некорректный endpoint подписки.');
    $port = isset($parsed['port']) ? ':' . (int)$parsed['port'] : '';
    $audience = $parsed['scheme'] . '://' . $parsed['host'] . $port;
    $header = pushB64Encode(json_encode(['typ' => 'JWT', 'alg' => 'ES256'], JSON_UNESCAPED_SLASHES));
    $payload = pushB64Encode(json_encode([
        'aud' => $audience,
        'exp' => time() + 3600,
        'sub' => (string)($vapid['subject'] ?? 'mailto:admin@example.com'),
    ], JSON_UNESCAPED_SLASHES));
    $input = $header . '.' . $payload;
    $signature = '';
    if (!openssl_sign($input, $signature, pushPrivateKey($private), OPENSSL_ALGO_SHA256)) throw new RuntimeException('Не удалось подписать VAPID-запрос.');
    return [$input . '.' . pushB64Encode(pushDerSignatureToRaw($signature)), pushB64Encode($public)];
}

function pushEncrypt(array $subscription, array $payload): string {
    $keys = is_array($subscription['keys'] ?? null) ? $subscription['keys'] : [];
    $receiverPublic = pushB64Decode((string)($keys['p256dh'] ?? ''));
    $authSecret = pushB64Decode((string)($keys['auth'] ?? ''));
    if (strlen($authSecret) < 16) throw new RuntimeException('Неверный ключ авторизации подписки.');

    $senderPrivate = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
    if ($senderPrivate === false) throw new RuntimeException('OpenSSL не поддерживает P-256 для Web Push.');
    $details = openssl_pkey_get_details($senderPrivate);
    if (!is_array($details) || empty($details['key'])) throw new RuntimeException('Не удалось получить временный ключ Web Push.');
    $senderPublic = substr(pushPemDer($details['key']), -65);
    if (strlen($senderPublic) !== 65 || $senderPublic[0] !== "\x04") throw new RuntimeException('Не удалось получить временный публичный ключ.');

    $sharedSecret = openssl_pkey_derive(pushPublicKey($receiverPublic), $senderPrivate, 32);
    if ($sharedSecret === false) throw new RuntimeException('Не удалось зашифровать Web Push.');
    $prk = hash_hmac('sha256', $sharedSecret, $authSecret, true);
    // RFC 8291: this is the first HKDF-Expand block, hence the trailing counter byte 0x01.
    $ikm = hash_hmac('sha256', "WebPush: info\x00" . $receiverPublic . $senderPublic . "\x01", $prk, true);
    $salt = random_bytes(16);
    $cek = pushHkdf($salt, $ikm, "Content-Encoding: aes128gcm\x00", 16);
    $nonce = pushHkdf($salt, $ikm, "Content-Encoding: nonce\x00", 12);
    $plaintext = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($plaintext)) throw new RuntimeException('Не удалось подготовить уведомление.');
    $tag = '';
    $ciphertext = openssl_encrypt($plaintext . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
    if ($ciphertext === false || strlen($tag) !== 16) throw new RuntimeException('Не удалось зашифровать уведомление.');
    return $salt . pack('N', 4096) . chr(65) . $senderPublic . $ciphertext . $tag;
}

function sendWebPush(array $subscription, array $payload, array $config): array {
    if (!function_exists('curl_init')) throw new RuntimeException('На хостинге не включено расширение PHP cURL.');
    $vapid = is_array($config['vapid'] ?? null) ? $config['vapid'] : [];
    $endpoint = (string)($subscription['endpoint'] ?? '');
    if (strpos($endpoint, 'https://') !== 0) throw new RuntimeException('Некорректный endpoint подписки.');
    [$jwt, $public] = pushVapidJwt($endpoint, $vapid);
    $body = pushEncrypt($subscription, $payload);
    $curl = curl_init($endpoint);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/octet-stream',
            'Content-Encoding: aes128gcm',
            'TTL: 86400',
            'Urgency: normal',
            'Authorization: vapid t=' . $jwt . ', k=' . $public,
        ],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return ['ok' => $response !== false && $status >= 200 && $status < 300, 'status' => $status, 'error' => $error];
}
