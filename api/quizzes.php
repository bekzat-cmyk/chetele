<?php
declare(strict_types=1);

/* Quizzes («Тесты»): the admin writes tests of single-choice questions, members take
   them in the app. Answers are graded here, so results and statistics are the server's.
   Questions are sent with their right answers because the app shows the answer and
   its explanation right after each question; a quiz gives no rating points, so there
   is nothing to gain by reading them early. */

const QUIZ_CATEGORIES = ['Намаз', 'Куран', 'Пост', 'Сира', 'Основы', 'Другое'];
const QUIZ_DEFAULT_LEVELS = ['Начинающий', 'Знающий', 'Хорошие знания', 'Отлично'];

function ensureQuizTables(PDO $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS quizzes (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(120) NOT NULL,
        description VARCHAR(600) NOT NULL DEFAULT \'\',
        category VARCHAR(24) NOT NULL DEFAULT \'Основы\',
        status VARCHAR(12) NOT NULL DEFAULT \'draft\',
        featured TINYINT(1) NOT NULL DEFAULT 0,
        show_answers TINYINT(1) NOT NULL DEFAULT 1,
        shuffle_questions TINYINT(1) NOT NULL DEFAULT 1,
        shuffle_options TINYINT(1) NOT NULL DEFAULT 0,
        show_stats TINYINT(1) NOT NULL DEFAULT 1,
        time_limit_min SMALLINT UNSIGNED NULL,
        question_time_sec SMALLINT UNSIGNED NULL,
        levels TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_quizzes_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    /* Seconds to answer each question; NULL means no limit. */
    $columns = $db->query('SHOW COLUMNS FROM quizzes')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('question_time_sec', $columns, true)) $db->exec('ALTER TABLE quizzes ADD COLUMN question_time_sec SMALLINT UNSIGNED NULL AFTER time_limit_min');
    $db->exec('CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT UNSIGNED NOT NULL,
        position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        text VARCHAR(600) NOT NULL,
        options TEXT NOT NULL,
        correct TINYINT UNSIGNED NOT NULL,
        explanation VARCHAR(800) NOT NULL DEFAULT \'\',
        KEY idx_quiz_questions_quiz (quiz_id, position),
        CONSTRAINT fk_quiz_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    $db->exec('CREATE TABLE IF NOT EXISTS quiz_attempts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT UNSIGNED NOT NULL,
        user_id CHAR(36) NOT NULL,
        correct SMALLINT UNSIGNED NOT NULL,
        total SMALLINT UNSIGNED NOT NULL,
        duration_sec INT UNSIGNED NULL,
        answers TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_quiz_attempts_quiz (quiz_id, created_at),
        KEY idx_quiz_attempts_user (user_id, quiz_id),
        CONSTRAINT fk_quiz_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
        CONSTRAINT fk_quiz_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function quizLevels($raw): array {
    $levels = is_string($raw) ? json_decode($raw, true) : $raw;
    $out = [];
    foreach (QUIZ_DEFAULT_LEVELS as $i => $default) {
        $name = is_array($levels) && isset($levels[$i]) && is_string($levels[$i]) ? trim($levels[$i]) : '';
        $out[] = $name !== '' ? mb_substr($name, 0, 40, 'UTF-8') : $default;
    }
    return $out;
}

/* Level by share of right answers: under 50 %, under 80 %, under 100 %, all right. */
function quizLevelIndex(int $correct, int $total): int {
    if ($total < 1) return 0;
    if ($correct >= $total) return 3;
    $share = $correct / $total;
    return $share >= 0.8 ? 2 : ($share >= 0.5 ? 1 : 0);
}

function quizQuestions(PDO $db, int $quizId): array {
    $statement = $db->prepare('SELECT id, text, options, correct, explanation FROM quiz_questions WHERE quiz_id = ? ORDER BY position, id');
    $statement->execute([$quizId]);
    return array_map(fn($row) => [
        'id' => (int)$row['id'],
        'text' => $row['text'],
        'options' => array_values(array_map('strval', json_decode((string)$row['options'], true) ?: [])),
        'correct' => (int)$row['correct'],
        'explanation' => $row['explanation'],
    ], $statement->fetchAll());
}

function quizPublic(array $quiz): array {
    return [
        'id' => (int)$quiz['id'], 'title' => $quiz['title'], 'description' => $quiz['description'],
        'category' => $quiz['category'], 'featured' => (int)$quiz['featured'] === 1,
        'showAnswers' => (int)$quiz['show_answers'] === 1, 'shuffleQuestions' => (int)$quiz['shuffle_questions'] === 1,
        'shuffleOptions' => (int)$quiz['shuffle_options'] === 1, 'showStats' => (int)$quiz['show_stats'] === 1,
        'timeLimitMin' => $quiz['time_limit_min'] === null ? null : (int)$quiz['time_limit_min'],
        'questionTimeSec' => ($quiz['question_time_sec'] ?? null) === null ? null : (int)$quiz['question_time_sec'],
        'levels' => quizLevels($quiz['levels']),
    ];
}

/* Reads and checks a quiz from the admin's request body. */
function quizInput(array $body): array {
    $title = trim((string)($body['title'] ?? ''));
    if ($title === '' || mb_strlen($title, 'UTF-8') > 120) respond(400, ['error' => 'Название теста — от 1 до 120 символов.']);
    $category = (string)($body['category'] ?? 'Основы');
    if (!in_array($category, QUIZ_CATEGORIES, true)) $category = 'Другое';
    $status = (string)($body['status'] ?? 'draft');
    if (!in_array($status, ['draft', 'published', 'hidden'], true)) $status = 'draft';
    $limit = $body['timeLimitMin'] ?? null;
    $limit = $limit === null || $limit === '' ? null : max(1, min(180, (int)$limit));
    /* Time to answer each question: 5 seconds to 10 minutes, or no limit. */
    $perQuestion = $body['questionTimeSec'] ?? null;
    $perQuestion = $perQuestion === null || $perQuestion === '' || (int)$perQuestion <= 0 ? null : max(5, min(600, (int)$perQuestion));
    $questions = $body['questions'] ?? [];
    if (!is_array($questions) || count($questions) > 100) respond(400, ['error' => 'В тесте может быть до 100 вопросов.']);
    $clean = [];
    foreach (array_values($questions) as $index => $question) {
        $number = $index + 1;
        $text = trim((string)($question['text'] ?? ''));
        if ($text === '' || mb_strlen($text, 'UTF-8') > 600) respond(400, ['error' => "Вопрос {$number}: введите текст (до 600 символов)."]);
        $options = [];
        foreach ((array)($question['options'] ?? []) as $option) {
            $option = trim((string)$option);
            if ($option !== '') $options[] = mb_substr($option, 0, 200, 'UTF-8');
        }
        if (count($options) < 2 || count($options) > 6) respond(400, ['error' => "Вопрос {$number}: нужно от 2 до 6 вариантов ответа."]);
        $correct = (int)($question['correct'] ?? -1);
        if ($correct < 0 || $correct >= count($options)) respond(400, ['error' => "Вопрос {$number}: отметьте верный ответ."]);
        $id = (int)($question['id'] ?? 0);
        $clean[] = ['id' => $id > 0 ? $id : null, 'text' => $text, 'options' => $options, 'correct' => $correct, 'explanation' => mb_substr(trim((string)($question['explanation'] ?? '')), 0, 800, 'UTF-8')];
    }
    if ($status === 'published' && count($clean) < 1) respond(400, ['error' => 'Добавьте хотя бы один вопрос, чтобы опубликовать тест.']);
    return [
        'title' => $title,
        'description' => mb_substr(trim((string)($body['description'] ?? '')), 0, 600, 'UTF-8'),
        'category' => $category, 'status' => $status,
        'featured' => ($body['featured'] ?? false) === true ? 1 : 0,
        'show_answers' => ($body['showAnswers'] ?? true) === false ? 0 : 1,
        'shuffle_questions' => ($body['shuffleQuestions'] ?? true) === false ? 0 : 1,
        'shuffle_options' => ($body['shuffleOptions'] ?? false) === true ? 1 : 0,
        'show_stats' => ($body['showStats'] ?? true) === false ? 0 : 1,
        'time_limit_min' => $limit,
        'question_time_sec' => $perQuestion,
        'levels' => json_encode(quizLevels($body['levels'] ?? null), JSON_UNESCAPED_UNICODE),
        'questions' => $clean,
    ];
}

/* Saves questions in order. Questions keep their ids when edited, so statistics per
   question survive; questions left out are deleted. */
function saveQuizQuestions(PDO $db, int $quizId, array $questions): void {
    $keep = [];
    $update = $db->prepare('UPDATE quiz_questions SET position=?, text=?, options=?, correct=?, explanation=? WHERE id=? AND quiz_id=?');
    $insert = $db->prepare('INSERT INTO quiz_questions (quiz_id, position, text, options, correct, explanation) VALUES (?,?,?,?,?,?)');
    $exists = $db->prepare('SELECT 1 FROM quiz_questions WHERE id=? AND quiz_id=?');
    foreach ($questions as $position => $question) {
        $options = json_encode($question['options'], JSON_UNESCAPED_UNICODE);
        $id = $question['id'];
        if ($id !== null) { $exists->execute([$id, $quizId]); if (!$exists->fetchColumn()) $id = null; }
        if ($id !== null) {
            $update->execute([$position, $question['text'], $options, $question['correct'], $question['explanation'], $id, $quizId]);
        } else {
            $insert->execute([$quizId, $position, $question['text'], $options, $question['correct'], $question['explanation']]);
            $id = (int)$db->lastInsertId();
        }
        $keep[] = (int)$id;
    }
    if ($keep) {
        $marks = implode(',', array_fill(0, count($keep), '?'));
        $db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND id NOT IN ({$marks})")->execute([$quizId, ...$keep]);
    } else {
        $db->prepare('DELETE FROM quiz_questions WHERE quiz_id=?')->execute([$quizId]);
    }
}

/* Count, average share of right answers and number of people for each quiz. */
function quizAttemptStats(PDO $db): array {
    $rows = $db->query('SELECT quiz_id, COUNT(*) AS attempts, COUNT(DISTINCT user_id) AS people, AVG(correct / NULLIF(total,0)) AS share,
        SUM(created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS attempts30 FROM quiz_attempts GROUP BY quiz_id')->fetchAll();
    $out = [];
    foreach ($rows as $row) $out[(int)$row['quiz_id']] = ['attempts' => (int)$row['attempts'], 'people' => (int)$row['people'], 'avg' => $row['share'] === null ? null : (int)round((float)$row['share'] * 100), 'attempts30' => (int)$row['attempts30']];
    return $out;
}

/* Handles every quiz route; returns false when the path is not a quiz route. */
function handleQuizRoutes(PDO $db, array $config, string $method, array $parts): bool {
    $isQuiz = ($parts[0] ?? '') === 'quizzes' || (($parts[0] ?? '') === 'admin' && ($parts[1] ?? '') === 'quizzes');
    if (!$isQuiz) return false;
    ensureQuizTables($db);

    /* ---- App ---- */
    if ($method === 'GET' && $parts === ['quizzes']) {
        $userId = (string)(sessionUserId($db) ?? '');
        $quizzes = $db->query("SELECT q.*, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id=q.id) AS question_count FROM quizzes q WHERE q.status='published' ORDER BY q.featured DESC, q.created_at DESC")->fetchAll();
        $stats = quizAttemptStats($db);
        $mine = [];
        if ($userId !== '') {
            $statement = $db->prepare('SELECT quiz_id, MAX(correct) AS best, COUNT(*) AS tries, MAX(created_at) AS last_at FROM quiz_attempts WHERE user_id=? GROUP BY quiz_id');
            $statement->execute([$userId]);
            foreach ($statement->fetchAll() as $row) $mine[(int)$row['quiz_id']] = $row;
        }
        $out = [];
        foreach ($quizzes as $quiz) {
            $id = (int)$quiz['id'];
            $item = quizPublic($quiz) + ['questionCount' => (int)$quiz['question_count']];
            $stat = $stats[$id] ?? null;
            $item['people'] = $item['showStats'] && $stat ? $stat['people'] : null;
            $item['avgPercent'] = $item['showStats'] && $stat ? $stat['avg'] : null;
            $item['myBest'] = isset($mine[$id]) ? (int)$mine[$id]['best'] : null;
            $item['myTries'] = isset($mine[$id]) ? (int)$mine[$id]['tries'] : 0;
            $out[] = $item;
        }
        respond(200, $out);
    }

    if ($method === 'GET' && count($parts) === 2 && $parts[0] === 'quizzes') {
        $find = $db->prepare("SELECT * FROM quizzes WHERE id=? AND status='published'");
        $find->execute([(int)$parts[1]]);
        $quiz = $find->fetch();
        if (!$quiz) respond(404, ['error' => 'Тест недоступен.']);
        respond(200, quizPublic($quiz) + ['questions' => quizQuestions($db, (int)$quiz['id'])]);
    }

    /* A finished attempt: the server grades it and keeps it. Guests take tests without saving. */
    if ($method === 'POST' && count($parts) === 3 && $parts[0] === 'quizzes' && $parts[2] === 'attempts') {
        $quizId = (int)$parts[1]; $body = requestBody(); $userId = (string)($body['userId'] ?? '');
        if (!preg_match('/^[a-f0-9-]{36}$/i', $userId)) respond(400, ['error' => 'Некорректный пользователь.']);
        requireUser($db, $userId);
        $find = $db->prepare("SELECT * FROM quizzes WHERE id=? AND status='published'");
        $find->execute([$quizId]);
        $quiz = $find->fetch();
        if (!$quiz) respond(404, ['error' => 'Тест недоступен.']);
        $questions = quizQuestions($db, $quizId);
        if (!$questions) respond(409, ['error' => 'В тесте нет вопросов.']);
        $given = [];
        foreach ((array)($body['answers'] ?? []) as $answer) {
            if (is_array($answer) && isset($answer['questionId'])) $given[(int)$answer['questionId']] = (int)($answer['choice'] ?? -1);
        }
        $correct = 0; $graded = [];
        foreach ($questions as $question) {
            $choice = $given[$question['id']] ?? -1;
            $right = $choice === $question['correct'];
            if ($right) $correct++;
            $graded[] = ['q' => $question['id'], 'c' => $choice, 'ok' => $right ? 1 : 0];
        }
        $recent = $db->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE user_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        $recent->execute([$userId]);
        if ((int)$recent->fetchColumn() >= 60) respond(429, ['error' => 'Слишком много попыток за час.']);
        $duration = isset($body['durationSec']) ? max(0, min(86400, (int)$body['durationSec'])) : null;
        $db->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, correct, total, duration_sec, answers) VALUES (?,?,?,?,?,?)')
            ->execute([$quizId, $userId, $correct, count($questions), $duration, json_encode($graded)]);
        $best = $db->prepare('SELECT MAX(correct) FROM quiz_attempts WHERE user_id=? AND quiz_id=?');
        $best->execute([$userId, $quizId]);
        $levels = quizLevels($quiz['levels']);
        respond(201, ['correct' => $correct, 'total' => count($questions), 'best' => (int)$best->fetchColumn(), 'level' => $levels[quizLevelIndex($correct, count($questions))]]);
    }

    /* ---- Admin ---- */
    if (($parts[0] ?? '') !== 'admin') respond(404, ['error' => 'Маршрут не найден.']);
    requireAdmin($config, $db);

    if ($method === 'GET' && $parts === ['admin', 'quizzes']) {
        $quizzes = $db->query('SELECT q.*, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id=q.id) AS question_count FROM quizzes q ORDER BY q.created_at DESC')->fetchAll();
        $stats = quizAttemptStats($db);
        $out = [];
        foreach ($quizzes as $quiz) {
            $id = (int)$quiz['id'];
            $out[] = quizPublic($quiz) + ['status' => $quiz['status'], 'questionCount' => (int)$quiz['question_count'], 'createdAt' => $quiz['created_at'], 'updatedAt' => $quiz['updated_at']] + ($stats[$id] ?? ['attempts' => 0, 'people' => 0, 'avg' => null, 'attempts30' => 0]);
        }
        $totals = $db->query('SELECT COUNT(*) AS attempts30, COUNT(DISTINCT user_id) AS people30 FROM quiz_attempts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetch();
        $everyone = (int)$db->query('SELECT COUNT(DISTINCT user_id) FROM quiz_attempts')->fetchColumn();
        $avg = $db->query('SELECT AVG(correct / NULLIF(total,0)) FROM quiz_attempts')->fetchColumn();
        $users = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        respond(200, ['quizzes' => $out, 'summary' => ['attempts30' => (int)$totals['attempts30'], 'people30' => (int)$totals['people30'], 'peopleAll' => $everyone, 'users' => $users, 'avg' => $avg === null ? null : (int)round((float)$avg * 100)]]);
    }

    if ($method === 'GET' && count($parts) === 3 && $parts[1] === 'quizzes') {
        $find = $db->prepare('SELECT * FROM quizzes WHERE id=?');
        $find->execute([(int)$parts[2]]);
        $quiz = $find->fetch();
        if (!$quiz) respond(404, ['error' => 'Тест не найден.']);
        $questions = quizQuestions($db, (int)$quiz['id']);
        /* Results by number of right answers, and the share of wrong answers per question. */
        $attempts = $db->prepare('SELECT correct, total, duration_sec, answers FROM quiz_attempts WHERE quiz_id=?');
        $attempts->execute([(int)$quiz['id']]);
        $distribution = []; $wrong = []; $seen = []; $durations = []; $perfect = 0; $count = 0; $share = 0.0;
        foreach ($attempts->fetchAll() as $attempt) {
            $count++;
            $distribution[(int)$attempt['correct']] = ($distribution[(int)$attempt['correct']] ?? 0) + 1;
            if ((int)$attempt['total'] > 0) { $share += (int)$attempt['correct'] / (int)$attempt['total']; if ((int)$attempt['correct'] >= (int)$attempt['total']) $perfect++; }
            if ($attempt['duration_sec'] !== null) $durations[] = (int)$attempt['duration_sec'];
            foreach (json_decode((string)$attempt['answers'], true) ?: [] as $answer) {
                $q = (int)($answer['q'] ?? 0); $seen[$q] = ($seen[$q] ?? 0) + 1;
                if (empty($answer['ok'])) $wrong[$q] = ($wrong[$q] ?? 0) + 1;
            }
        }
        $hardest = [];
        foreach ($questions as $question) {
            $n = $seen[$question['id']] ?? 0;
            if ($n > 0) $hardest[] = ['id' => $question['id'], 'text' => $question['text'], 'wrongPercent' => (int)round(($wrong[$question['id']] ?? 0) / $n * 100), 'answers' => $n];
        }
        usort($hardest, fn($a, $b) => $b['wrongPercent'] <=> $a['wrongPercent']);
        sort($durations);
        respond(200, quizPublic($quiz) + [
            'status' => $quiz['status'], 'createdAt' => $quiz['created_at'], 'questions' => $questions,
            'stats' => ['attempts' => $count, 'avg' => $count ? (int)round($share / $count * 100) : null, 'perfect' => $perfect,
                'medianSec' => $durations ? $durations[intdiv(count($durations), 2)] : null,
                'distribution' => $distribution, 'hardest' => array_slice($hardest, 0, 5)],
        ]);
    }

    if ($method === 'POST' && $parts === ['admin', 'quizzes']) {
        $input = quizInput(requestBody());
        $db->beginTransaction();
        try {
            if ($input['featured']) $db->exec('UPDATE quizzes SET featured=0');
            $db->prepare('INSERT INTO quizzes (title, description, category, status, featured, show_answers, shuffle_questions, shuffle_options, show_stats, time_limit_min, question_time_sec, levels) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$input['title'], $input['description'], $input['category'], $input['status'], $input['featured'], $input['show_answers'], $input['shuffle_questions'], $input['shuffle_options'], $input['show_stats'], $input['time_limit_min'], $input['question_time_sec'], $input['levels']]);
            $id = (int)$db->lastInsertId();
            saveQuizQuestions($db, $id, $input['questions']);
            $db->commit();
        } catch (Throwable $error) { if ($db->inTransaction()) $db->rollBack(); throw $error; }
        respond(201, ['id' => $id]);
    }

    if ($method === 'PUT' && count($parts) === 3 && $parts[1] === 'quizzes') {
        $id = (int)$parts[2];
        $exists = $db->prepare('SELECT 1 FROM quizzes WHERE id=?'); $exists->execute([$id]);
        if (!$exists->fetchColumn()) respond(404, ['error' => 'Тест не найден.']);
        $input = quizInput(requestBody());
        $db->beginTransaction();
        try {
            if ($input['featured']) $db->prepare('UPDATE quizzes SET featured=0 WHERE id<>?')->execute([$id]);
            $db->prepare('UPDATE quizzes SET title=?, description=?, category=?, status=?, featured=?, show_answers=?, shuffle_questions=?, shuffle_options=?, show_stats=?, time_limit_min=?, question_time_sec=?, levels=? WHERE id=?')
                ->execute([$input['title'], $input['description'], $input['category'], $input['status'], $input['featured'], $input['show_answers'], $input['shuffle_questions'], $input['shuffle_options'], $input['show_stats'], $input['time_limit_min'], $input['question_time_sec'], $input['levels'], $id]);
            saveQuizQuestions($db, $id, $input['questions']);
            $db->commit();
        } catch (Throwable $error) { if ($db->inTransaction()) $db->rollBack(); throw $error; }
        respond(200, ['updated' => true]);
    }

    if ($method === 'POST' && count($parts) === 4 && $parts[1] === 'quizzes' && $parts[3] === 'status') {
        $id = (int)$parts[2]; $status = (string)(requestBody()['status'] ?? '');
        if (!in_array($status, ['draft', 'published', 'hidden'], true)) respond(400, ['error' => 'Некорректный статус.']);
        if ($status === 'published') {
            $count = $db->prepare('SELECT COUNT(*) FROM quiz_questions WHERE quiz_id=?'); $count->execute([$id]);
            if ((int)$count->fetchColumn() < 1) respond(409, ['error' => 'Добавьте хотя бы один вопрос, чтобы опубликовать тест.']);
        }
        $statement = $db->prepare('UPDATE quizzes SET status=? WHERE id=?'); $statement->execute([$status, $id]);
        $exists = $db->prepare('SELECT 1 FROM quizzes WHERE id=?'); $exists->execute([$id]);
        if (!$exists->fetchColumn()) respond(404, ['error' => 'Тест не найден.']);
        respond(200, ['status' => $status]);
    }

    if ($method === 'DELETE' && count($parts) === 3 && $parts[1] === 'quizzes') {
        $statement = $db->prepare('DELETE FROM quizzes WHERE id=?'); $statement->execute([(int)$parts[2]]);
        if ($statement->rowCount() === 0) respond(404, ['error' => 'Тест не найден.']);
        respond(200, ['deleted' => true]);
    }

    respond(404, ['error' => 'Маршрут не найден.']);
}
