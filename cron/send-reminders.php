<?php
declare(strict_types=1);

/* Run this file once per minute from the hosting control-panel Cron scheduler. */

/* Each run leaves a short report beside this file (the folder is closed to the web), so a
   silent cron can be checked over FTP: when it last ran, on which PHP, what it sent. */
function cronReport(array $report): void {
    $report = ['at' => gmdate('c'), 'php' => PHP_VERSION, 'sapi' => PHP_SAPI] + $report;
    @file_put_contents(__DIR__ . '/last-run.json', json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/* Web Push encryption needs openssl_pkey_derive (PHP 7.3+). The cron command can run an
   older PHP than the site does, so say so instead of failing on the first subscription. */
if (!function_exists('openssl_pkey_derive') || !function_exists('curl_init')) {
    cronReport(['error' => 'Cron runs PHP ' . PHP_VERSION . ' without openssl_pkey_derive or curl: point the cron command at the PHP the site uses (8.x).']);
    echo "Niet reminders: PHP " . PHP_VERSION . " cannot send Web Push\n";
    exit(1);
}

require_once __DIR__ . '/../api/push.php';

/* A slot is due from its time until this many minutes later, so a cron that runs a little
   late or skips a minute still sends it; the marker keeps it to once a day. */
const REMINDER_WINDOW_MINUTES = 15;

function minutesOfDay(string $time): int {
    [$hours, $minutes] = array_map('intval', explode(':', $time) + [0, 0]);
    return $hours * 60 + $minutes;
}

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
            $slot = null;
            foreach (($settings['slots'] ?? $defaults['slots']) as $candidate) {
                if (!($candidate['enabled'] ?? true) || !preg_match('/^\d{1,2}:\d{2}$/', (string)($candidate['time'] ?? ''))) continue;
                $late = minutesOfDay($time) - minutesOfDay($candidate['time']);
                if ($late >= 0 && $late < REMINDER_WINDOW_MINUTES) { $slot = $candidate; break; }
            }
            if (!$slot) continue;
            $marker = $today . '-' . substr((string)($slot['id'] ?? str_replace(':', '', $time)), 0, 4);
            if ($subscription['last_sent_marker'] === $marker) continue;
            $progress->execute([$subscription['user_id'], $today]);
            $completed = (int)($progress->fetchColumn() ?: 0);
            $slotId = $slot['id'] ?? 'start'; $body = $slotMessages[$slotId] ?? $slotMessages['start'];
            if ($slotId === 'continue' && $completed > 0) $body = "Уже {$completed} из 12 дел отмечено. Продолжим?";
            if ($slotId === 'finish' && $completed > 0) $body = "Сегодня отмечено {$completed} из 12 дел. Бережно подведите итог дня.";
            /* The title says what the reminder is about; a tap opens today's deeds. */
            $titles = ['start' => 'Дела дня', 'continue' => $completed > 0 ? "Дела дня · {$completed} из 12" : 'Продолжим путь', 'finish' => 'Итог дня'];
            $title = $titles[$slotId] ?? 'Дела дня';

            $result = sendWebPush([
                'endpoint' => $subscription['endpoint'],
                'keys' => ['p256dh' => $subscription['p256dh'], 'auth' => $subscription['auth']],
            ], [
                'title' => $title,
                'body' => $body,
                'url' => '/?open=deeds',
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
                $lastError = 'HTTP ' . $result['status'] . ' ' . $result['error'];
                error_log('Niet Web Push delivery failed: ' . $lastError);
            }
        } catch (Throwable $error) {
            $failed++;
            $lastError = $error->getMessage();
            error_log('Niet Web Push reminder error: ' . $lastError);
        }
    }

    /* The evening before an event's last day (18:00 in the admin's time zone), its approved
       participants get one push. reminded_on is set before sending, so a crash never repeats it. */
    $eventReminders = 0;
    try {
        require_once __DIR__ . '/../api/event-reminders.php';
        $zone = (string)($db->query("SELECT setting_value FROM admin_settings WHERE setting_key = 'timezone'")->fetchColumn() ?: 'Asia/Bishkek');
        if (!in_array($zone, timezone_identifiers_list(), true)) $zone = 'Asia/Bishkek';
        $adminNow = $utcNow->setTimezone(new DateTimeZone($zone));
        if ((int)$adminNow->format('G') >= 18) {
            $todayAdmin = $adminNow->format('Y-m-d');
            $due = $db->prepare("SELECT id, title, mode FROM events WHERE status = 'published' AND remind_before_end = 1 AND ends_on = ? AND (reminded_on IS NULL OR reminded_on <> ?)");
            $due->execute([$adminNow->modify('+1 day')->format('Y-m-d'), $todayAdmin]);
            $mark = $db->prepare('UPDATE events SET reminded_on = ? WHERE id = ?');
            foreach ($due->fetchAll() as $event) {
                $mark->execute([$todayAdmin, $event['id']]);
                $body = $event['mode'] === 'personal_path'
                    ? 'Завтра последний день вашего личного пути. Отметьте сегодняшние дела.'
                    : 'Завтра последний день. Отметьте дела — они засчитаются в общую цель.';
                $result = sendEventPush($db, (int)$event['id'], '«' . $event['title'] . '» — завтра финал', $body, $config);
                $eventReminders += $result['sent'];
            }
        }
    } catch (Throwable $error) {
        $lastError = 'event reminders: ' . $error->getMessage();
        error_log('Chetele event reminder error: ' . $error->getMessage());
    }

    cronReport(['subscriptions' => count($subscriptions), 'sent' => $sent, 'failed' => $failed, 'eventReminders' => $eventReminders, 'lastError' => $lastError ?? null]);
    echo 'Niet reminders: sent=' . $sent . '; failed=' . $failed . PHP_EOL;
} catch (Throwable $error) {
    cronReport(['error' => $error->getMessage()]);
    error_log('Niet reminder cron failed: ' . $error->getMessage());
    http_response_code(500);
    echo "Niet reminders: configuration error\n";
    exit(1);
}
