<?php
declare(strict_types=1);

/* Live quiz («Живой квиз»): the admin runs a published test for everyone at once.
   Members join from the Tests tab and see the same question at the same moment; the
   server keeps the clock, accepts one answer per question and scores it, so nothing
   can be read or faked on the phone. A right answer earns 500–1000 points, more for
   a faster one. The app learns what is happening by asking every second or two.

   Steps: lobby → question → reveal → question … → finished (or cancelled). When a
   question's time runs out, the next request moves it to reveal by itself; the admin
   opens each next question. */

const LIVE_ACTIVE = ['lobby', 'question', 'reveal'];
const LIVE_GRACE_MS = 1500;          // network delay forgiven after the timer ends
const LIVE_SHOW_FINISHED_MIN = 30;   // the result stays in the app this long

function ensureLiveTables(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS live_quizzes (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT UNSIGNED NOT NULL,
        status VARCHAR(12) NOT NULL DEFAULT \'lobby\',
        q_index SMALLINT NOT NULL DEFAULT -1,
        q_started_ms BIGINT NULL,
        time_sec SMALLINT UNSIGNED NOT NULL DEFAULT 20,
        question_ids TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        finished_at TIMESTAMP NULL,
        KEY idx_live_status (status, created_at),
        CONSTRAINT fk_live_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $db->exec('CREATE TABLE IF NOT EXISTS live_players (
        live_id INT UNSIGNED NOT NULL,
        user_id CHAR(36) NOT NULL,
        score INT UNSIGNED NOT NULL DEFAULT 0,
        correct SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        streak SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        time_ms INT UNSIGNED NOT NULL DEFAULT 0,
        joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (live_id, user_id),
        CONSTRAINT fk_live_players_live FOREIGN KEY (live_id) REFERENCES live_quizzes(id) ON DELETE CASCADE,
        CONSTRAINT fk_live_players_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $db->exec('CREATE TABLE IF NOT EXISTS live_answers (
        live_id INT UNSIGNED NOT NULL,
        user_id CHAR(36) NOT NULL,
        q_index SMALLINT NOT NULL,
        choice TINYINT NOT NULL,
        ok TINYINT(1) NOT NULL DEFAULT 0,
        points INT UNSIGNED NOT NULL DEFAULT 0,
        ms INT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (live_id, user_id, q_index),
        CONSTRAINT fk_live_answers_live FOREIGN KEY (live_id) REFERENCES live_quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function liveNowMs(): int { return (int)floor(microtime(true) * 1000); }

/* The latest live quiz the app should show: one in progress, or one that ended a short while ago. */
function liveCurrent(PDO $db): ?array {
    $row = $db->query("SELECT l.*, q.title, q.category FROM live_quizzes l JOIN quizzes q ON q.id = l.quiz_id
        WHERE l.status IN ('lobby','question','reveal') OR (l.status IN ('finished','cancelled') AND l.finished_at >= DATE_SUB(NOW(), INTERVAL " . LIVE_SHOW_FINISHED_MIN . " MINUTE))
        ORDER BY l.id DESC LIMIT 1")->fetch();
    return $row ? liveTick($db, $row) : null;
}

function liveFind(PDO $db, int $id): ?array {
    $find = $db->prepare('SELECT l.*, q.title, q.category FROM live_quizzes l JOIN quizzes q ON q.id = l.quiz_id WHERE l.id = ?');
    $find->execute([$id]);
    $row = $find->fetch();
    return $row ? liveTick($db, $row) : null;
}

/* A question turns into its reveal on the first request after its time is up, or as
   soon as every player has answered. */
function liveTick(PDO $db, array $live): array {
    if ($live['status'] !== 'question') return $live;
    $over = liveNowMs() > (int)$live['q_started_ms'] + (int)$live['time_sec'] * 1000 + LIVE_GRACE_MS;
    if (!$over) {
        $count = $db->prepare('SELECT (SELECT COUNT(*) FROM live_players WHERE live_id = ?) AS players, (SELECT COUNT(*) FROM live_answers WHERE live_id = ? AND q_index = ?) AS answers');
        $count->execute([(int)$live['id'], (int)$live['id'], (int)$live['q_index']]);
        $row = $count->fetch();
        $over = (int)$row['players'] > 0 && (int)$row['answers'] >= (int)$row['players'];
    }
    if ($over) { liveReveal($db, $live); $live['status'] = 'reveal'; }
    return $live;
}

/* Who missed the question loses their streak. Only the request that actually moves the step does this. */
function liveReveal(PDO $db, array $live): void {
    $move = $db->prepare("UPDATE live_quizzes SET status = 'reveal' WHERE id = ? AND status = 'question' AND q_index = ?");
    $move->execute([(int)$live['id'], (int)$live['q_index']]);
    if ($move->rowCount() === 0) return;
    $db->prepare('UPDATE live_players p SET p.streak = 0 WHERE p.live_id = ? AND NOT EXISTS (SELECT 1 FROM live_answers a WHERE a.live_id = p.live_id AND a.user_id = p.user_id AND a.q_index = ? AND a.ok = 1)')
        ->execute([(int)$live['id'], (int)$live['q_index']]);
}

function liveQuestionIds(array $live): array {
    return array_values(array_map('intval', json_decode((string)$live['question_ids'], true) ?: []));
}

function liveQuestion(PDO $db, array $live): ?array {
    $ids = liveQuestionIds($live); $index = (int)$live['q_index'];
    if (!isset($ids[$index])) return null;
    $find = $db->prepare('SELECT id, text, options, correct, explanation FROM quiz_questions WHERE id = ?');
    $find->execute([$ids[$index]]);
    $row = $find->fetch();
    if (!$row) return null;
    return ['text' => $row['text'], 'options' => array_values(array_map('strval', json_decode((string)$row['options'], true) ?: [])), 'correct' => (int)$row['correct'], 'explanation' => (string)$row['explanation']];
}

/* Every player with their place; ties go to whoever answered right faster overall. */
function liveStandings(PDO $db, int $liveId, int $index): array {
    $rows = $db->prepare('SELECT p.user_id, p.score, p.correct, p.streak, p.time_ms, u.display_name,
            a.choice, a.ok, a.points, a.ms
        FROM live_players p JOIN users u ON u.id = p.user_id
        LEFT JOIN live_answers a ON a.live_id = p.live_id AND a.user_id = p.user_id AND a.q_index = ?
        WHERE p.live_id = ? ORDER BY p.score DESC, p.time_ms ASC, p.joined_at ASC');
    $rows->execute([$index, $liveId]);
    $list = $rows->fetchAll();
    /* The places before this question, so the app can show who moved up. */
    $before = $list;
    $prevTime = fn($row) => (int)$row['time_ms'] - ((int)($row['ok'] ?? 0) === 1 ? (int)($row['ms'] ?? 0) : 0);
    usort($before, fn($a, $b) => [(int)$b['score'] - (int)($b['points'] ?? 0), $prevTime($a)] <=> [(int)$a['score'] - (int)($a['points'] ?? 0), $prevTime($b)]);
    $previous = [];
    foreach ($before as $i => $row) $previous[$row['user_id']] = $i + 1;
    $out = [];
    foreach ($list as $i => $row) $out[] = [
        'userId' => $row['user_id'], 'name' => (string)$row['display_name'], 'score' => (int)$row['score'],
        'correct' => (int)$row['correct'], 'streak' => (int)$row['streak'], 'place' => $i + 1,
        'move' => ($previous[$row['user_id']] ?? $i + 1) - ($i + 1),
        'answered' => $row['choice'] !== null, 'choice' => $row['choice'] === null ? null : (int)$row['choice'],
        'ok' => $row['ok'] === null ? null : (int)$row['ok'] === 1, 'points' => (int)($row['points'] ?? 0),
    ];
    return $out;
}

/* What the app or the admin sees right now. Members get the question without its answer
   until the reveal; the admin gets everything. */
function liveState(PDO $db, array $live, ?string $userId, bool $admin = false): array {
    $id = (int)$live['id']; $index = (int)$live['q_index']; $status = $live['status'];
    $total = count(liveQuestionIds($live));
    $standings = liveStandings($db, $id, max(0, $index));
    $me = null;
    foreach ($standings as $row) if ($userId !== null && $row['userId'] === $userId) $me = $row;
    $answered = count(array_filter($standings, fn($row) => $row['answered']));
    $out = [
        'id' => $id, 'quizId' => (int)$live['quiz_id'], 'title' => $live['title'], 'category' => $live['category'],
        'status' => $status, 'index' => $index, 'total' => $total, 'timeSec' => (int)$live['time_sec'],
        'players' => count($standings), 'joined' => $me !== null,
        'names' => array_slice(array_map(fn($row) => $row['name'], $standings), 0, 12),
        'createdAt' => $live['created_at'], 'finishedAt' => $live['finished_at'],
    ];
    if ($me !== null) $out['me'] = ['score' => $me['score'], 'place' => $me['place'], 'correct' => $me['correct'], 'streak' => $me['streak'], 'move' => $me['move']];
    if (in_array($status, ['question', 'reveal'], true)) {
        $question = liveQuestion($db, $live);
        $out['answered'] = $answered;
        if ($question) {
            $out['question'] = ['text' => $question['text'], 'options' => $question['options']];
            if ($status === 'question') $out['remainingMs'] = max(0, (int)$live['q_started_ms'] + (int)$live['time_sec'] * 1000 - liveNowMs());
            if ($me !== null && $me['answered']) $out['myAnswer'] = ['choice' => $me['choice']] + ($status === 'reveal' ? ['ok' => $me['ok'], 'points' => $me['points']] : []);
            if ($status === 'reveal' || $admin) {
                $counts = array_fill(0, count($question['options']), 0);
                foreach ($standings as $row) if ($row['choice'] !== null && isset($counts[$row['choice']])) $counts[$row['choice']]++;
                $out['reveal'] = ['correct' => $question['correct'], 'explanation' => $question['explanation'], 'counts' => $counts,
                    'right' => count(array_filter($standings, fn($row) => $row['ok'] === true))];
            }
        }
    }
    if (in_array($status, ['reveal', 'finished'], true) || $admin) {
        $limit = $admin ? 200 : ($status === 'finished' ? 10 : 5);
        $out['leaders'] = array_map(fn($row) => [
            'name' => $row['name'], 'score' => $row['score'], 'place' => $row['place'], 'correct' => $row['correct'],
            'move' => $row['move'], 'me' => $userId !== null && $row['userId'] === $userId,
        ] + ($admin ? ['answered' => $row['answered']] : []), array_slice($standings, 0, $limit));
    }
    return $out;
}

/* Push to everyone who allowed notifications: «Живой квиз начинается». */
function livePushAll(PDO $db, array $config, string $title, string $body): array {
    require_once __DIR__ . '/push.php';
    $targets = $db->query('SELECT id, endpoint, p256dh, auth FROM push_subscriptions WHERE active = 1')->fetchAll();
    $sent = 0; $failed = 0;
    $disable = $db->prepare('UPDATE push_subscriptions SET active = 0 WHERE id = ?');
    foreach ($targets as $target) {
        try {
            $result = sendWebPush(['endpoint' => $target['endpoint'], 'keys' => ['p256dh' => $target['p256dh'], 'auth' => $target['auth']]],
                ['title' => $title, 'body' => $body, 'url' => '/?open=quiz', 'tag' => 'chetele-live-quiz', 'actions' => [['action' => 'open', 'title' => 'Подключиться']]], $config);
            if ($result['ok']) $sent++;
            else { $failed++; if (in_array($result['status'], [404, 410], true)) $disable->execute([$target['id']]); }
        } catch (Throwable $error) { $failed++; error_log('Chetele live push error: ' . $error->getMessage()); }
    }
    return ['targets' => count($targets), 'sent' => $sent, 'failed' => $failed];
}

function handleLiveRoutes(PDO $db, array $config, string $method, array $parts): void {
    require_once __DIR__ . '/quizzes.php';
    ensureQuizTables($db);
    ensureLiveTables($db);

    /* ---- App ---- */
    if ($method === 'GET' && $parts === ['live', 'current']) {
        $live = liveCurrent($db);
        respond(200, ['live' => $live ? liveState($db, $live, sessionUserId($db)) : null, 'serverNow' => liveNowMs()]);
    }

    if ($method === 'POST' && count($parts) === 3 && $parts[0] === 'live' && in_array($parts[2], ['join', 'answer'], true)) {
        $body = requestBody(); $userId = (string)($body['userId'] ?? '');
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId)) respond(400, ['error' => 'Войдите в профиль, чтобы участвовать.']);
        requireUser($db, $userId);
        $live = liveFind($db, (int)$parts[1]);
        if (!$live || !in_array($live['status'], LIVE_ACTIVE, true)) respond(409, ['error' => 'Квиз уже закончился.']);
        $db->prepare('INSERT IGNORE INTO live_players (live_id, user_id) VALUES (?, ?)')->execute([(int)$live['id'], $userId]);

        if ($parts[2] === 'answer') {
            $index = (int)($body['index'] ?? -1); $choice = (int)($body['choice'] ?? -1);
            if ($live['status'] !== 'question' || $index !== (int)$live['q_index']) respond(409, ['error' => 'Время на этот вопрос вышло.', 'reason' => 'late']);
            $question = liveQuestion($db, $live);
            if (!$question || $choice < 0 || $choice >= count($question['options'])) respond(400, ['error' => 'Некорректный ответ.']);
            $limit = (int)$live['time_sec'] * 1000;
            $ms = liveNowMs() - (int)$live['q_started_ms'];
            if ($ms > $limit + LIVE_GRACE_MS) respond(409, ['error' => 'Время на этот вопрос вышло.', 'reason' => 'late']);
            $ms = max(0, min($limit, $ms));
            $ok = $choice === $question['correct'];
            $points = $ok ? (int)round(1000 - 500 * $ms / max(1, $limit)) : 0;
            $insert = $db->prepare('INSERT IGNORE INTO live_answers (live_id, user_id, q_index, choice, ok, points, ms) VALUES (?,?,?,?,?,?,?)');
            $insert->execute([(int)$live['id'], $userId, $index, $choice, $ok ? 1 : 0, $points, $ms]);
            if ($insert->rowCount() === 0) respond(409, ['error' => 'Ответ на этот вопрос уже принят.', 'reason' => 'twice']);
            $db->prepare($ok ? 'UPDATE live_players SET score = score + ?, correct = correct + 1, streak = streak + 1, time_ms = time_ms + ? WHERE live_id = ? AND user_id = ?'
                             : 'UPDATE live_players SET score = score + ?, streak = 0, time_ms = time_ms + ? WHERE live_id = ? AND user_id = ?')
                ->execute([$points, $ok ? $ms : 0, (int)$live['id'], $userId]);
            /* The answer's result stays hidden until everyone sees the reveal. */
            respond(200, ['accepted' => true]);
        }
        respond(200, ['live' => liveState($db, liveFind($db, (int)$live['id']), $userId)]);
    }

    /* ---- Admin ---- */
    if (($parts[0] ?? '') !== 'admin') respond(404, ['error' => 'Маршрут не найден.']);
    requireAdmin($config, $db);

    if ($method === 'GET' && $parts === ['admin', 'live']) {
        $live = liveCurrent($db);
        $recent = $db->query("SELECT l.id, l.status, l.created_at, l.finished_at, q.title,
                (SELECT COUNT(*) FROM live_players p WHERE p.live_id = l.id) AS players,
                (SELECT u.display_name FROM live_players p JOIN users u ON u.id = p.user_id WHERE p.live_id = l.id ORDER BY p.score DESC, p.time_ms ASC LIMIT 1) AS winner
            FROM live_quizzes l JOIN quizzes q ON q.id = l.quiz_id WHERE l.status = 'finished' ORDER BY l.id DESC LIMIT 5")->fetchAll();
        respond(200, [
            'live' => $live ? liveState($db, $live, null, true) + ($live['status'] === 'question' || $live['status'] === 'reveal' ? ['questionFull' => liveQuestion($db, $live)] : []) : null,
            'recent' => array_map(fn($row) => ['id' => (int)$row['id'], 'title' => $row['title'], 'players' => (int)$row['players'], 'winner' => $row['winner'], 'createdAt' => $row['created_at'], 'finishedAt' => $row['finished_at']], $recent),
            'serverNow' => liveNowMs(),
        ]);
    }

    if ($method === 'POST' && $parts === ['admin', 'live']) {
        $body = requestBody();
        $quizId = (int)($body['quizId'] ?? 0);
        $timeSec = max(5, min(120, (int)($body['timeSec'] ?? 20)));
        $running = $db->query("SELECT COUNT(*) FROM live_quizzes WHERE status IN ('lobby','question','reveal')")->fetchColumn();
        if ((int)$running > 0) respond(409, ['error' => 'Уже идёт живой квиз. Завершите его, чтобы начать новый.']);
        $quiz = $db->prepare("SELECT id, title FROM quizzes WHERE id = ? AND status = 'published'");
        $quiz->execute([$quizId]);
        $found = $quiz->fetch();
        if (!$found) respond(404, ['error' => 'Выберите опубликованный тест.']);
        $ids = $db->prepare('SELECT id FROM quiz_questions WHERE quiz_id = ? ORDER BY position, id');
        $ids->execute([$quizId]);
        $list = array_map('intval', $ids->fetchAll(PDO::FETCH_COLUMN));
        if (!$list) respond(409, ['error' => 'В тесте нет вопросов.']);
        $db->prepare('INSERT INTO live_quizzes (quiz_id, time_sec, question_ids) VALUES (?, ?, ?)')->execute([$quizId, $timeSec, json_encode($list)]);
        $id = (int)$db->lastInsertId();
        $push = null;
        if (!empty($body['push'])) $push = livePushAll($db, $config, 'Живой квиз начинается', '«' . $found['title'] . '» — подключайтесь во вкладке «Тесты».');
        respond(201, ['id' => $id, 'push' => $push]);
    }

    if ($method === 'POST' && count($parts) === 4 && $parts[1] === 'live') {
        $live = liveFind($db, (int)$parts[2]);
        if (!$live) respond(404, ['error' => 'Квиз не найден.']);
        $id = (int)$live['id']; $action = $parts[3]; $total = count(liveQuestionIds($live));
        if ($action === 'push') {
            if (!in_array($live['status'], LIVE_ACTIVE, true)) respond(409, ['error' => 'Квиз уже закончился.']);
            respond(200, livePushAll($db, $config, 'Живой квиз идёт', '«' . $live['title'] . '» — подключайтесь во вкладке «Тесты».'));
        }
        if ($action === 'start' || $action === 'next') {
            $from = $action === 'start' ? 'lobby' : 'reveal';
            if ($live['status'] !== $from) respond(409, ['error' => $action === 'start' ? 'Квиз уже начался.' : 'Сначала покажите ответ на текущий вопрос.']);
            $nextIndex = (int)$live['q_index'] + 1;
            if ($nextIndex >= $total) {
                $db->prepare("UPDATE live_quizzes SET status = 'finished', finished_at = NOW() WHERE id = ?")->execute([$id]);
            } else {
                $db->prepare("UPDATE live_quizzes SET status = 'question', q_index = ?, q_started_ms = ? WHERE id = ? AND status = ?")->execute([$nextIndex, liveNowMs(), $id, $from]);
            }
        } elseif ($action === 'reveal') {
            if ($live['status'] !== 'question') respond(409, ['error' => 'Сейчас нет открытого вопроса.']);
            liveReveal($db, $live);
        } elseif ($action === 'finish' || $action === 'cancel') {
            if (!in_array($live['status'], LIVE_ACTIVE, true)) respond(409, ['error' => 'Квиз уже закончился.']);
            /* Ending with no answers given at all is a cancellation: no podium to show. */
            $status = $action === 'cancel' || (int)$live['q_index'] < 0 ? 'cancelled' : 'finished';
            if ($live['status'] === 'question') liveReveal($db, $live);
            $db->prepare('UPDATE live_quizzes SET status = ?, finished_at = NOW() WHERE id = ?')->execute([$status, $id]);
        } else respond(404, ['error' => 'Маршрут не найден.']);
        $fresh = liveFind($db, $id);
        respond(200, ['live' => liveState($db, $fresh, null, true) + (in_array($fresh['status'], ['question', 'reveal'], true) ? ['questionFull' => liveQuestion($db, $fresh)] : []), 'serverNow' => liveNowMs()]);
    }

    respond(404, ['error' => 'Маршрут не найден.']);
}
