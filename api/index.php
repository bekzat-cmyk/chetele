<?php
declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/zikr.php';

function respond(int $status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestBody(): array {
    $body = json_decode(file_get_contents('php://input'), true);
    return is_array($body) ? $body : [];
}

function cleanName($value): string {
    $name = is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value) ?? '') : '';
    return mb_substr($name, 0, 24, 'UTF-8');
}

function cleanLogin($value): string {
    $login = is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value) ?? '') : '';
    return mb_strtolower($login, 'UTF-8');
}

function validLogin($value): bool {
    return is_string($value) && preg_match('/^[\p{L}\p{M}\p{N}][\p{L}\p{M}\p{N}\s._\'-]{0,23}$/u', $value) === 1;
}

function validPassword($value): bool {
    return is_string($value) && mb_strlen($value, 'UTF-8') >= 1 && mb_strlen($value, 'UTF-8') <= 72;
}

function validDate(string $date): bool {
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function amount($value): int {
    $number = filter_var($value, FILTER_VALIDATE_INT);
    return $number !== false && $number > 0 ? min((int)$number, 99999) : 0;
}

function uuidV4(): string {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

function ensureAuthColumns(PDO $db): void {
    $columns = $db->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('login', $columns, true)) {
        $db->exec('ALTER TABLE users ADD COLUMN login VARCHAR(24) NULL UNIQUE AFTER display_name');
    }
    if (!in_array('pin_hash', $columns, true)) {
        $db->exec('ALTER TABLE users ADD COLUMN pin_hash VARCHAR(255) NULL AFTER login');
    }
}

function ensurePushTable(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS push_subscriptions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id CHAR(36) NOT NULL,
        endpoint TEXT NOT NULL,
        endpoint_hash CHAR(64) NOT NULL,
        p256dh VARCHAR(512) NOT NULL,
        auth VARCHAR(256) NOT NULL,
        timezone VARCHAR(64) NOT NULL DEFAULT \'Asia/Bishkek\',
        reminder_time CHAR(5) NOT NULL DEFAULT \'12:00\',
        reminder_settings TEXT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        last_sent_date DATE NULL,
        last_sent_marker CHAR(16) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_push_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY uq_push_endpoint (endpoint_hash),
        KEY idx_push_reminders (active, reminder_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $columns = $db->query('SHOW COLUMNS FROM push_subscriptions')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('last_sent_marker', $columns, true)) {
        $db->exec('ALTER TABLE push_subscriptions ADD COLUMN last_sent_marker CHAR(16) NULL AFTER last_sent_date');
    }
    if (!in_array('reminder_settings', $columns, true)) {
        $db->exec('ALTER TABLE push_subscriptions ADD COLUMN reminder_settings TEXT NULL AFTER reminder_time');
    }
}

function ensureEventsTables(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS events (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(120) NOT NULL,
        description TEXT NULL,
        metric VARCHAR(24) NOT NULL DEFAULT \'salawat\',
        goal BIGINT UNSIGNED NOT NULL DEFAULT 0,
        starts_on DATE NOT NULL,
        ends_on DATE NOT NULL,
        status VARCHAR(16) NOT NULL DEFAULT \'draft\',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_events_status_dates (status, starts_on, ends_on)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $eventColumns = $db->query('SHOW COLUMNS FROM events')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('mode', $eventColumns, true)) {
        $db->exec("ALTER TABLE events ADD COLUMN mode VARCHAR(24) NOT NULL DEFAULT 'common_goal' AFTER description");
    }
    if (!in_array('leaderboard_enabled', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN leaderboard_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER mode');
    }
    if (!in_array('results_visibility', $eventColumns, true)) {
        $db->exec("ALTER TABLE events ADD COLUMN results_visibility VARCHAR(16) NOT NULL DEFAULT 'names' AFTER leaderboard_enabled");
    }
    $db->exec('CREATE TABLE IF NOT EXISTS event_participants (
        event_id BIGINT UNSIGNED NOT NULL,
        user_id CHAR(36) NOT NULL,
        joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (event_id, user_id),
        CONSTRAINT fk_event_participant_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        CONSTRAINT fk_event_participant_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $participantColumns = $db->query('SHOW COLUMNS FROM event_participants')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('personal_target', $participantColumns, true)) {
        $db->exec('ALTER TABLE event_participants ADD COLUMN personal_target BIGINT UNSIGNED NULL AFTER joined_at');
    }
}

function ensureSessionsTable(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS user_sessions (
        token_hash CHAR(64) NOT NULL PRIMARY KEY,
        user_id CHAR(36) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_used_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        KEY idx_sessions_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function createSession(PDO $db, string $userId): string {
    $token = bin2hex(random_bytes(32));
    $statement = $db->prepare('INSERT INTO user_sessions (token_hash, user_id) VALUES (?, ?)');
    $statement->execute([hash('sha256', $token), $userId]);
    return $token;
}

function sessionToken(): string {
    $token = (string)($_SERVER['HTTP_X_AUTH_TOKEN'] ?? '');
    return preg_match('/^[a-f0-9]{64}$/', $token) === 1 ? $token : '';
}

/* Returns the user id of a valid session, or null. Sessions expire after
   180 days without use. */
function sessionUserId(PDO $db): ?string {
    $token = sessionToken();
    if ($token === '') return null;
    $statement = $db->prepare('SELECT user_id FROM user_sessions WHERE token_hash = ? AND last_used_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)');
    $statement->execute([hash('sha256', $token)]);
    $userId = $statement->fetchColumn();
    if ($userId === false) return null;
    $touch = $db->prepare('UPDATE user_sessions SET last_used_at = NOW() WHERE token_hash = ? AND last_used_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
    $touch->execute([hash('sha256', $token)]);
    return (string)$userId;
}

/* Every personal route must call this: the user id in the URL or body is only
   trusted when it matches the session token sent in X-Auth-Token. */
function requireUser(PDO $db, string $userId): void {
    $sessionUser = sessionUserId($db);
    if ($sessionUser === null) respond(401, ['error' => 'Сессия истекла. Войдите снова.', 'reason' => 'session_required']);
    if (!hash_equals($sessionUser, $userId)) respond(403, ['error' => 'Нет доступа к этому кабинету.']);
}

function ensureAdminSettingsTable(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS admin_settings (
        setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function eventMetricSql(string $metric, string $tasksColumn): string {
    if ($metric === 'quran_pages') return "CAST(JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.quran')) AS UNSIGNED)";
    if ($metric === 'dhikr') return "CAST(JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.tasbihat')) AS UNSIGNED)";
    if ($metric === 'tahajjud_count') return "CASE WHEN JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.tahajjud')) = 'yes' THEN 1 ELSE 0 END";
    return "CAST(JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.salawat')) AS UNSIGNED)";
}

function requireAdmin(array $config): void {
    $legacyKey = (string)($config['admin_key'] ?? '');
    $providedKey = (string)($_SERVER['HTTP_X_ADMIN_KEY'] ?? '');
    $login = (string)($_SERVER['HTTP_X_ADMIN_LOGIN'] ?? '');
    $password = (string)($_SERVER['HTTP_X_ADMIN_PASSWORD'] ?? '');
    $expectedLogin = (string)($config['admin_login'] ?? '');
    $expectedPassword = (string)($config['admin_password'] ?? '');
    /* There are no built-in defaults: without a configured strong password the
       admin API stays closed. */
    $credentialsConfigured = $expectedLogin !== '' && strlen($expectedPassword) >= 12 && $expectedPassword !== 'admin12345';
    if ($legacyKey === '' && !$credentialsConfigured) respond(503, ['error' => 'Админ-доступ не настроен: задайте admin_login и admin_password (от 12 символов) в api/config.php.']);
    $keyAllowed = $legacyKey !== '' && hash_equals($legacyKey, $providedKey);
    $credentialsAllowed = $credentialsConfigured && hash_equals($expectedLogin, $login) && hash_equals($expectedPassword, $password);
    if (!$keyAllowed && !$credentialsAllowed) respond(403, ['error' => 'Неверный логин или пароль администратора.']);
}

function validReminderTime($value): bool {
    return is_string($value) && preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $value) === 1;
}

function validTimezone($value): bool {
    return is_string($value) && in_array($value, timezone_identifiers_list(), true);
}

function reminderSettings($value): array {
    $defaults = ['slots' => [
        ['id' => 'start', 'label' => 'Начать путь', 'time' => '12:00', 'enabled' => true],
        ['id' => 'continue', 'label' => 'Продолжить', 'time' => '16:30', 'enabled' => true],
        ['id' => 'finish', 'label' => 'Подвести итог', 'time' => '20:00', 'enabled' => true],
    ], 'quiet' => ['enabled' => false, 'start' => '22:00', 'end' => '08:00']];
    if (!is_array($value)) return $defaults;
    $savedSlots = is_array($value['slots'] ?? null) ? $value['slots'] : [];
    foreach ($defaults['slots'] as &$slot) foreach ($savedSlots as $saved) if (($saved['id'] ?? '') === $slot['id']) {
        $slot['enabled'] = ($saved['enabled'] ?? true) !== false;
        if (validReminderTime($saved['time'] ?? null)) $slot['time'] = $saved['time'];
    }
    unset($slot);
    $quiet = is_array($value['quiet'] ?? null) ? $value['quiet'] : [];
    $defaults['quiet']['enabled'] = ($quiet['enabled'] ?? false) === true;
    if (validReminderTime($quiet['start'] ?? null)) $defaults['quiet']['start'] = $quiet['start'];
    if (validReminderTime($quiet['end'] ?? null)) $defaults['quiet']['end'] = $quiet['end'];
    return $defaults;
}

function createLogin(PDO $db): string {
    do {
        $login = 'niet' . random_int(100000, 999999);
        $check = $db->prepare('SELECT 1 FROM users WHERE login = ?');
        $check->execute([$login]);
    } while ($check->fetchColumn());
    return $login;
}

function normalizeEntry(array $input): array {
    $tasks = isset($input['tasks']) && is_array($input['tasks']) ? $input['tasks'] : [];
    $qaza = isset($input['qaza']) && is_array($input['qaza']) ? $input['qaza'] : [];
    $nafilInput = isset($tasks['nafil']) && is_array($tasks['nafil']) ? $tasks['nafil'] : [];
    $nafil = [];
    foreach (['asr', 'kuptan', 'avvabin', 'duha'] as $name) {
        $nafil[$name] = !empty($nafilInput[$name]);
    }
    /* Per-zikr counts from the Zikr counter; the totals themselves live in salawat and tasbihat. */
    $zikr = cleanZikrMap($tasks['zikr'] ?? []);
    $normalizedTasks = [
        'quran' => amount($tasks['quran'] ?? 0),
        'jauchan' => amount($tasks['jauchan'] ?? 0),
        'kitap' => amount($tasks['kitap'] ?? 0),
        'nafil' => $nafil,
        'tahajjud' => ($tasks['tahajjud'] ?? '') === 'yes' ? 'yes' : 'no',
        'salawat' => amount($tasks['salawat'] ?? 0),
        'tasbihat' => amount($tasks['tasbihat'] ?? 0),
        'zikr' => (object)$zikr,
    ];
    $normalizedQaza = [];
    foreach (['Багымдат', 'Пешим', 'Аср', 'Шам', 'Куптан'] as $name) {
        $normalizedQaza[$name] = amount($qaza[$name] ?? 0);
    }
    $completed = 0;
    foreach (['quran', 'jauchan', 'kitap', 'salawat', 'tasbihat'] as $name) {
        $completed += $normalizedTasks[$name] > 0 ? 1 : 0;
    }
    $completed += $normalizedTasks['tahajjud'] === 'yes' ? 1 : 0;
    $completed += count(array_filter($nafil));
    $completed += count(array_filter($normalizedQaza)) > 0 ? 1 : 0;
    return ['tasks' => $normalizedTasks, 'qaza' => $normalizedQaza, 'completed' => $completed, 'points' => $completed * 10];
}

/* This route deliberately runs before the database connection. It makes PHP
   runtime issues diagnosable without exposing any configuration values. */
if (trim((string)($_GET['path'] ?? ''), '/') === 'runtime') {
    respond(200, [
        'php_version' => PHP_VERSION,
        'pdo' => class_exists('PDO'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'curl' => function_exists('curl_init'),
        'openssl' => function_exists('openssl_pkey_new'),
        'ecdh' => function_exists('openssl_pkey_derive'),
    ]);
}

try {
    $configPath = __DIR__ . '/config.php';
    if (!is_file($configPath)) {
        respond(500, ['error' => 'Не найден серверный конфиг базы данных.', 'reason' => 'config_missing']);
    }
    $config = require $configPath;
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['database']);
    $db = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    ensureAuthColumns($db);
    ensurePushTable($db);
    ensureEventsTables($db);
    ensureAdminSettingsTable($db);
    ensureSessionsTable($db);

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = trim((string)($_GET['path'] ?? ''), '/');
    $parts = $path === '' ? [] : explode('/', $path);

    if ($method === 'GET' && $parts === ['health']) {
        $db->query('SELECT 1');
        respond(200, ['status' => 'ok']);
    }

    if ($method === 'POST' && $parts === ['users']) {
        $body = requestBody();
        $name = cleanName($body['displayName'] ?? '');
        $login = cleanLogin($body['login'] ?? '');
        $password = (string)($body['password'] ?? '');
        if ($name === '' || !validLogin($login) || !validPassword($password)) respond(400, ['error' => 'Введите имя и пароль.']);
        $id = uuidV4();
        $statement = $db->prepare('INSERT INTO users (id, display_name, login, pin_hash) VALUES (?, ?, ?, ?)');
        try {
            $statement->execute([$id, $name, $login, password_hash($password, PASSWORD_DEFAULT)]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') respond(409, ['error' => 'Это имя уже занято.']);
            throw $exception;
        }
        respond(201, ['id' => $id, 'displayName' => $name, 'login' => $login, 'token' => createSession($db, $id)]);
    }

    if ($method === 'PUT' && count($parts) === 2 && $parts[0] === 'users') {
        $userId = $parts[1];
        $body = requestBody();
        $name = cleanName($body['displayName'] ?? '');
        $login = cleanLogin($body['login'] ?? '');
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || $name === '' || !validLogin($login)) respond(400, ['error' => 'Некорректные данные пользователя.']);
        requireUser($db, $userId);
        $statement = $db->prepare('UPDATE users SET display_name = ? WHERE id = ? AND login = ?');
        $statement->execute([$name, $userId, $login]);
        if ($statement->rowCount() === 0) respond(404, ['error' => 'Пользователь не найден.']);
        respond(200, ['displayName' => $name]);
    }

    if ($method === 'POST' && $parts === ['auth', 'login']) {
        $body = requestBody();
        $login = cleanLogin($body['login'] ?? '');
        $password = (string)($body['password'] ?? $body['pin'] ?? '');
        if (!validLogin($login) || !validPassword($password)) respond(400, ['error' => 'Проверьте имя и пароль.']);
        $statement = $db->prepare('SELECT id, display_name AS displayName, login, pin_hash FROM users WHERE login = ? LIMIT 1');
        $statement->execute([$login]);
        $user = $statement->fetch();
        if (!$user || !$user['pin_hash'] || !password_verify($password, $user['pin_hash'])) respond(401, ['error' => 'Неверное имя или пароль.']);
        respond(200, ['id' => $user['id'], 'displayName' => $user['displayName'], 'login' => $user['login'], 'token' => createSession($db, $user['id'])]);
    }

    if ($method === 'DELETE' && $parts === ['auth', 'session']) {
        $token = sessionToken();
        if ($token !== '') {
            $statement = $db->prepare('DELETE FROM user_sessions WHERE token_hash = ?');
            $statement->execute([hash('sha256', $token)]);
        }
        respond(200, ['loggedOut' => true]);
    }

    if ($method === 'GET' && $parts === ['push', 'public-key']) {
        $vapid = is_array($config['vapid'] ?? null) ? $config['vapid'] : [];
        $publicKey = (string)($vapid['public_key'] ?? '');
        if ($publicKey === '') respond(503, ['error' => 'Web Push ещё не настроен на сервере.']);
        respond(200, ['publicKey' => $publicKey]);
    }

    if ($method === 'GET' && $parts === ['push', 'diagnostics']) {
        $vapid = is_array($config['vapid'] ?? null) ? $config['vapid'] : [];
        $publicKey = (string)($vapid['public_key'] ?? '');
        $privateKey = (string)($vapid['private_key'] ?? '');
        respond(200, [
            'status' => 'ok',
            'php_version' => PHP_VERSION,
            'curl' => function_exists('curl_init'),
            'openssl' => function_exists('openssl_pkey_new'),
            'ecdh' => function_exists('openssl_pkey_derive'),
            'vapid_public_key' => $publicKey !== '',
            'vapid_private_key' => $privateKey !== '',
        ]);
    }

    if ($method === 'GET' && $parts === ['push', 'status']) {
        $userId = (string)($_GET['userId'] ?? '');
        $login = strtolower(trim((string)($_GET['login'] ?? '')));
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || !validLogin($login)) respond(400, ['error' => 'Некорректный пользователь.']);
        requireUser($db, $userId);
        $statement = $db->prepare('SELECT p.reminder_settings FROM push_subscriptions p INNER JOIN users u ON u.id = p.user_id WHERE p.user_id = ? AND u.login = ? AND p.active = 1 ORDER BY p.updated_at DESC LIMIT 1');
        $statement->execute([$userId, $login]);
        $settings = $statement->fetchColumn();
        respond(200, ['enabled' => $settings !== false, 'settings' => reminderSettings($settings ? json_decode((string)$settings, true) : null)]);
    }

    if ($method === 'POST' && $parts === ['push', 'subscriptions']) {
        $body = requestBody();
        $userId = (string)($body['userId'] ?? '');
        $login = strtolower(trim((string)($body['login'] ?? '')));
        $subscription = is_array($body['subscription'] ?? null) ? $body['subscription'] : [];
        $keys = is_array($subscription['keys'] ?? null) ? $subscription['keys'] : [];
        $endpoint = trim((string)($subscription['endpoint'] ?? ''));
        $p256dh = trim((string)($keys['p256dh'] ?? ''));
        $auth = trim((string)($keys['auth'] ?? ''));
        $time = '12:00';
        $timezone = trim((string)($body['timezone'] ?? 'Asia/Bishkek'));
        $settings = reminderSettings($body['settings'] ?? null);
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || !validLogin($login) || strpos($endpoint, 'https://') !== 0 || strlen($endpoint) > 4096 || $p256dh === '' || $auth === '' || !validTimezone($timezone)) {
            respond(400, ['error' => 'Некорректная подписка на уведомления.']);
        }
        requireUser($db, $userId);
        $user = $db->prepare('SELECT 1 FROM users WHERE id = ? AND login = ?');
        $user->execute([$userId, $login]);
        if (!$user->fetchColumn()) respond(404, ['error' => 'Пользователь не найден.']);
        $statement = $db->prepare('INSERT INTO push_subscriptions (user_id, endpoint, endpoint_hash, p256dh, auth, timezone, reminder_time, reminder_settings, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), endpoint = VALUES(endpoint), p256dh = VALUES(p256dh), auth = VALUES(auth), timezone = VALUES(timezone), reminder_time = VALUES(reminder_time), reminder_settings = VALUES(reminder_settings), active = 1');
        $statement->execute([$userId, $endpoint, hash('sha256', $endpoint), $p256dh, $auth, $timezone, $time, json_encode($settings, JSON_UNESCAPED_UNICODE)]);
        respond(201, ['subscribed' => true, 'settings' => $settings]);
    }

    if ($method === 'DELETE' && $parts === ['push', 'subscriptions']) {
        $body = requestBody();
        $userId = (string)($body['userId'] ?? '');
        $login = strtolower(trim((string)($body['login'] ?? '')));
        $endpoint = trim((string)($body['endpoint'] ?? ''));
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || !validLogin($login) || ($endpoint !== '' && strpos($endpoint, 'https://') !== 0)) respond(400, ['error' => 'Некорректная подписка.']);
        requireUser($db, $userId);
        if ($endpoint === '') {
            /* A Home Screen app may no longer have its old browser subscription.
               Let the user still turn reminders off for this account. */
            $statement = $db->prepare('UPDATE push_subscriptions p INNER JOIN users u ON u.id = p.user_id SET p.active = 0 WHERE p.user_id = ? AND u.login = ?');
            $statement->execute([$userId, $login]);
        } else {
            $statement = $db->prepare('UPDATE push_subscriptions p INNER JOIN users u ON u.id = p.user_id SET p.active = 0 WHERE p.user_id = ? AND u.login = ? AND p.endpoint_hash = ?');
            $statement->execute([$userId, $login, hash('sha256', $endpoint)]);
        }
        respond(200, ['enabled' => false]);
    }

    if ($method === 'POST' && $parts === ['push', 'test']) {
        $body = requestBody();
        $userId = (string)($body['userId'] ?? '');
        $login = strtolower(trim((string)($body['login'] ?? '')));
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || !validLogin($login)) respond(400, ['error' => 'Некорректный пользователь.']);
        requireUser($db, $userId);
        $statement = $db->prepare('SELECT p.id, p.endpoint, p.p256dh, p.auth FROM push_subscriptions p INNER JOIN users u ON u.id = p.user_id WHERE p.user_id = ? AND u.login = ? AND p.active = 1 ORDER BY p.updated_at DESC LIMIT 1');
        $statement->execute([$userId, $login]);
        $subscription = $statement->fetch();
        if (!$subscription) respond(404, ['error' => 'На этом устройстве нет активной подписки.']);
        require_once __DIR__ . '/push.php';
        try {
            $result = sendWebPush(['endpoint' => $subscription['endpoint'], 'keys' => ['p256dh' => $subscription['p256dh'], 'auth' => $subscription['auth']]], ['title' => 'Напоминания включены', 'body' => 'Так будут приходить напоминания о делах дня ✦', 'url' => '/?open=deeds'], $config);
        } catch (Throwable $error) {
            error_log('Niet Web Push test error: ' . $error->getMessage());
            $reason = 'server_push_error';
            if (!function_exists('curl_init')) $reason = 'curl_missing';
            elseif (!function_exists('openssl_pkey_new')) $reason = 'openssl_missing';
            elseif (!function_exists('openssl_pkey_derive')) $reason = 'ecdh_missing';
            respond(500, ['error' => 'Сервер не смог отправить тестовое уведомление.', 'reason' => $reason]);
        }
        if (in_array($result['status'], [404, 410], true)) {
            $disable = $db->prepare('UPDATE push_subscriptions SET active = 0 WHERE id = ?');
            $disable->execute([$subscription['id']]);
        }
        if (!$result['ok']) respond(502, ['error' => 'Сервис уведомлений не принял тест.', 'reason' => 'push_gateway_rejected', 'status' => $result['status']]);
        respond(200, ['sent' => true]);
    }

    if ($method === 'GET' && count($parts) === 3 && $parts[0] === 'users' && $parts[2] === 'entries') {
        $userId = $parts[1];
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId)) respond(400, ['error' => 'Некорректный пользователь.']);
        requireUser($db, $userId);
        $statement = $db->prepare('SELECT entry_date AS date, tasks, qaza FROM daily_entries WHERE user_id = ? ORDER BY entry_date ASC');
        $statement->execute([$userId]);
        $entries = $statement->fetchAll();
        foreach ($entries as &$entry) {
            $entry['tasks'] = json_decode($entry['tasks'], true) ?: [];
            $entry['qaza'] = json_decode($entry['qaza'], true) ?: [];
        }
        unset($entry);
        respond(200, $entries);
    }

    /* Zikr counter taps: {"deltas": {"subhanallah": 3}} added to the stored counts
       in one transaction, so concurrent phones never lose each other's taps. */
    if ($method === 'POST' && count($parts) === 5 && $parts[0] === 'users' && $parts[2] === 'entries' && $parts[4] === 'zikr') {
        $userId = $parts[1];
        $date = $parts[3];
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || !validDate($date)) respond(400, ['error' => 'Некорректный запрос.']);
        requireUser($db, $userId);
        $deltas = requestBody()['deltas'] ?? null;
        if (!is_array($deltas) || !$deltas) respond(400, ['error' => 'Нет изменений счётчика.']);
        $db->beginTransaction();
        $db->prepare("INSERT IGNORE INTO daily_entries (user_id, entry_date, tasks, qaza, completed_count, points) VALUES (?, ?, '{}', '{}', 0, 0)")->execute([$userId, $date]);
        $row = $db->prepare('SELECT tasks, qaza FROM daily_entries WHERE user_id = ? AND entry_date = ? FOR UPDATE');
        $row->execute([$userId, $date]);
        $stored = $row->fetch() ?: ['tasks' => '{}', 'qaza' => '{}'];
        $tasks = json_decode((string)$stored['tasks'], true);
        $qaza = json_decode((string)$stored['qaza'], true);
        $tasks = is_array($tasks) ? $tasks : [];
        $zikr = cleanZikrMap($tasks['zikr'] ?? []);
        $tasks = withZikr($tasks, manualPart($tasks, $zikr), applyZikrDeltas($zikr, $deltas));
        $entry = normalizeEntry(['tasks' => $tasks, 'qaza' => is_array($qaza) ? $qaza : []]);
        $update = $db->prepare('UPDATE daily_entries SET tasks = ?, qaza = ?, completed_count = ?, points = ? WHERE user_id = ? AND entry_date = ?');
        $update->execute([json_encode($entry['tasks'], JSON_UNESCAPED_UNICODE), json_encode($entry['qaza'], JSON_UNESCAPED_UNICODE), $entry['completed'], $entry['points'], $userId, $date]);
        $db->commit();
        respond(200, $entry);
    }

    if (count($parts) === 4 && $parts[0] === 'users' && $parts[2] === 'entries') {
        [$ignored, $userId, $ignoredEntries, $date] = $parts;
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId) || !validDate($date)) respond(400, ['error' => 'Некорректный запрос.']);
        requireUser($db, $userId);

        if ($method === 'GET') {
            $statement = $db->prepare('SELECT tasks, qaza, completed_count AS completed, points FROM daily_entries WHERE user_id = ? AND entry_date = ?');
            $statement->execute([$userId, $date]);
            $entry = $statement->fetch();
            if (!$entry) respond(200, null);
            $entry['tasks'] = json_decode($entry['tasks'], true);
            $entry['qaza'] = json_decode($entry['qaza'], true);
            respond(200, $entry);
        }

        if ($method === 'PUT') {
            $checkUser = $db->prepare('SELECT 1 FROM users WHERE id = ?');
            $checkUser->execute([$userId]);
            if (!$checkUser->fetchColumn()) respond(404, ['error' => 'Пользователь не найден.']);
            $body = requestBody();
            $db->beginTransaction();
            if (($body['zikrSync'] ?? null) === 2) {
                /* Per-zikr counts belong to the zikr route below. The phone's day
                   totals are reduced to their manual part and the stored counts
                   are added back, so a stale sync cannot erase another phone's taps. */
                $stored = $db->prepare('SELECT tasks FROM daily_entries WHERE user_id = ? AND entry_date = ? FOR UPDATE');
                $stored->execute([$userId, $date]);
                $storedTasks = json_decode((string)($stored->fetchColumn() ?: '{}'), true);
                $storedZikr = cleanZikrMap(is_array($storedTasks) ? ($storedTasks['zikr'] ?? []) : []);
                $tasks = is_array($body['tasks'] ?? null) ? $body['tasks'] : [];
                $body['tasks'] = withZikr($tasks, manualPart($tasks, cleanZikrMap($tasks['zikr'] ?? [])), $storedZikr);
            }
            $entry = normalizeEntry($body);
            $statement = $db->prepare('INSERT INTO daily_entries (user_id, entry_date, tasks, qaza, completed_count, points) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE tasks = VALUES(tasks), qaza = VALUES(qaza), completed_count = VALUES(completed_count), points = VALUES(points)');
            $statement->execute([$userId, $date, json_encode($entry['tasks'], JSON_UNESCAPED_UNICODE), json_encode($entry['qaza'], JSON_UNESCAPED_UNICODE), $entry['completed'], $entry['points']]);
            $db->commit();
            respond(200, $entry);
        }
    }

    if ($method === 'GET' && $parts === ['events']) {
        /* Personal progress is shown only for the session owner. */
        $userId = (string)(sessionUserId($db) ?? '');
        $events = $db->query("SELECT * FROM events WHERE status = 'published' AND ends_on >= CURDATE() ORDER BY starts_on ASC, id DESC")->fetchAll();
        foreach ($events as &$event) {
            $metricSql = eventMetricSql((string)$event['metric'], 'e.tasks');
            $progress = $db->prepare("SELECT COALESCE(SUM({$metricSql}),0) FROM daily_entries e INNER JOIN event_participants p ON p.user_id=e.user_id AND p.event_id=? WHERE e.entry_date BETWEEN GREATEST(?, DATE(p.joined_at)) AND ?");
            $progress->execute([$event['id'], $event['starts_on'], $event['ends_on']]);
            $event['totalProgress'] = (int)$progress->fetchColumn();
            $count = $db->prepare('SELECT COUNT(*) FROM event_participants WHERE event_id = ?'); $count->execute([$event['id']]); $event['participants'] = (int)$count->fetchColumn();
            $event['joined'] = false; $event['myProgress'] = 0; $event['myRank'] = null; $event['personalTarget'] = null; $event['leaders'] = [];
            if (preg_match('/^[a-f0-9-]{36}$/i', $userId)) {
                $joined = $db->prepare('SELECT joined_at, personal_target FROM event_participants WHERE event_id=? AND user_id=?'); $joined->execute([$event['id'], $userId]); $participant=$joined->fetch();
                $event['joined'] = (bool)$participant;
                if ($participant) {
                    $mineSql = eventMetricSql((string)$event['metric'], 'tasks');
                    $mine = $db->prepare("SELECT COALESCE(SUM({$mineSql}),0) FROM daily_entries WHERE user_id=? AND entry_date BETWEEN GREATEST(?, DATE(?)) AND ?");
                    $mine->execute([$userId, $event['starts_on'], $participant['joined_at'], $event['ends_on']]);
                    $event['myProgress']=(int)$mine->fetchColumn(); $event['personalTarget']=$participant['personal_target'] === null ? null : (int)$participant['personal_target'];
                }
            }
            if (($event['mode'] ?? 'common_goal') === 'common_goal' && (int)($event['leaderboard_enabled'] ?? 1) === 1) {
                $leaderSql = eventMetricSql((string)$event['metric'], 'e.tasks');
                $leaders = $db->prepare("SELECT u.display_name AS name, COALESCE(SUM({$leaderSql}),0) AS progress FROM event_participants p INNER JOIN users u ON u.id=p.user_id LEFT JOIN daily_entries e ON e.user_id=p.user_id AND e.entry_date BETWEEN GREATEST(?, DATE(p.joined_at)) AND ? WHERE p.event_id=? GROUP BY p.user_id,u.display_name ORDER BY progress DESC,u.display_name ASC LIMIT 3");
                $leaders->execute([$event['starts_on'], $event['ends_on'], $event['id']]); $event['leaders']=$leaders->fetchAll();
                foreach ($event['leaders'] as $index => &$leader) {
                    if (($event['results_visibility'] ?? 'names') === 'anonymous') $leader['name']='Участник '.str_pad((string)($index+1),2,'0',STR_PAD_LEFT);
                    elseif (($event['results_visibility'] ?? 'names') === 'initials') { $parts=preg_split('/\s+/u',trim((string)$leader['name']))?:[]; $leader['name']=implode(' ',array_map(function($part){ return mb_substr($part,0,1,'UTF-8').'.'; },$parts)); }
                }
                unset($leader);
                if ($event['joined']) {
                    $rankSql = eventMetricSql((string)$event['metric'], 'e.tasks');
                    $rank = $db->prepare("SELECT 1 + COUNT(*) FROM (SELECT p.user_id, COALESCE(SUM({$rankSql}),0) AS progress FROM event_participants p LEFT JOIN daily_entries e ON e.user_id=p.user_id AND e.entry_date BETWEEN GREATEST(?, DATE(p.joined_at)) AND ? WHERE p.event_id=? GROUP BY p.user_id) ranked WHERE progress > ?");
                    $rank->execute([$event['starts_on'], $event['ends_on'], $event['id'], $event['myProgress']]); $event['myRank']=(int)$rank->fetchColumn();
                }
            }
        }
        unset($event); respond(200, $events);
    }

    if ($method === 'POST' && count($parts) === 3 && $parts[0] === 'events' && $parts[2] === 'join') {
        $eventId=(int)$parts[1]; $body=requestBody(); $userId=(string)($body['userId']??''); $login=strtolower(trim((string)($body['login']??'')));
        if ($eventId<1 || !preg_match('/^[a-f0-9-]{36}$/i',$userId) || !validLogin($login)) respond(400,['error'=>'Некорректные данные участия.']);
        requireUser($db, $userId);
        $event=$db->prepare("SELECT id,mode,goal FROM events WHERE id=? AND status='published' AND ends_on>=CURDATE()"); $event->execute([$eventId]); $eventData=$event->fetch(); if(!$eventData) respond(404,['error'=>'Событие недоступно.']);
        $user=$db->prepare('SELECT 1 FROM users WHERE id=? AND login=?'); $user->execute([$userId,$login]); if(!$user->fetchColumn()) respond(403,['error'=>'Пользователь не найден.']);
        $personalTarget = null;
        if (($eventData['mode'] ?? 'common_goal') === 'personal_path') { $personalTarget=amount($body['personalTarget'] ?? 0); if($personalTarget<1) respond(400,['error'=>'Укажите личную цель.']); }
        $join=$db->prepare('INSERT IGNORE INTO event_participants (event_id,user_id,personal_target) VALUES (?,?,?)'); $join->execute([$eventId,$userId,$personalTarget]); respond(201,['joined'=>true]);
    }

    if ($method === 'GET' && $parts === ['admin','events']) {
        requireAdmin($config);
        $events = $db->query("SELECT e.*,
            (SELECT COUNT(*) FROM event_participants p WHERE p.event_id=e.id) AS participant_count,
            (SELECT COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(d.tasks, '$.salawat')) AS UNSIGNED)),0)
             FROM event_participants p INNER JOIN daily_entries d ON d.user_id=p.user_id
             WHERE p.event_id=e.id AND d.entry_date BETWEEN GREATEST(e.starts_on, DATE(p.joined_at)) AND e.ends_on) AS total_progress
            FROM events e ORDER BY created_at DESC")->fetchAll();
        respond(200, $events);
    }

    if ($method === 'GET' && $parts === ['admin','users']) {
        requireAdmin($config);
        $users = $db->query("SELECT u.id,u.display_name,u.login,u.created_at,
            COUNT(DISTINCT p.event_id) AS event_count,COALESCE(SUM(e.points),0) AS points
            FROM users u LEFT JOIN event_participants p ON p.user_id=u.id
            LEFT JOIN daily_entries e ON e.user_id=u.id
            GROUP BY u.id,u.display_name,u.login,u.created_at ORDER BY u.created_at DESC")->fetchAll();
        respond(200, $users);
    }

    if ($method === 'GET' && $parts === ['admin','overview']) {
        requireAdmin($config);
        $users=(int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $activeEvents=(int)$db->query("SELECT COUNT(*) FROM events WHERE status='published' AND starts_on<=CURDATE() AND ends_on>=CURDATE()")->fetchColumn();
        $todayEntries=(int)$db->query('SELECT COUNT(*) FROM daily_entries WHERE entry_date=CURDATE()')->fetchColumn();
        $todayPoints=(int)$db->query('SELECT COALESCE(SUM(points),0) FROM daily_entries WHERE entry_date=CURDATE()')->fetchColumn();
        $newUsersPeriod=(int)$db->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)')->fetchColumn();
        $completedEvents=(int)$db->query("SELECT COUNT(*) FROM events WHERE status='published' AND ends_on<CURDATE()")->fetchColumn();
        $activity=$db->query('SELECT entry_date AS date, COUNT(*) AS entries FROM daily_entries WHERE entry_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY entry_date ORDER BY entry_date ASC')->fetchAll();
        $recentUsers=$db->query('SELECT display_name,created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();
        $recentEvents=$db->query("SELECT title,starts_on,ends_on,status FROM events ORDER BY created_at DESC LIMIT 4")->fetchAll();
        respond(200,['users'=>$users,'activeEvents'=>$activeEvents,'todayEntries'=>$todayEntries,'todayPoints'=>$todayPoints,'newUsersPeriod'=>$newUsersPeriod,'completedEvents'=>$completedEvents,'activity'=>$activity,'recentUsers'=>$recentUsers,'recentEvents'=>$recentEvents]);
    }

    if ($method === 'GET' && $parts === ['admin','settings']) {
        requireAdmin($config);
        $defaults=['timezone'=>'Asia/Bishkek','defaultLeaderboard'=>true,'defaultVisibility'=>'names','defaultModeration'=>false];
        $saved=$db->query('SELECT setting_key,setting_value FROM admin_settings')->fetchAll();
        foreach($saved as $row) if(array_key_exists($row['setting_key'],$defaults)) {
            $defaults[$row['setting_key']]=in_array($row['setting_key'],['defaultLeaderboard','defaultModeration'],true)?$row['setting_value']==='1':$row['setting_value'];
        }
        respond(200,$defaults);
    }

    if ($method === 'PUT' && $parts === ['admin','settings']) {
        requireAdmin($config); $body=requestBody();
        $settings=[
            'timezone'=>validTimezone($body['timezone']??null)?$body['timezone']:'Asia/Bishkek',
            'defaultLeaderboard'=>!empty($body['defaultLeaderboard'])?'1':'0',
            'defaultVisibility'=>in_array($body['defaultVisibility']??'', ['names','initials','anonymous'], true)?$body['defaultVisibility']:'names',
            'defaultModeration'=>!empty($body['defaultModeration'])?'1':'0',
        ];
        $statement=$db->prepare('INSERT INTO admin_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        foreach($settings as $key=>$value) $statement->execute([$key,$value]);
        respond(200,['saved'=>true]);
    }

    if ($method === 'DELETE' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'users') {
        requireAdmin($config); $userId=(string)$parts[2];
        if (!preg_match('/^[a-f0-9-]{36}$/i',$userId)) respond(400,['error'=>'Некорректный пользователь.']);
        $db->beginTransaction();
        try {
            $db->prepare('DELETE FROM push_subscriptions WHERE user_id=?')->execute([$userId]);
            $db->prepare('DELETE FROM event_participants WHERE user_id=?')->execute([$userId]);
            $db->prepare('DELETE FROM daily_entries WHERE user_id=?')->execute([$userId]);
            $statement=$db->prepare('DELETE FROM users WHERE id=?'); $statement->execute([$userId]);
            if ($statement->rowCount()===0) { $db->rollBack(); respond(404,['error'=>'Пользователь не найден.']); }
            $db->commit(); respond(200,['deleted'=>true]);
        } catch (Throwable $error) { if($db->inTransaction()) $db->rollBack(); throw $error; }
    }

    if ($method === 'POST' && $parts === ['admin','events']) {
        requireAdmin($config); $body=requestBody(); $title=trim((string)($body['title']??'')); $description=trim((string)($body['description']??'')); $goal=(int)($body['goal']??0); $starts=(string)($body['startsOn']??''); $ends=(string)($body['endsOn']??'');
        $metric=(string)($body['metric']??'salawat'); $mode=(string)($body['mode']??'common_goal'); $status=(string)($body['status']??'published');
        $leaderboardEnabled=($body['leaderboardEnabled']??true)===true?1:0; $resultsVisibility=(string)($body['resultsVisibility']??'names');
        if (!in_array($metric, ['salawat','dhikr','quran_pages','tahajjud_count'], true)) $metric='salawat';
        if (!in_array($mode, ['common_goal','personal_path'], true)) $mode='common_goal';
        if (!in_array($status, ['draft','published'], true)) $status='published';
        if (!in_array($resultsVisibility, ['names','initials','anonymous'], true)) $resultsVisibility='names';
        if($title==='' || mb_strlen($title)>120 || $goal<1 || !validDate($starts) || !validDate($ends) || $ends<$starts) respond(400,['error'=>'Проверьте название, цель и даты.']);
        $statement=$db->prepare("INSERT INTO events (title,description,mode,leaderboard_enabled,results_visibility,metric,goal,starts_on,ends_on,status) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $statement->execute([$title,mb_substr($description,0,800,'UTF-8'),$mode,$leaderboardEnabled,$resultsVisibility,$metric,$goal,$starts,$ends,$status]); respond(201,['id'=>(int)$db->lastInsertId()]);
    }

    if ($method === 'PUT' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'events') {
        requireAdmin($config); $eventId=(int)$parts[2]; $body=requestBody();
        $title=trim((string)($body['title']??'')); $description=trim((string)($body['description']??'')); $goal=(int)($body['goal']??0);
        $starts=(string)($body['startsOn']??''); $ends=(string)($body['endsOn']??'');
        $metric=(string)($body['metric']??'salawat'); $mode=(string)($body['mode']??'common_goal'); $status=(string)($body['status']??'published');
        $leaderboardEnabled=($body['leaderboardEnabled']??true)===true?1:0; $resultsVisibility=(string)($body['resultsVisibility']??'names');
        if ($eventId<1 || $title==='' || mb_strlen($title)>120 || $goal<1 || !validDate($starts) || !validDate($ends) || $ends<$starts) respond(400,['error'=>'Проверьте название, цель и даты.']);
        if (!in_array($metric, ['salawat','dhikr','quran_pages','tahajjud_count'], true)) $metric='salawat';
        if (!in_array($mode, ['common_goal','personal_path'], true)) $mode='common_goal';
        if (!in_array($status, ['draft','published'], true)) $status='published';
        if (!in_array($resultsVisibility, ['names','initials','anonymous'], true)) $resultsVisibility='names';
        $statement=$db->prepare('UPDATE events SET title=?,description=?,mode=?,leaderboard_enabled=?,results_visibility=?,metric=?,goal=?,starts_on=?,ends_on=?,status=? WHERE id=?');
        $statement->execute([$title,mb_substr($description,0,800,'UTF-8'),$mode,$leaderboardEnabled,$resultsVisibility,$metric,$goal,$starts,$ends,$status,$eventId]);
        /* MySQL returns 0 affected rows when the submitted values are identical.
           This is still a successful save; distinguish it from a missing event. */
        if ($statement->rowCount()===0) {
            $exists=$db->prepare('SELECT 1 FROM events WHERE id=?');
            $exists->execute([$eventId]);
            if (!$exists->fetchColumn()) respond(404,['error'=>'Событие не найдено.']);
        }
        respond(200,['updated'=>true]);
    }

    if ($method === 'DELETE' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'events') {
        requireAdmin($config); $eventId=(int)$parts[2]; if ($eventId<1) respond(400,['error'=>'Некорректное событие.']);
        $statement=$db->prepare('DELETE FROM events WHERE id=?'); $statement->execute([$eventId]);
        if ($statement->rowCount()===0) respond(404,['error'=>'Событие не найдено.']);
        respond(200,['deleted'=>true]);
    }

    if ($method === 'GET' && $parts === ['leaderboard']) {
        $limit = min(max((int)($_GET['limit'] ?? 50), 1), 100);
        /* User ids are never exposed publicly; the caller's own row is marked with "me". */
        $sessionUser = sessionUserId($db);
        /* period=week counts the last 7 days including today, period=month the calendar
           month so far; anything else is all time. Each row's "move" compares the place with
           the previous period (the 7 days before, last month, or all time up to a week ago). */
        $period = $_GET['period'] ?? '';
        if ($period === 'week') {
            $joinPeriod = ' AND e.entry_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)';
            $joinBefore = ' AND e.entry_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        } elseif ($period === 'month') {
            $joinPeriod = " AND e.entry_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
            $joinBefore = " AND e.entry_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01') AND e.entry_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        } else {
            $joinPeriod = '';
            $joinBefore = ' AND e.entry_date < DATE_SUB(CURDATE(), INTERVAL 6 DAY)';
        }
        $placesBefore = [];
        foreach ($db->query("SELECT u.id FROM users u LEFT JOIN daily_entries e ON e.user_id = u.id{$joinBefore} GROUP BY u.id, u.created_at ORDER BY COALESCE(SUM(e.points), 0) DESC, u.created_at ASC")->fetchAll() as $index => $row) {
            $placesBefore[(string)$row['id']] = $index + 1;
        }
        $move = function (string $id, int $place) use ($placesBefore): int { return isset($placesBefore[$id]) ? $placesBefore[$id] - $place : 0; };
        header('X-Total-Count: ' . count($placesBefore));
        $statement = $db->query("SELECT u.id, u.display_name AS name, COALESCE(SUM(e.points), 0) AS points FROM users u LEFT JOIN daily_entries e ON e.user_id = u.id{$joinPeriod} GROUP BY u.id, u.display_name, u.created_at ORDER BY points DESC, u.created_at ASC LIMIT {$limit}");
        $rows = [];
        $meListed = false;
        foreach ($statement->fetchAll() as $index => $row) {
            $isMe = $sessionUser !== null && hash_equals($sessionUser, (string)$row['id']);
            $meListed = $meListed || $isMe;
            $rows[] = ['name' => $row['name'], 'points' => (int)$row['points'], 'me' => $isMe, 'place' => $index + 1, 'move' => $move((string)$row['id'], $index + 1)];
        }
        /* The caller outside the top list still gets their own place, appended last. */
        if ($sessionUser !== null && !$meListed) {
            $mine = $db->prepare("SELECT u.display_name AS name, COALESCE(SUM(e.points), 0) AS points FROM users u LEFT JOIN daily_entries e ON e.user_id = u.id{$joinPeriod} WHERE u.id = ? GROUP BY u.id, u.display_name");
            $mine->execute([$sessionUser]);
            $me = $mine->fetch();
            if ($me) {
                $ahead = $db->prepare("SELECT COUNT(*) FROM (SELECT u.id, COALESCE(SUM(e.points), 0) AS points FROM users u LEFT JOIN daily_entries e ON e.user_id = u.id{$joinPeriod} GROUP BY u.id) ranked WHERE points > ?");
                $ahead->execute([(int)$me['points']]);
                $place = (int)$ahead->fetchColumn() + 1;
                $rows[] = ['name' => $me['name'], 'points' => (int)$me['points'], 'me' => true, 'place' => $place, 'move' => $move($sessionUser, $place)];
            }
        }
        respond(200, $rows);
    }

    respond(404, ['error' => 'Маршрут не найден.']);
} catch (Throwable $error) {
    error_log('Niet API error: ' . $error->getMessage());
    $message = $error->getMessage();
    $databaseCode = (string)$error->getCode();
    $reason = 'database_error';
    $details = [];
    if (stripos($message, 'Access denied') !== false || $databaseCode === '1045') {
        $reason = 'access_denied';
        if (preg_match("/@'([^']+)'/", $message, $matches)) {
            $details['mysql_sees_site_as'] = $matches[1];
        }
    } elseif (stripos($message, "doesn't exist") !== false || stripos($message, 'Base table or view not found') !== false) {
        $reason = 'schema_missing';
    } elseif (stripos($message, 'Unknown database') !== false || $databaseCode === '1049') {
        $reason = 'database_not_found';
    } elseif (stripos($message, "Class 'PDO' not found") !== false || stripos($message, 'Class "PDO" not found') !== false) {
        $reason = 'pdo_missing';
    } elseif (stripos($message, "could not find driver") !== false) {
        $reason = 'pdo_mysql_missing';
    } elseif (stripos($message, "Can't connect") !== false || stripos($message, 'Connection refused') !== false || in_array($databaseCode, ['2002','2003'], true)) {
        $reason = 'database_host_unreachable';
    }
    respond(500, array_merge(['error' => 'Ошибка подключения к базе данных.', 'reason' => $reason], $details));
}
