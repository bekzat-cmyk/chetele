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
    /* moderation: new participants wait for the admin's approval before their marks count.
       remind_before_end: cron pushes participants the evening before the last day. */
    if (!in_array('moderation', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN moderation TINYINT(1) NOT NULL DEFAULT 0 AFTER results_visibility');
    }
    if (!in_array('remind_before_end', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN remind_before_end TINYINT(1) NOT NULL DEFAULT 1 AFTER moderation');
    }
    if (!in_array('reminded_on', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN reminded_on DATE NULL AFTER remind_before_end');
    }
    if (!in_array('manual_reminded_at', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN manual_reminded_at DATETIME NULL AFTER reminded_on');
    }
    if (!in_array('custom_unit', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN custom_unit VARCHAR(16) NULL AFTER metric');
    }
    if (!in_array('custom_step', $eventColumns, true)) {
        $db->exec('ALTER TABLE events ADD COLUMN custom_step INT UNSIGNED NOT NULL DEFAULT 1 AFTER custom_unit');
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
    $db->exec('CREATE TABLE IF NOT EXISTS event_contributions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        event_id BIGINT UNSIGNED NOT NULL,
        user_id CHAR(36) NOT NULL,
        amount INT UNSIGNED NOT NULL,
        entry_date DATE NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_contributions_event_user (event_id, user_id),
        CONSTRAINT fk_contribution_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        CONSTRAINT fk_contribution_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    /* approved | pending | rejected. Only approved participants count in totals and ratings. */
    if (!in_array('status', $participantColumns, true)) {
        $db->exec("ALTER TABLE event_participants ADD COLUMN status VARCHAR(12) NOT NULL DEFAULT 'approved' AFTER personal_target");
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

/* Groups are the admin's own lists of members (a family, a class, a mosque circle),
   so their reports can be read together. Members leave a group when deleted. */
function ensureGroupsTables(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS admin_groups (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL,
        description VARCHAR(300) NOT NULL DEFAULT \'\',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $db->exec('CREATE TABLE IF NOT EXISTS admin_group_members (
        group_id INT UNSIGNED NOT NULL,
        user_id CHAR(36) NOT NULL,
        added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (group_id, user_id),
        INDEX idx_group_members_user (user_id),
        CONSTRAINT fk_group_members_group FOREIGN KEY (group_id) REFERENCES admin_groups(id) ON DELETE CASCADE,
        CONSTRAINT fk_group_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

/* Reads a group's name, description and member ids from a request body. */
function groupInput(array $body): array {
    $name = trim((string)($body['name'] ?? ''));
    $description = trim((string)($body['description'] ?? ''));
    if ($name === '' || mb_strlen($name) > 80) respond(400, ['error' => 'Название группы — от 1 до 80 символов.']);
    $members = $body['members'] ?? [];
    if (!is_array($members) || count($members) > 1000) respond(400, ['error' => 'Некорректный список участников.']);
    $ids = [];
    foreach ($members as $id) {
        if (!is_string($id) || !preg_match('/^[a-f0-9-]{36}$/i', $id)) respond(400, ['error' => 'Некорректный участник в списке.']);
        $ids[strtolower($id)] = true;
    }
    return [$name, mb_substr($description, 0, 300, 'UTF-8'), array_keys($ids)];
}

/* Replaces a group's members; ids of deleted accounts are skipped. */
function saveGroupMembers(PDO $db, int $groupId, array $userIds): void {
    $db->prepare('DELETE FROM admin_group_members WHERE group_id = ?')->execute([$groupId]);
    $insert = $db->prepare('INSERT INTO admin_group_members (group_id, user_id) SELECT ?, id FROM users WHERE id = ?');
    foreach ($userIds as $userId) $insert->execute([$groupId, $userId]);
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
    if ($metric === 'book_pages') return "CAST(JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.kitap')) AS UNSIGNED)";
    if ($metric === 'tahajjud_count') return "CASE WHEN JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.tahajjud')) = 'yes' THEN 1 ELSE 0 END";
    return "CAST(JSON_UNQUOTE(JSON_EXTRACT({$tasksColumn}, '$.salawat')) AS UNSIGNED)";
}

const EVENT_METRICS = ['salawat','dhikr','quran_pages','book_pages','tahajjud_count','custom'];

/* A «custom» event is not tied to the app's deeds: participants add their own amounts
   (kilometres, pages, visits…) to event_contributions. Every other metric is read from
   daily_entries between the event's start (or the day of joining) and its end. */
function eventIsCustom(array $event): bool { return ($event['metric'] ?? '') === 'custom'; }

/* Rows (user_id, progress) for the event's approved participants. */
function eventProgressTable(array $event): array {
    if (eventIsCustom($event)) {
        return ["SELECT p.user_id, COALESCE(SUM(c.amount),0) AS progress FROM event_participants p
            LEFT JOIN event_contributions c ON c.event_id=p.event_id AND c.user_id=p.user_id
            WHERE p.event_id=? AND p.status='approved' GROUP BY p.user_id", [$event['id']]];
    }
    $metricSql = eventMetricSql((string)$event['metric'], 'e.tasks');
    return ["SELECT p.user_id, COALESCE(SUM({$metricSql}),0) AS progress FROM event_participants p
        LEFT JOIN daily_entries e ON e.user_id=p.user_id AND e.entry_date BETWEEN GREATEST(?, DATE(p.joined_at)) AND ?
        WHERE p.event_id=? AND p.status='approved' GROUP BY p.user_id", [$event['starts_on'], $event['ends_on'], $event['id']]];
}

function eventTotal(PDO $db, array $event): int {
    [$sql, $params] = eventProgressTable($event);
    $statement = $db->prepare("SELECT COALESCE(SUM(progress),0) FROM ({$sql}) t");
    $statement->execute($params);
    return (int)$statement->fetchColumn();
}

function eventLeaders(PDO $db, array $event, int $limit): array {
    [$sql, $params] = eventProgressTable($event);
    $statement = $db->prepare("SELECT u.display_name AS name, t.progress FROM ({$sql}) t INNER JOIN users u ON u.id=t.user_id ORDER BY t.progress DESC, u.display_name ASC LIMIT {$limit}");
    $statement->execute($params);
    return array_map(fn($row) => ['name' => $row['name'], 'progress' => (int)$row['progress']], $statement->fetchAll());
}

function eventRank(PDO $db, array $event, int $progress): int {
    [$sql, $params] = eventProgressTable($event);
    $statement = $db->prepare("SELECT 1 + COUNT(*) FROM ({$sql}) t WHERE t.progress > ?");
    $statement->execute([...$params, $progress]);
    return (int)$statement->fetchColumn();
}

/* One member's own progress, whatever their approval status. */
function eventUserProgress(PDO $db, array $event, string $userId, string $joinedAt): int {
    if (eventIsCustom($event)) {
        $statement = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM event_contributions WHERE event_id=? AND user_id=?');
        $statement->execute([$event['id'], $userId]);
    } else {
        $metricSql = eventMetricSql((string)$event['metric'], 'tasks');
        $statement = $db->prepare("SELECT COALESCE(SUM({$metricSql}),0) FROM daily_entries WHERE user_id=? AND entry_date BETWEEN GREATEST(?, DATE(?)) AND ?");
        $statement->execute([$userId, $event['starts_on'], $joinedAt, $event['ends_on']]);
    }
    return (int)$statement->fetchColumn();
}

/* Unit and quick-button step of a custom event, read from a request body. */
function customEventInput(array $body, string $metric): array {
    if ($metric !== 'custom') return [null, 1];
    $unit = trim(preg_replace('/\s+/u', ' ', (string)($body['customUnit'] ?? '')) ?? '');
    if ($unit === '' || mb_strlen($unit, 'UTF-8') > 16) respond(400, ['error' => 'Укажите, в чём считаем: например «км», «стр.» или «раз» (до 16 символов).']);
    $step = (int)($body['customStep'] ?? 1);
    return [$unit, max(1, min(10000, $step))];
}

/* The client's address. Behind the hosting's own nginx proxy REMOTE_ADDR is a local
   address, and only then is X-Real-IP / X-Forwarded-For trusted. */
function clientIp(): string {
    $remote = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $isProxy = $remote === '' || filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    if ($isProxy) {
        /* X-Real-IP is set by the proxy itself. In X-Forwarded-For only the last entry was
           added by the proxy; earlier ones come from the client and can be forged. */
        $real = trim((string)($_SERVER['HTTP_X_REAL_IP'] ?? ''));
        if (filter_var($real, FILTER_VALIDATE_IP)) return $real;
        $chain = array_map('trim', explode(',', (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')));
        $last = (string)end($chain);
        if (filter_var($last, FILTER_VALIDATE_IP)) return $last;
    }
    return $remote !== '' ? $remote : 'unknown';
}

const ADMIN_MAX_FAILURES = 5;
const ADMIN_LOCK_MINUTES = 15;

function ensureAdminAttemptsTable(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS admin_login_failures (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        login_tried VARCHAR(64) NOT NULL DEFAULT \'\',
        failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_admin_failures_ip_time (ip, failed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

/* Checks the admin's login and password on every admin request. After ADMIN_MAX_FAILURES
   wrong attempts from one address within ADMIN_LOCK_MINUTES, that address is refused
   (even with the right password) until the window passes. Other addresses, the admin's
   included, are not affected, so the lock cannot be used to shut the admin out. */
function requireAdmin(array $config, PDO $db): void {
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

    ensureAdminAttemptsTable($db);
    $ip = clientIp();
    $recent = $db->prepare('SELECT COUNT(*), MIN(failed_at) FROM admin_login_failures WHERE ip = ? AND failed_at > DATE_SUB(NOW(), INTERVAL ' . ADMIN_LOCK_MINUTES . ' MINUTE)');
    $recent->execute([$ip]);
    [$failures, $firstFailure] = $recent->fetch(PDO::FETCH_NUM);
    if ((int)$failures >= ADMIN_MAX_FAILURES) {
        $wait = $db->prepare('SELECT GREATEST(1, CEIL(TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(?, INTERVAL ' . ADMIN_LOCK_MINUTES . ' MINUTE)) / 60))');
        $wait->execute([$firstFailure]);
        $minutes = (int)$wait->fetchColumn();
        header('Retry-After: ' . ($minutes * 60));
        respond(429, ['error' => "Слишком много неверных попыток входа. Попробуйте через {$minutes} мин.", 'reason' => 'admin_locked']);
    }

    $keyAllowed = $legacyKey !== '' && hash_equals($legacyKey, $providedKey);
    $credentialsAllowed = $credentialsConfigured && hash_equals($expectedLogin, $login) && hash_equals($expectedPassword, $password);
    if ($keyAllowed || $credentialsAllowed) {
        if ((int)$failures > 0) $db->prepare('DELETE FROM admin_login_failures WHERE ip = ?')->execute([$ip]);
        return;
    }

    $db->prepare('INSERT INTO admin_login_failures (ip, login_tried) VALUES (?, ?)')->execute([$ip, mb_substr($login, 0, 64, 'UTF-8')]);
    $db->exec('DELETE FROM admin_login_failures WHERE failed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)');
    $left = ADMIN_MAX_FAILURES - (int)$failures - 1;
    respond(403, ['error' => $left > 0 ? "Неверный логин или пароль администратора. Осталось попыток: {$left}." : 'Неверный логин или пароль. Вход с этого адреса закрыт на ' . ADMIN_LOCK_MINUTES . ' минут.']);
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
        'fast' => ($tasks['fast'] ?? '') === 'yes' ? 'yes' : (($tasks['fast'] ?? '') === 'no' ? 'no' : ''),
        'salawat' => amount($tasks['salawat'] ?? 0),
        'tasbihat' => amount($tasks['tasbihat'] ?? 0),
        'zikr' => (object)$zikr,
    ];
    $normalizedQaza = [];
    foreach (['Багымдат', 'Пешим', 'Аср', 'Шам', 'Куптан', 'Витр'] as $name) {
        $normalizedQaza[$name] = amount($qaza[$name] ?? 0);
    }
    $completed = 0;
    foreach (['quran', 'jauchan', 'kitap', 'salawat', 'tasbihat'] as $name) {
        $completed += $normalizedTasks[$name] > 0 ? 1 : 0;
    }
    $completed += $normalizedTasks['tahajjud'] === 'yes' ? 1 : 0;
    $completed += $normalizedTasks['fast'] === 'yes' ? 1 : 0;
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
    ensureGroupsTables($db);
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
        $today = (string)$db->query('SELECT CURDATE()')->fetchColumn();
        $events = $db->query("SELECT * FROM events WHERE status IN ('published','paused') AND ends_on >= CURDATE() ORDER BY starts_on ASC, id DESC")->fetchAll();
        foreach ($events as &$event) {
            unset($event['reminded_on'], $event['manual_reminded_at']);
            $event['totalProgress'] = eventTotal($db, $event);
            $count = $db->prepare("SELECT COUNT(*) FROM event_participants WHERE event_id = ? AND status = 'approved'"); $count->execute([$event['id']]); $event['participants'] = (int)$count->fetchColumn();
            $event['joined'] = false; $event['pending'] = false; $event['rejected'] = false; $event['paused'] = $event['status'] === 'paused'; $event['myProgress'] = 0; $event['myRank'] = null; $event['personalTarget'] = null; $event['leaders'] = [];
            $event['custom'] = eventIsCustom($event); $event['canContribute'] = false; $event['myEntries'] = [];
            if (preg_match('/^[a-f0-9-]{36}$/i', $userId)) {
                $joined = $db->prepare('SELECT joined_at, personal_target, status FROM event_participants WHERE event_id=? AND user_id=?'); $joined->execute([$event['id'], $userId]); $participant=$joined->fetch();
                $event['joined'] = (bool)$participant;
                $event['pending'] = $participant && $participant['status'] === 'pending';
                $event['rejected'] = $participant && $participant['status'] === 'rejected';
                if ($participant) {
                    $event['myProgress'] = eventUserProgress($db, $event, $userId, (string)$participant['joined_at']);
                    $event['personalTarget'] = $participant['personal_target'] === null ? null : (int)$participant['personal_target'];
                    if ($event['custom']) {
                        /* Custom amounts can be added while the event runs, pause or not; a pending
                           member's amounts are kept and count once approved. */
                        $event['canContribute'] = !$event['rejected'] && $event['starts_on'] <= $today;
                        $mine = $db->prepare('SELECT id, amount, entry_date FROM event_contributions WHERE event_id=? AND user_id=? ORDER BY id DESC LIMIT 10');
                        $mine->execute([$event['id'], $userId]);
                        $event['myEntries'] = array_map(fn($row) => ['id' => (int)$row['id'], 'amount' => (int)$row['amount'], 'date' => $row['entry_date']], $mine->fetchAll());
                    }
                }
            }
            if (($event['mode'] ?? 'common_goal') === 'common_goal' && (int)($event['leaderboard_enabled'] ?? 1) === 1) {
                $event['leaders'] = eventLeaders($db, $event, 3);
                foreach ($event['leaders'] as $index => &$leader) {
                    if (($event['results_visibility'] ?? 'names') === 'anonymous') $leader['name']='Участник '.str_pad((string)($index+1),2,'0',STR_PAD_LEFT);
                    elseif (($event['results_visibility'] ?? 'names') === 'initials') { $parts=preg_split('/\s+/u',trim((string)$leader['name']))?:[]; $leader['name']=implode(' ',array_map(function($part){ return mb_substr($part,0,1,'UTF-8').'.'; },$parts)); }
                }
                unset($leader);
                if ($event['joined'] && !$event['pending'] && !$event['rejected']) $event['myRank'] = eventRank($db, $event, $event['myProgress']);
            }
        }
        unset($event); respond(200, $events);
    }

    /* A participant adds an amount to a custom event («прошёл 5 км», «прочитал 20 страниц»). */
    if ($method === 'POST' && count($parts) === 3 && $parts[0] === 'events' && $parts[2] === 'contributions') {
        $eventId=(int)$parts[1]; $body=requestBody(); $userId=(string)($body['userId']??''); $value=amount($body['amount']??0);
        if ($eventId<1 || !preg_match('/^[a-f0-9-]{36}$/i',$userId)) respond(400,['error'=>'Некорректные данные.']);
        if ($value<1) respond(400,['error'=>'Введите число больше нуля.']);
        requireUser($db, $userId);
        $find=$db->prepare("SELECT e.id, p.status FROM events e INNER JOIN event_participants p ON p.event_id=e.id AND p.user_id=?
            WHERE e.id=? AND e.metric='custom' AND e.status IN ('published','paused') AND e.starts_on<=CURDATE() AND e.ends_on>=CURDATE()");
        $find->execute([$userId,$eventId]); $row=$find->fetch();
        if (!$row) respond(404,['error'=>'Сейчас в это событие нельзя добавить вклад.']);
        if ($row['status']==='rejected') respond(403,['error'=>'Заявка на участие отклонена.']);
        $today=$db->prepare('SELECT COUNT(*) FROM event_contributions WHERE event_id=? AND user_id=? AND entry_date=CURDATE()'); $today->execute([$eventId,$userId]);
        if ((int)$today->fetchColumn()>=50) respond(429,['error'=>'Слишком много записей за день.']);
        $insert=$db->prepare('INSERT INTO event_contributions (event_id,user_id,amount,entry_date) VALUES (?,?,?,CURDATE())'); $insert->execute([$eventId,$userId,$value]);
        respond(201,['id'=>(int)$db->lastInsertId()]);
    }

    /* A participant removes one of their own amounts, e.g. a typo. */
    if ($method === 'DELETE' && count($parts) === 4 && $parts[0] === 'events' && $parts[2] === 'contributions') {
        $sessionUser=sessionUserId($db); if ($sessionUser===null) respond(401,['error'=>'Сессия истекла. Войдите снова.','reason'=>'session_required']);
        $statement=$db->prepare('DELETE FROM event_contributions WHERE id=? AND event_id=? AND user_id=?'); $statement->execute([(int)$parts[3],(int)$parts[1],$sessionUser]);
        if ($statement->rowCount()===0) respond(404,['error'=>'Запись не найдена.']);
        respond(200,['deleted'=>true]);
    }

    if ($method === 'POST' && count($parts) === 3 && $parts[0] === 'events' && $parts[2] === 'join') {
        $eventId=(int)$parts[1]; $body=requestBody(); $userId=(string)($body['userId']??''); $login=strtolower(trim((string)($body['login']??'')));
        if ($eventId<1 || !preg_match('/^[a-f0-9-]{36}$/i',$userId) || !validLogin($login)) respond(400,['error'=>'Некорректные данные участия.']);
        requireUser($db, $userId);
        $event=$db->prepare("SELECT id,mode,goal,moderation FROM events WHERE id=? AND status='published' AND ends_on>=CURDATE()"); $event->execute([$eventId]); $eventData=$event->fetch(); if(!$eventData) respond(404,['error'=>'Событие недоступно.']);
        $user=$db->prepare('SELECT 1 FROM users WHERE id=? AND login=?'); $user->execute([$userId,$login]); if(!$user->fetchColumn()) respond(403,['error'=>'Пользователь не найден.']);
        $personalTarget = null;
        if (($eventData['mode'] ?? 'common_goal') === 'personal_path') { $personalTarget=amount($body['personalTarget'] ?? 0); if($personalTarget<1) respond(400,['error'=>'Укажите личную цель.']); }
        $pending=(int)($eventData['moderation'] ?? 0) === 1;
        $join=$db->prepare('INSERT IGNORE INTO event_participants (event_id,user_id,personal_target,status) VALUES (?,?,?,?)'); $join->execute([$eventId,$userId,$personalTarget,$pending?'pending':'approved']); respond(201,['joined'=>true,'pending'=>$pending]);
    }

    if ($method === 'GET' && $parts === ['admin','events']) {
        requireAdmin($config, $db);
        $events = $db->query("SELECT e.*,
            (SELECT COUNT(*) FROM event_participants p WHERE p.event_id=e.id AND p.status='approved') AS participant_count,
            (SELECT COUNT(*) FROM event_participants p WHERE p.event_id=e.id AND p.status='pending') AS pending_count,
            (SELECT COUNT(*) FROM event_contributions c WHERE c.event_id=e.id) AS contribution_count
            FROM events e ORDER BY created_at DESC")->fetchAll();
        foreach ($events as &$event) $event['total_progress'] = eventTotal($db, $event);
        unset($event);
        respond(200, $events);
    }

    if ($method === 'GET' && $parts === ['admin','users']) {
        requireAdmin($config, $db);
        $users = $db->query("SELECT u.id,u.display_name,u.login,u.created_at,
            (SELECT COUNT(*) FROM event_participants p WHERE p.user_id=u.id AND p.status='approved') AS event_count,
            (SELECT COALESCE(SUM(e.points),0) FROM daily_entries e WHERE e.user_id=u.id) AS points,
            (SELECT COUNT(*) FROM daily_entries e WHERE e.user_id=u.id AND e.completed_count>0) AS active_days,
            (SELECT MAX(e.entry_date) FROM daily_entries e WHERE e.user_id=u.id AND e.completed_count>0) AS last_active,
            (SELECT COUNT(*) FROM push_subscriptions s WHERE s.user_id=u.id AND s.active=1) AS push_devices
            FROM users u ORDER BY u.created_at DESC")->fetchAll();
        respond(200, $users);
    }

    if ($method === 'GET' && $parts === ['admin','overview']) {
        requireAdmin($config, $db);
        $users=(int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $activeEvents=(int)$db->query("SELECT COUNT(*) FROM events WHERE status='published' AND starts_on<=CURDATE() AND ends_on>=CURDATE()")->fetchColumn();
        $todayEntries=(int)$db->query('SELECT COUNT(*) FROM daily_entries WHERE entry_date=CURDATE()')->fetchColumn();
        $todayPoints=(int)$db->query('SELECT COALESCE(SUM(points),0) FROM daily_entries WHERE entry_date=CURDATE()')->fetchColumn();
        $newUsersPeriod=(int)$db->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)')->fetchColumn();
        $completedEvents=(int)$db->query("SELECT COUNT(*) FROM events WHERE status='published' AND ends_on<CURDATE()")->fetchColumn();
        $activity=$db->query('SELECT entry_date AS date, COUNT(*) AS entries FROM daily_entries WHERE entry_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY entry_date ORDER BY entry_date ASC')->fetchAll();
        $recentUsers=$db->query('SELECT display_name,created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();
        $recentEvents=$db->query("SELECT title,starts_on,ends_on,status FROM events ORDER BY created_at DESC LIMIT 4")->fetchAll();
        /* How many people marked each deed today. */
        $deedCounts=['salawat'=>0,'tasbihat'=>0,'quran'=>0,'jauchan'=>0,'kitap'=>0,'nafil'=>0,'tahajjud'=>0,'fast'=>0,'qaza'=>0];
        foreach ($db->query('SELECT tasks,qaza FROM daily_entries WHERE entry_date=CURDATE() AND completed_count>0') as $row) {
            $tasks=json_decode((string)$row['tasks'],true); $qaza=json_decode((string)$row['qaza'],true);
            if (!is_array($tasks)) continue;
            foreach (['salawat','tasbihat','quran','jauchan','kitap'] as $key) if ((int)($tasks[$key]??0)>0) $deedCounts[$key]++;
            if (is_array($tasks['nafil']??null) && array_filter($tasks['nafil'])) $deedCounts['nafil']++;
            if (($tasks['tahajjud']??'')==='yes') $deedCounts['tahajjud']++;
            if (($tasks['fast']??'')==='yes') $deedCounts['fast']++;
            if (is_array($qaza) && array_filter($qaza)) $deedCounts['qaza']++;
        }
        $pendingModeration=(int)$db->query("SELECT COUNT(*) FROM event_participants WHERE status='pending'")->fetchColumn();
        respond(200,['deedsToday'=>$deedCounts,'pendingModeration'=>$pendingModeration,'users'=>$users,'activeEvents'=>$activeEvents,'todayEntries'=>$todayEntries,'todayPoints'=>$todayPoints,'newUsersPeriod'=>$newUsersPeriod,'completedEvents'=>$completedEvents,'activity'=>$activity,'recentUsers'=>$recentUsers,'recentEvents'=>$recentEvents]);
    }

    if ($method === 'GET' && $parts === ['admin','settings']) {
        requireAdmin($config, $db);
        $defaults=['timezone'=>'Asia/Bishkek','defaultLeaderboard'=>true,'defaultVisibility'=>'names','defaultModeration'=>false];
        $saved=$db->query('SELECT setting_key,setting_value FROM admin_settings')->fetchAll();
        foreach($saved as $row) if(array_key_exists($row['setting_key'],$defaults)) {
            $defaults[$row['setting_key']]=in_array($row['setting_key'],['defaultLeaderboard','defaultModeration'],true)?$row['setting_value']==='1':$row['setting_value'];
        }
        /* The reminder cron leaves its last report beside itself (see cron/send-reminders.php). */
        $cron=null; $cronFile=__DIR__.'/../cron/last-run.json';
        if (is_file($cronFile)) { $report=json_decode((string)file_get_contents($cronFile),true); if (is_array($report)) $cron=['at'=>$report['at']??null,'sent'=>$report['sent']??null,'failed'=>$report['failed']??null,'eventReminders'=>$report['eventReminders']??null,'error'=>$report['error']??($report['lastError']??null)]; }
        $defaults['system']=[
            'pushSubscriptions'=>(int)$db->query('SELECT COUNT(*) FROM push_subscriptions WHERE active=1')->fetchColumn(),
            'pushUsers'=>(int)$db->query('SELECT COUNT(DISTINCT user_id) FROM push_subscriptions WHERE active=1')->fetchColumn(),
            'cron'=>$cron,
            'failedLogins24h'=>(int)$db->query('SELECT COUNT(*) FROM admin_login_failures WHERE failed_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')->fetchColumn(),
            'failedIps24h'=>(int)$db->query('SELECT COUNT(DISTINCT ip) FROM admin_login_failures WHERE failed_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')->fetchColumn(),
            'lastFailedAt'=>$db->query('SELECT MAX(failed_at) FROM admin_login_failures')->fetchColumn() ?: null,
        ];
        respond(200,$defaults);
    }

    if ($method === 'PUT' && $parts === ['admin','settings']) {
        requireAdmin($config, $db); $body=requestBody();
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
        requireAdmin($config, $db); $userId=(string)$parts[2];
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

    if ($method === 'GET' && $parts === ['admin','groups']) {
        requireAdmin($config, $db);
        /* The list shows each group's last 7 days: who marked anything and how many deeds. */
        $groups = $db->query("SELECT g.id, g.name, g.description, g.created_at,
            (SELECT COUNT(*) FROM admin_group_members m WHERE m.group_id = g.id) AS member_count,
            (SELECT COUNT(DISTINCT d.user_id) FROM admin_group_members m INNER JOIN daily_entries d ON d.user_id = m.user_id
              WHERE m.group_id = g.id AND d.entry_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND d.completed_count > 0) AS active_week,
            (SELECT COALESCE(SUM(d.completed_count), 0) FROM admin_group_members m INNER JOIN daily_entries d ON d.user_id = m.user_id
              WHERE m.group_id = g.id AND d.entry_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)) AS deeds_week,
            (SELECT GROUP_CONCAT(u.display_name ORDER BY m.added_at SEPARATOR '\n') FROM admin_group_members m INNER JOIN users u ON u.id = m.user_id
              WHERE m.group_id = g.id) AS member_names
            FROM admin_groups g ORDER BY g.name")->fetchAll();
        foreach ($groups as &$group) {
            $names = $group['member_names'] === null ? [] : explode("\n", (string)$group['member_names']);
            $group['preview'] = array_slice($names, 0, 5);
            unset($group['member_names']);
        }
        unset($group);
        respond(200, $groups);
    }

    if ($method === 'POST' && $parts === ['admin','groups']) {
        requireAdmin($config, $db);
        [$name, $description, $members] = groupInput(requestBody());
        $db->beginTransaction();
        try {
            $db->prepare('INSERT INTO admin_groups (name, description) VALUES (?, ?)')->execute([$name, $description]);
            $groupId = (int)$db->lastInsertId();
            saveGroupMembers($db, $groupId, $members);
            $db->commit();
        } catch (Throwable $error) { if ($db->inTransaction()) $db->rollBack(); throw $error; }
        respond(201, ['id' => $groupId]);
    }

    if ($method === 'PUT' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'groups') {
        requireAdmin($config, $db);
        $groupId = (int)$parts[2];
        [$name, $description, $members] = groupInput(requestBody());
        $exists = $db->prepare('SELECT 1 FROM admin_groups WHERE id = ?'); $exists->execute([$groupId]);
        if (!$exists->fetchColumn()) respond(404, ['error' => 'Группа не найдена.']);
        $db->beginTransaction();
        try {
            $db->prepare('UPDATE admin_groups SET name = ?, description = ? WHERE id = ?')->execute([$name, $description, $groupId]);
            saveGroupMembers($db, $groupId, $members);
            $db->commit();
        } catch (Throwable $error) { if ($db->inTransaction()) $db->rollBack(); throw $error; }
        respond(200, ['updated' => true]);
    }

    if ($method === 'DELETE' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'groups') {
        requireAdmin($config, $db);
        $statement = $db->prepare('DELETE FROM admin_groups WHERE id = ?'); $statement->execute([(int)$parts[2]]);
        if ($statement->rowCount() === 0) respond(404, ['error' => 'Группа не найдена.']);
        respond(200, ['deleted' => true]);
    }

    /* A group's report for the last 7, 30 or 90 days, ending today: totals per member
       and how many members were active each day. */
    if ($method === 'GET' && count($parts) === 4 && $parts[0] === 'admin' && $parts[1] === 'groups' && $parts[3] === 'report') {
        requireAdmin($config, $db);
        $groupId = (int)$parts[2];
        $days = (int)($_GET['days'] ?? 7);
        if (!in_array($days, [7, 30, 90], true)) $days = 7;
        $group = $db->prepare('SELECT id, name, description, created_at FROM admin_groups WHERE id = ?'); $group->execute([$groupId]);
        $groupData = $group->fetch();
        if (!$groupData) respond(404, ['error' => 'Группа не найдена.']);
        $to = (string)$db->query('SELECT CURDATE()')->fetchColumn();
        $from = (new DateTimeImmutable($to))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
        $num = fn(string $key) => "COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(d.tasks, '$.{$key}')) AS UNSIGNED)), 0)";
        $yes = fn(string $key) => "COALESCE(SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(d.tasks, '$.{$key}')) = 'yes' THEN 1 ELSE 0 END), 0)";
        $members = $db->prepare("SELECT u.id, u.display_name, u.login, m.added_at,
            COALESCE(SUM(CASE WHEN d.completed_count > 0 THEN 1 ELSE 0 END), 0) AS active_days,
            COALESCE(SUM(d.completed_count), 0) AS deeds,
            COALESCE(SUM(d.points), 0) AS points,
            {$num('salawat')} AS salawat,
            {$num('tasbihat')} AS tasbihat,
            {$num('quran')} AS quran,
            {$yes('tahajjud')} AS tahajjud,
            {$yes('fast')} AS fast,
            (SELECT MAX(l.entry_date) FROM daily_entries l WHERE l.user_id = u.id AND l.completed_count > 0) AS last_active
            FROM admin_group_members m
            INNER JOIN users u ON u.id = m.user_id
            LEFT JOIN daily_entries d ON d.user_id = u.id AND d.entry_date BETWEEN ? AND ?
            WHERE m.group_id = ?
            GROUP BY u.id, u.display_name, u.login, m.added_at
            ORDER BY deeds DESC, u.display_name");
        $members->execute([$from, $to, $groupId]);
        $daily = $db->prepare('SELECT d.entry_date AS date,
            COUNT(DISTINCT CASE WHEN d.completed_count > 0 THEN d.user_id END) AS active,
            COALESCE(SUM(d.completed_count), 0) AS deeds
            FROM daily_entries d INNER JOIN admin_group_members m ON m.user_id = d.user_id AND m.group_id = ?
            WHERE d.entry_date BETWEEN ? AND ? GROUP BY d.entry_date ORDER BY d.entry_date');
        $daily->execute([$groupId, $from, $to]);
        respond(200, ['group' => $groupData, 'days' => $days, 'from' => $from, 'to' => $to, 'members' => $members->fetchAll(), 'daily' => $daily->fetchAll()]);
    }

    if ($method === 'POST' && $parts === ['admin','events']) {
        requireAdmin($config, $db); $body=requestBody(); $title=trim((string)($body['title']??'')); $description=trim((string)($body['description']??'')); $goal=(int)($body['goal']??0); $starts=(string)($body['startsOn']??''); $ends=(string)($body['endsOn']??'');
        $metric=(string)($body['metric']??'salawat'); $mode=(string)($body['mode']??'common_goal'); $status=(string)($body['status']??'published');
        $leaderboardEnabled=($body['leaderboardEnabled']??true)===true?1:0; $resultsVisibility=(string)($body['resultsVisibility']??'names');
        $moderation=($body['moderation']??false)===true?1:0; $remindBeforeEnd=($body['remindBeforeEnd']??true)===true?1:0;
        if (!in_array($metric, EVENT_METRICS, true)) $metric='salawat';
        [$customUnit,$customStep]=customEventInput($body,$metric);
        if (!in_array($mode, ['common_goal','personal_path'], true)) $mode='common_goal';
        if (!in_array($status, ['draft','published'], true)) $status='published';
        if (!in_array($resultsVisibility, ['names','initials','anonymous'], true)) $resultsVisibility='names';
        if($title==='' || mb_strlen($title)>120 || $goal<1 || !validDate($starts) || !validDate($ends) || $ends<$starts) respond(400,['error'=>'Проверьте название, цель и даты.']);
        $statement=$db->prepare("INSERT INTO events (title,description,mode,leaderboard_enabled,results_visibility,moderation,remind_before_end,metric,custom_unit,custom_step,goal,starts_on,ends_on,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $statement->execute([$title,mb_substr($description,0,800,'UTF-8'),$mode,$leaderboardEnabled,$resultsVisibility,$moderation,$remindBeforeEnd,$metric,$customUnit,$customStep,$goal,$starts,$ends,$status]); respond(201,['id'=>(int)$db->lastInsertId()]);
    }

    if ($method === 'PUT' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'events') {
        requireAdmin($config, $db); $eventId=(int)$parts[2]; $body=requestBody();
        $title=trim((string)($body['title']??'')); $description=trim((string)($body['description']??'')); $goal=(int)($body['goal']??0);
        $starts=(string)($body['startsOn']??''); $ends=(string)($body['endsOn']??'');
        $metric=(string)($body['metric']??'salawat'); $mode=(string)($body['mode']??'common_goal'); $status=(string)($body['status']??'published');
        $leaderboardEnabled=($body['leaderboardEnabled']??true)===true?1:0; $resultsVisibility=(string)($body['resultsVisibility']??'names');
        $moderation=($body['moderation']??false)===true?1:0; $remindBeforeEnd=($body['remindBeforeEnd']??true)===true?1:0;
        if ($eventId<1 || $title==='' || mb_strlen($title)>120 || $goal<1 || !validDate($starts) || !validDate($ends) || $ends<$starts) respond(400,['error'=>'Проверьте название, цель и даты.']);
        if (!in_array($metric, EVENT_METRICS, true)) $metric='salawat';
        [$customUnit,$customStep]=customEventInput($body,$metric);
        if (!in_array($mode, ['common_goal','personal_path'], true)) $mode='common_goal';
        if (!in_array($status, ['draft','published','paused'], true)) $status='published';
        if (!in_array($resultsVisibility, ['names','initials','anonymous'], true)) $resultsVisibility='names';
        $statement=$db->prepare('UPDATE events SET title=?,description=?,mode=?,leaderboard_enabled=?,results_visibility=?,moderation=?,remind_before_end=?,metric=?,custom_unit=?,custom_step=?,goal=?,starts_on=?,ends_on=?,status=? WHERE id=?');
        $statement->execute([$title,mb_substr($description,0,800,'UTF-8'),$mode,$leaderboardEnabled,$resultsVisibility,$moderation,$remindBeforeEnd,$metric,$customUnit,$customStep,$goal,$starts,$ends,$status,$eventId]);
        /* Switching moderation off lets everyone who waited in. */
        if ($moderation===0) $db->prepare("UPDATE event_participants SET status='approved' WHERE event_id=? AND status='pending'")->execute([$eventId]);
        /* MySQL returns 0 affected rows when the submitted values are identical.
           This is still a successful save; distinguish it from a missing event. */
        if ($statement->rowCount()===0) {
            $exists=$db->prepare('SELECT 1 FROM events WHERE id=?');
            $exists->execute([$eventId]);
            if (!$exists->fetchColumn()) respond(404,['error'=>'Событие не найдено.']);
        }
        respond(200,['updated'=>true]);
    }

    if ($method === 'POST' && count($parts) === 4 && $parts[0] === 'admin' && $parts[1] === 'events' && $parts[3] === 'pause') {
        requireAdmin($config, $db); $eventId=(int)$parts[2]; $paused=(requestBody()['paused']??true)===true;
        $statement=$db->prepare("UPDATE events SET status=? WHERE id=? AND status IN ('published','paused')");
        $statement->execute([$paused?'paused':'published',$eventId]);
        $check=$db->prepare("SELECT status FROM events WHERE id=?"); $check->execute([$eventId]); $status=$check->fetchColumn();
        if ($status===false) respond(404,['error'=>'Событие не найдено.']);
        if (!in_array($status,['published','paused'],true)) respond(409,['error'=>'Пауза доступна только для опубликованных событий.']);
        respond(200,['status'=>$status]);
    }

    /* «Напомнить»: one push to the event's approved participants, at most once an hour. */
    if ($method === 'POST' && count($parts) === 4 && $parts[0] === 'admin' && $parts[1] === 'events' && $parts[3] === 'remind') {
        requireAdmin($config, $db); $eventId=(int)$parts[2];
        $find=$db->prepare("SELECT *, COALESCE(manual_reminded_at > DATE_SUB(NOW(), INTERVAL 1 HOUR),0) AS recent, (starts_on<=CURDATE() AND ends_on>=CURDATE()) AS live FROM events WHERE id=?"); $find->execute([$eventId]); $event=$find->fetch();
        if (!$event) respond(404,['error'=>'Событие не найдено.']);
        if ($event['status']!=='published' || (int)$event['live']!==1) respond(409,['error'=>'Напомнить можно только об идущем опубликованном событии.']);
        if ((int)$event['recent']===1) respond(429,['error'=>'Напоминание уже отправлено в последний час.']);
        if (!function_exists('openssl_pkey_derive') || !function_exists('curl_init') || empty($config['vapid']['private_key'])) respond(503,['error'=>'Push-уведомления не настроены на сервере.']);
        $title='«'.$event['title'].'»';
        if (($event['mode']??'common_goal')==='personal_path') $body='Ваш личный путь продолжается — отметьте сегодняшние дела.';
        else {
            $percent=min(100,(int)floor(eventTotal($db,$event)/max(1,(int)$event['goal'])*100));
            $body="Общая цель выполнена на {$percent}%. ".(eventIsCustom($event)?'Добавьте свой вклад в карточке события.':'Отметьте свои дела — каждый вклад засчитывается.');
        }
        require_once __DIR__ . '/event-reminders.php';
        $db->prepare('UPDATE events SET manual_reminded_at=NOW() WHERE id=?')->execute([$eventId]);
        respond(200, sendEventPush($db,$eventId,$title,$body,$config));
    }

    /* Moderation queue: participants of moderated events waiting for approval. */
    if ($method === 'GET' && $parts === ['admin','moderation']) {
        requireAdmin($config, $db);
        $rows=$db->query("SELECT p.event_id,p.user_id,p.joined_at,p.personal_target,e.id,e.title,e.metric,e.custom_unit,e.mode,e.starts_on,e.ends_on,u.display_name,u.login
            FROM event_participants p INNER JOIN events e ON e.id=p.event_id INNER JOIN users u ON u.id=p.user_id
            WHERE p.status='pending' ORDER BY p.joined_at ASC LIMIT 500")->fetchAll();
        foreach ($rows as &$row) {
            $row['progress']=eventUserProgress($db,$row,(string)$row['user_id'],(string)$row['joined_at']);
        }
        unset($row);
        respond(200,$rows);
    }

    if ($method === 'POST' && $parts === ['admin','moderation']) {
        requireAdmin($config, $db); $body=requestBody();
        $decision=(string)($body['decision']??''); $items=$body['items']??[];
        if (!in_array($decision,['approve','reject'],true) || !is_array($items) || count($items)<1 || count($items)>500) respond(400,['error'=>'Некорректное решение.']);
        $statement=$db->prepare("UPDATE event_participants SET status=? WHERE event_id=? AND user_id=? AND status='pending'");
        $done=0;
        foreach ($items as $item) {
            $eventId=(int)($item['eventId']??0); $userId=(string)($item['userId']??'');
            if ($eventId<1 || !preg_match('/^[a-f0-9-]{36}$/i',$userId)) continue;
            $statement->execute([$decision==='approve'?'approved':'rejected',$eventId,$userId]); $done+=$statement->rowCount();
        }
        respond(200,['updated'=>$done]);
    }

    /* A new temporary password, shown once to the admin to pass on. All of the member's
       sessions end, so the old password and any lost phone stop working. */
    if ($method === 'POST' && count($parts) === 4 && $parts[0] === 'admin' && $parts[1] === 'users' && $parts[3] === 'password') {
        requireAdmin($config, $db); $userId=(string)$parts[2];
        if (!preg_match('/^[a-f0-9-]{36}$/i',$userId)) respond(400,['error'=>'Некорректный пользователь.']);
        $alphabet='abcdefghjkmnpqrstuvwxyz23456789'; $password='';
        for ($i=0;$i<10;$i++) $password.=$alphabet[random_int(0,strlen($alphabet)-1)];
        $statement=$db->prepare('UPDATE users SET pin_hash=? WHERE id=?'); $statement->execute([password_hash($password,PASSWORD_DEFAULT),$userId]);
        if ($statement->rowCount()===0) respond(404,['error'=>'Пользователь не найден.']);
        $db->prepare('DELETE FROM user_sessions WHERE user_id=?')->execute([$userId]);
        respond(200,['password'=>$password]);
    }

    if ($method === 'DELETE' && count($parts) === 3 && $parts[0] === 'admin' && $parts[1] === 'events') {
        requireAdmin($config, $db); $eventId=(int)$parts[2]; if ($eventId<1) respond(400,['error'=>'Некорректное событие.']);
        $statement=$db->prepare('DELETE FROM events WHERE id=?'); $statement->execute([$eventId]);
        if ($statement->rowCount()===0) respond(404,['error'=>'Событие не найдено.']);
        respond(200,['deleted'=>true]);
    }

    if ($method === 'GET' && ($parts === ['leaderboard'] || (count($parts) === 3 && $parts[0] === 'leaderboard' && $parts[1] === 'profile'))) {
        require_once __DIR__ . '/leaderboard.php';
        /* User ids are never exposed publicly: rows carry an opaque key for the participant
           card, and the caller's own row is marked with "me". */
        $sessionUser = sessionUserId($db);
        $secret = 'chetele-leaderboard|' . (string)($config['password'] ?? '');
        $today = (string)$db->query('SELECT CURDATE()')->fetchColumn();
        $people = [];
        foreach ($db->query('SELECT id, display_name, created_at FROM users')->fetchAll() as $user) {
            $people[(string)$user['id']] = ['name' => $user['display_name'], 'created' => (string)$user['created_at']];
        }
        /* Each person's total for a metric between two dates (null = from the start). */
        $scores = function (string $metric, ?string $from, string $to) use ($db, $people): array {
            $totals = array_fill_keys(array_keys($people), 0);
            $where = 'entry_date <= ?' . ($from ? ' AND entry_date >= ?' : '');
            $args = $from ? [$to, $from] : [$to];
            if ($metric === 'points') {
                $statement = $db->prepare("SELECT user_id, SUM(points) AS total FROM daily_entries WHERE {$where} GROUP BY user_id");
                $statement->execute($args);
                foreach ($statement->fetchAll() as $row) if (isset($totals[$row['user_id']])) $totals[$row['user_id']] = (int)$row['total'];
                return $totals;
            }
            $statement = $db->prepare("SELECT user_id, tasks FROM daily_entries WHERE {$where}");
            $statement->execute($args);
            foreach ($statement->fetchAll() as $row) {
                $tasks = json_decode((string)$row['tasks'], true);
                if (isset($totals[$row['user_id']]) && is_array($tasks)) $totals[$row['user_id']] += leaderboardDayScore($metric, $tasks, 0);
            }
            return $totals;
        };
        $rank = function (array $totals) use ($people): array {
            $list = [];
            foreach ($totals as $id => $score) $list[$id] = ['score' => $score, 'created' => $people[$id]['created']];
            return leaderboardRank($list);
        };
        header('X-Total-Count: ' . count($people));

        if ($parts[0] === 'leaderboard' && count($parts) === 3) {
            /* A participant's card: points, place, streaks and badges, never their deeds. */
            $userId = null;
            foreach (array_keys($people) as $id) if (hash_equals(leaderboardKey((string)$id, $secret), (string)$parts[2])) { $userId = (string)$id; break; }
            if ($userId === null) respond(404, ['error' => 'Участник не найден.']);
            $entriesStatement = $db->prepare('SELECT entry_date, tasks, completed_count FROM daily_entries WHERE user_id = ?');
            $entriesStatement->execute([$userId]);
            $entries = []; $days = [];
            foreach ($entriesStatement->fetchAll() as $row) {
                $tasks = json_decode((string)$row['tasks'], true);
                $entries[] = ['date' => (string)$row['entry_date'], 'tasks' => is_array($tasks) ? $tasks : [], 'completed' => (int)$row['completed_count']];
                $days[(string)$row['entry_date']] = (int)$row['completed_count'];
            }
            $points = [];
            foreach (['week', 'month', 'all'] as $period) {
                $window = leaderboardWindow($period, $today);
                $points[$period] = $scores('points', $window['from'], $window['to'])[$userId];
            }
            $allTime = $rank($scores('points', null, $today));
            $since = $entries ? min(array_column($entries, 'date')) : substr($people[$userId]['created'], 0, 10);
            respond(200, [
                'key' => $parts[2], 'name' => $people[$userId]['name'], 'me' => $sessionUser !== null && hash_equals($sessionUser, $userId),
                'since' => $since, 'points' => $points, 'place' => $allTime['places'][$userId],
                'streak' => leaderboardStreaks($days, $today), 'badges' => leaderboardBadges($entries),
            ]);
        }

        $limit = min(max((int)($_GET['limit'] ?? 50), 1), 100);
        $metric = in_array($_GET['metric'] ?? 'points', LEADERBOARD_METRICS, true) ? ($_GET['metric'] ?? 'points') : 'points';
        $window = leaderboardWindow((string)($_GET['period'] ?? ''), $today);
        $now = $scores($metric, $window['from'], $window['to']);
        $ranked = $rank($now);
        $before = $rank($scores($metric, $window['beforeFrom'], $window['beforeTo']))['places'];
        $row = function (string $id) use ($people, $now, $ranked, $before, $sessionUser, $secret): array {
            $place = $ranked['places'][$id];
            return [
                'name' => $people[$id]['name'], 'points' => $now[$id], 'place' => $place,
                'me' => $sessionUser !== null && hash_equals($sessionUser, $id),
                'move' => leaderboardMove($before[$id] ?? null, $place, $now[$id]),
                'key' => leaderboardKey($id, $secret),
            ];
        };
        $top = array_slice($ranked['order'], 0, $limit);
        $rows = array_map(fn($id) => $row((string)$id), $top);
        /* The caller outside the top list still gets their own row, appended last. */
        if ($sessionUser !== null && isset($people[$sessionUser]) && !in_array($sessionUser, array_map('strval', $top), true)) $rows[] = $row($sessionUser);
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
