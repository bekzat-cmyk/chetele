<?php
declare(strict_types=1);

/* Push reminders for one event, sent to its approved participants. Used by the admin's
   «Напомнить» button and by the cron that reminds a day before an event ends. */

require_once __DIR__ . '/push.php';

/* Active push subscriptions of an event's approved participants. */
function eventReminderTargets(PDO $db, int $eventId): array {
    $statement = $db->prepare("SELECT s.id, s.endpoint, s.p256dh, s.auth FROM push_subscriptions s
        INNER JOIN event_participants p ON p.user_id = s.user_id AND p.event_id = ? AND p.status = 'approved'
        WHERE s.active = 1");
    $statement->execute([$eventId]);
    return $statement->fetchAll();
}

/* Sends one message to every target; gone subscriptions (404/410) are switched off. */
function sendEventPush(PDO $db, int $eventId, string $title, string $body, array $config): array {
    $targets = eventReminderTargets($db, $eventId);
    $sent = 0;
    $failed = 0;
    $disable = $db->prepare('UPDATE push_subscriptions SET active = 0 WHERE id = ?');
    foreach ($targets as $target) {
        try {
            $result = sendWebPush(
                ['endpoint' => $target['endpoint'], 'keys' => ['p256dh' => $target['p256dh'], 'auth' => $target['auth']]],
                ['title' => $title, 'body' => $body, 'url' => '/?open=events', 'tag' => 'chetele-event-' . $eventId, 'actions' => [['action' => 'open', 'title' => 'Открыть событие']]],
                $config
            );
            if ($result['ok']) $sent++;
            else {
                $failed++;
                if (in_array($result['status'], [404, 410], true)) $disable->execute([$target['id']]);
            }
        } catch (Throwable $error) {
            $failed++;
            error_log('Chetele event push error: ' . $error->getMessage());
        }
    }
    return ['targets' => count($targets), 'sent' => $sent, 'failed' => $failed];
}
