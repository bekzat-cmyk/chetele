<?php
// Run: php tests/zikr_test.php
declare(strict_types=1);
require __DIR__ . '/../api/zikr.php';

$failures = 0;
function check(string $name, $expected, $actual): void {
    global $failures;
    if ($expected === $actual) { echo "ok   - $name\n"; return; }
    $failures++;
    echo "FAIL - $name\n  expected: " . json_encode($expected) . "\n  actual:   " . json_encode($actual) . "\n";
}

check('clean map keeps valid positive counts only',
    ['subhanallah' => 33, 'salawat' => 5],
    cleanZikrMap(['subhanallah' => 33, 'salawat' => '5', 'Bad Key' => 3, 'zero' => 0, 'neg' => -2, 'big' => 'x']));

check('clean map rejects non-arrays', [], cleanZikrMap('nope'));

check('totals split salawat from every other zikr',
    ['salawat' => 7, 'tasbihat' => 43],
    zikrTotals(['salawat' => 7, 'subhanallah' => 33, 'istighfar' => 10]));

check('manual part is what the day total holds beyond the counter',
    ['salawat' => 100, 'tasbihat' => 0],
    manualPart(['salawat' => 107, 'tasbihat' => 33], ['salawat' => 7, 'subhanallah' => 33]));

check('manual part never goes below zero',
    ['salawat' => 0, 'tasbihat' => 0],
    manualPart(['salawat' => 2, 'tasbihat' => 0], ['salawat' => 7, 'subhanallah' => 33]));

check('totals are rebuilt from the manual part and the zikr map',
    ['salawat' => 105, 'tasbihat' => 40, 'zikr' => ['salawat' => 5, 'subhanallah' => 40]],
    array_intersect_key(withZikr(['salawat' => 1, 'tasbihat' => 1, 'quran' => 3], ['salawat' => 100, 'tasbihat' => 0], ['salawat' => 5, 'subhanallah' => 40]), ['salawat' => 1, 'tasbihat' => 1, 'zikr' => 1]));

check('other deeds are left untouched',
    3,
    withZikr(['quran' => 3], ['salawat' => 0, 'tasbihat' => 0], [])['quran']);

check('deltas add to the stored counts and drop zeros',
    ['subhanallah' => 38],
    applyZikrDeltas(['subhanallah' => 33, 'salawat' => 2], ['subhanallah' => 5, 'salawat' => -2]));

check('deltas never push a count below zero',
    [],
    applyZikrDeltas(['subhanallah' => 3], ['subhanallah' => -10]));

check('invalid deltas are ignored',
    ['subhanallah' => 1],
    applyZikrDeltas([], ['subhanallah' => 1, 'Bad Key' => 5, 'x' => 'abc', 'y' => 1.5]));

// Two phones on one day: the counter on phone A must survive a stale sync from phone B.
$day = ['salawat' => 100, 'tasbihat' => 0, 'zikr' => []];
$zikrA = applyZikrDeltas(cleanZikrMap($day['zikr']), ['subhanallah' => 33]);
$day = withZikr($day, manualPart($day, cleanZikrMap($day['zikr'])), $zikrA);
$staleFromB = ['salawat' => 100, 'tasbihat' => 0, 'zikr' => []];
$afterSync = withZikr($staleFromB, manualPart($staleFromB, cleanZikrMap($staleFromB['zikr'])), cleanZikrMap($day['zikr']));
check('a stale full sync keeps the other phone\'s counter', 33, $afterSync['tasbihat']);
check('a stale full sync keeps the manual salawat', 100, $afterSync['salawat']);

// Phone B types a new manual salawat value while A's counter added 5 salawat.
$stored = withZikr(['salawat' => 100, 'tasbihat' => 0], ['salawat' => 100, 'tasbihat' => 0], ['salawat' => 5]);
$fromB = ['salawat' => 120, 'tasbihat' => 0, 'zikr' => []];
$merged = withZikr($fromB, manualPart($fromB, cleanZikrMap($fromB['zikr'])), cleanZikrMap($stored['zikr']));
check('a manual edit on one phone adds to the counter from the other', 125, $merged['salawat']);

echo $failures ? "\n$failures failed\n" : "\nall passed\n";
exit($failures ? 1 : 0);
