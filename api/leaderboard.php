<?php
/* Leaderboard maths without the database: what a day counts for each metric, places
   with ties, streaks and badges for a participant's card. Used by api/index.php and
   tests/leaderboard.php. */

const LEADERBOARD_METRICS = ['points', 'salawat', 'zikr', 'quran', 'tahajjud', 'nafil'];

/* What one saved day adds to a metric. $tasks is the stored tasks JSON, $points the
   stored points of the day. */
function leaderboardDayScore(string $metric, array $tasks, int $points): int {
    $amount = static fn($value): int => max(0, (int)(is_numeric($value) ? $value : 0));
    return match ($metric) {
        'salawat' => $amount($tasks['salawat'] ?? 0),
        'zikr' => $amount($tasks['tasbihat'] ?? 0),
        'quran' => $amount($tasks['quran'] ?? 0),
        'tahajjud' => ($tasks['tahajjud'] ?? '') === 'yes' ? 1 : 0,
        'nafil' => count(array_filter(is_array($tasks['nafil'] ?? null) ? $tasks['nafil'] : [])),
        default => max(0, $points),
    };
}

/* Orders participants by score (then by who joined first) and gives each a place where
   equal scores share it: 120, 90, 90, 40 → 1, 2, 2, 4.
   $people: id => ['score' => int, 'created' => string]. Returns ordered ids and places. */
function leaderboardRank(array $people): array {
    $ids = array_keys($people);
    usort($ids, static fn($a, $b) => [$people[$b]['score'], $people[$a]['created'], (string)$a] <=> [$people[$a]['score'], $people[$b]['created'], (string)$b]);
    $places = [];
    foreach ($ids as $index => $id) {
        $previous = $ids[$index - 1] ?? null;
        $places[$id] = $previous !== null && $people[$previous]['score'] === $people[$id]['score'] ? $places[$previous] : $index + 1;
    }
    return ['order' => $ids, 'places' => $places];
}

/* How many places someone rose (positive) or fell since the previous period. Nobody
   "moves" while they have nothing yet: shifts among people with zero are just ties. */
function leaderboardMove(?int $before, int $now, int $score): int {
    return $before === null || $score === 0 ? 0 : $before - $now;
}

/* Current and best runs of days with at least one deed. $days: date => completed count,
   in any order; $today: Y-m-d. A run still counts as current if yesterday was its last day. */
function leaderboardStreaks(array $days, string $today): array {
    $active = array_keys(array_filter($days, static fn($count) => $count > 0));
    sort($active);
    $best = 0; $run = 0; $previous = null;
    foreach ($active as $date) {
        $run = $previous !== null && date('Y-m-d', strtotime($previous . ' +1 day')) === $date ? $run + 1 : 1;
        $best = max($best, $run);
        $previous = $date;
    }
    $last = end($active) ?: null;
    $yesterday = date('Y-m-d', strtotime($today . ' -1 day'));
    return ['current' => $last === $today || $last === $yesterday ? $run : 0, 'best' => $best, 'days' => count($active)];
}

/* The same six badges as the app's profile, from a person's saved days.
   $entries: list of ['date' => Y-m-d, 'tasks' => array, 'completed' => int], any order. */
function leaderboardBadges(array $entries, int $dailyMax = 11): array {
    usort($entries, static fn($a, $b) => strcmp($a['date'], $b['date']));
    $longest = 0; $run = 0; $fullRun = 0; $longestFull = 0; $nightRun = 0; $longestNights = 0;
    $quran = 0; $salawat = 0; $days = 0; $previous = null;
    foreach ($entries as $entry) {
        $next = $previous !== null && date('Y-m-d', strtotime($previous . ' +1 day')) === $entry['date'];
        $done = (int)$entry['completed'];
        $run = $done > 0 ? ($next ? $run + 1 : 1) : 0; $longest = max($longest, $run);
        $fullRun = $done >= $dailyMax ? ($next ? $fullRun + 1 : 1) : 0; $longestFull = max($longestFull, $fullRun);
        $night = ($entry['tasks']['tahajjud'] ?? '') === 'yes';
        $nightRun = $night ? ($next ? $nightRun + 1 : 1) : 0; $longestNights = max($longestNights, $nightRun);
        $quran += leaderboardDayScore('quran', $entry['tasks'], 0);
        $salawat += leaderboardDayScore('salawat', $entry['tasks'], 0);
        $days += $done > 0 ? 1 : 0;
        $previous = $entry['date'];
    }
    return [
        ['title' => 'Ваш ритм', 'earned' => $longest >= 3],
        ['title' => 'Полная неделя', 'earned' => $longestFull >= 7],
        ['title' => 'Путь Корана', 'earned' => $quran >= 604],
        ['title' => 'Тахажуд', 'earned' => $longestNights >= 40],
        ['title' => 'Салават', 'earned' => $salawat >= 100000],
        ['title' => 'Постоянство', 'earned' => $days >= 21],
    ];
}

/* Dates a period covers, and the period before it for the move arrows:
   week = the last 7 days and the 7 before; month = this month so far and last month;
   all time = everything, compared with everything up to a week ago. null = no limit. */
function leaderboardWindow(string $period, string $today): array {
    $shift = static fn(string $days): string => date('Y-m-d', strtotime("$today $days day"));
    if ($period === 'week') return ['from' => $shift('-6'), 'to' => $today, 'beforeFrom' => $shift('-13'), 'beforeTo' => $shift('-7')];
    if ($period === 'month') {
        $first = substr($today, 0, 8) . '01';
        return ['from' => $first, 'to' => $today, 'beforeFrom' => date('Y-m-01', strtotime("$first -1 month")), 'beforeTo' => date('Y-m-d', strtotime("$first -1 day"))];
    }
    return ['from' => null, 'to' => $today, 'beforeFrom' => null, 'beforeTo' => $shift('-7')];
}

/* A stable public key for a participant: the id never leaves the server. */
function leaderboardKey(string $userId, string $secret): string {
    return substr(hash_hmac('sha256', $userId, $secret), 0, 16);
}
