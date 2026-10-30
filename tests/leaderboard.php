<?php
// Run: php tests/leaderboard.php
require __DIR__ . '/../api/leaderboard.php';

$failed = 0;
function check(string $name, $actual, $expected): void {
    global $failed;
    if ($actual === $expected) { echo "ok   $name\n"; return; }
    $failed++;
    echo "FAIL $name\n  expected " . json_encode($expected, JSON_UNESCAPED_UNICODE) . "\n  actual   " . json_encode($actual, JSON_UNESCAPED_UNICODE) . "\n";
}

$tasks = ['salawat' => 100, 'tasbihat' => 33, 'quran' => 5, 'tahajjud' => 'yes', 'nafil' => ['asr' => true, 'kuptan' => true, 'avvabin' => false, 'duha' => false]];
check('a day counts for each metric', array_map(fn($m) => leaderboardDayScore($m, $tasks, 70), LEADERBOARD_METRICS), [70, 100, 33, 5, 1, 2]);
check('an unknown metric counts points', leaderboardDayScore('nonsense', $tasks, 70), 70);

$ranked = leaderboardRank([
    'a' => ['score' => 90, 'created' => '2026-03-02'],
    'b' => ['score' => 120, 'created' => '2026-03-05'],
    'c' => ['score' => 90, 'created' => '2026-03-01'],
    'd' => ['score' => 40, 'created' => '2026-03-01'],
]);
check('higher scores first, earlier sign-up wins a tie in the order', $ranked['order'], ['b', 'c', 'a', 'd']);
check('equal scores share a place', $ranked['places'], ['b' => 1, 'c' => 2, 'a' => 2, 'd' => 4]);

check('rising two places', leaderboardMove(5, 3, 40), 2);
check('nobody moves with zero', leaderboardMove(5, 7, 0), 0);
check('a newcomer has no move', leaderboardMove(null, 3, 40), 0);

$days = ['2026-10-25' => 3, '2026-10-26' => 1, '2026-10-27' => 0, '2026-10-28' => 2, '2026-10-29' => 4];
check('streak ending yesterday is current', leaderboardStreaks($days, '2026-10-30'), ['current' => 2, 'best' => 2, 'days' => 4]);
check('streak broken two days ago is not current', leaderboardStreaks($days, '2026-10-31')['current'], 0);

$entries = [];
for ($i = 0; $i < 21; $i++) $entries[] = ['date' => date('Y-m-d', strtotime("2026-10-01 +$i day")), 'tasks' => ['quran' => 30], 'completed' => 3];
check('badges from saved days', array_column(leaderboardBadges($entries), 'earned', 'title'), ['Ваш ритм' => true, 'Полная неделя' => false, 'Путь Корана' => true, 'Тахажуд' => false, 'Салават' => false, 'Постоянство' => true]);

check('week window', leaderboardWindow('week', '2026-10-30'), ['from' => '2026-10-24', 'to' => '2026-10-30', 'beforeFrom' => '2026-10-17', 'beforeTo' => '2026-10-23']);
check('month window', leaderboardWindow('month', '2026-03-10'), ['from' => '2026-03-01', 'to' => '2026-03-10', 'beforeFrom' => '2026-02-01', 'beforeTo' => '2026-02-28']);
check('all time window', leaderboardWindow('', '2026-10-30'), ['from' => null, 'to' => '2026-10-30', 'beforeFrom' => null, 'beforeTo' => '2026-10-23']);

check('key is stable and hides the id', leaderboardKey('user-1', 'secret'), leaderboardKey('user-1', 'secret'));
check('key is 16 characters', strlen(leaderboardKey('user-1', 'secret')), 16);

echo $failed ? "\n$failed failed\n" : "\nall passed\n";
exit($failed ? 1 : 0);
