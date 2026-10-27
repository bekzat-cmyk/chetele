<?php
declare(strict_types=1);

/* Run this file once per minute from the hosting control-panel Cron scheduler. */

require_once __DIR__ . '/../api/push.php';

function cronConnect(array $config): PDO {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['database']);
    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

try {
    $config = require __DIR__ . '/../api/config.php';
    $db = cronConnect($config);
    $subscriptions = $db->query('SELECT id, user_id, endpoint, p256dh, auth, timezone, reminder_settings, last_sent_marker FROM push_subscriptions WHERE active = 1')->fetchAll();
    $utcNow = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $slotMessages = [
        'start' => 'Начните сегодняшний путь с одного небольшого дела ✦',
        'continue' => 'Найдите минуту для себя — продолжим сегодняшний путь?',
        'finish' => 'Как прошёл день? Отметьте то, что успели сделать.',
    ];
    $progress = $db->prepare('SELECT completed_count FROM daily_entries WHERE user_id = ? AND entry_date = ? LIMIT 1');
    $sent = 0;
    $failed = 0;

    foreach ($subscriptions as $subscription) {
        try {
            $localNow = $utcNow->setTimezone(new DateTimeZone($subscription['timezone']));
            $today = $localNow->format('Y-m-d');
            $time = $localNow->format('H:i');
            $defaults = ['slots' => [['id'=>'start','time'=>'12:00','enabled'=>true],['id'=>'continue','time'=>'16:30','enabled'=>true],['id'=>'finish','time'=>'20:00','enabled'=>true]], 'quiet'=>['enabled'=>false,'start'=>'22:00','end'=>'08:00']];
            $settings = json_decode((string)($subscription['reminder_settings'] ?? ''), true);
            if (!is_array($settings)) $settings = $defaults;
            $quiet = $settings['quiet'] ?? $defaults['quiet'];
            $isQuiet = !empty($quiet['enabled']) && (($quiet['start'] <= $quiet['end'] && $time >= $quiet['start'] && $time < $quiet['end']) || ($quiet['start'] > $quiet['end'] && ($time >= $quiet['start'] || $time < $quiet['end'])));
            if ($isQuiet) continue;
            $slot = null; foreach (($settings['slots'] ?? $defaults['slots']) as $candidate) if (($candidate['enabled'] ?? true) && ($candidate['time'] ?? '') === $time) { $slot = $candidate; break; }
            if (!$slot) continue;
            $marker = $today . '-' . substr((string)($slot['id'] ?? str_replace(':', '', $time)), 0, 4);
            if ($subscription['last_sent_marker'] === $marker) continue;
            $progress->execute([$subscription['user_id'], $today]);
            $completed = (int)($progress->fetchColumn() ?: 0);
            $slotId = $slot['id'] ?? 'start'; $body = $slotMessages[$slotId] ?? $slotMessages['start'];
            if ($slotId === 'continue' && $completed > 0) $body = "Уже {$completed} из 11 дел отмечено. Продолжим?";
            if ($slotId === 'finish' && $completed > 0) $body = "Сегодня отмечено {$completed} из 11 дел. Бережно подведите итог дня.";

            $result = sendWebPush([
                'endpoint' => $subscription['endpoint'],
                'keys' => ['p256dh' => $subscription['p256dh'], 'auth' => $subscription['auth']],
            ], [
                'title' => 'Chetele',
                'body' => $body,
                'url' => '/',
                'tag' => 'niet-daily-reminder',
                'actions' => [['action' => 'open', 'title' => 'Открыть дела']],
            ], $config);

            if ($result['ok']) {
                $update = $db->prepare('UPDATE push_subscriptions SET last_sent_date = ?, last_sent_marker = ? WHERE id = ?');
                $update->execute([$today, $marker, $subscription['id']]);
                $sent++;
            } elseif (in_array($result['status'], [404, 410], true)) {
                $disable = $db->prepare('UPDATE push_subscriptions SET active = 0 WHERE id = ?');
                $disable->execute([$subscription['id']]);
            } else {
                $failed++;
                error_log('Niet Web Push delivery failed: HTTP ' . $result['status'] . ' ' . $result['error']);
            }
        } catch (Throwable $error) {
            $failed++;
            error_log('Niet Web Push reminder error: ' . $error->getMessage());
        }
    }

    echo 'Niet reminders: sent=' . $sent . '; failed=' . $failed . PHP_EOL;
} catch (Throwable $error) {
    error_log('Niet reminder cron failed: ' . $error->getMessage());
    http_response_code(500);
    echo "Niet reminders: configuration error\n";
    exit(1);
}
