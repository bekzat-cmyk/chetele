<?php
declare(strict_types=1);

/*
 * Zikr counter rules. Each field has one writer:
 *  - the per-zikr counts (tasks.zikr) change only through applyZikrDeltas,
 *    which the server runs inside a transaction for every batch of taps;
 *  - the day totals tasks.salawat / tasks.tasbihat are the user's manual part
 *    plus those counts, so a full-day sync from any phone keeps the counter.
 * Pure functions: no database, no globals. Tested by tests/zikr_test.php.
 */

const ZIKR_MAX_COUNT = 99999;
const ZIKR_MAX_KEYS = 60;

function zikrKeyValid($key): bool {
    return is_string($key) && preg_match('/^[a-z0-9_]{1,32}$/', $key) === 1;
}

function zikrInt($value): ?int {
    if (is_int($value)) return $value;
    if (is_string($value) && preg_match('/^-?\d{1,6}$/', $value) === 1) return (int)$value;
    return null;
}

function cleanZikrMap($input): array {
    if (!is_array($input)) return [];
    $map = [];
    foreach ($input as $key => $value) {
        if (count($map) >= ZIKR_MAX_KEYS) break;
        $count = zikrInt($value);
        if (zikrKeyValid($key) && $count !== null && $count > 0) $map[$key] = min($count, ZIKR_MAX_COUNT);
    }
    return $map;
}

function zikrTotals(array $zikr): array {
    $totals = ['salawat' => 0, 'tasbihat' => 0];
    foreach ($zikr as $id => $count) $totals[$id === 'salawat' ? 'salawat' : 'tasbihat'] += $count;
    return $totals;
}

function manualPart(array $tasks, array $zikr): array {
    $counted = zikrTotals($zikr);
    $manual = [];
    foreach (['salawat', 'tasbihat'] as $field) {
        $total = zikrInt($tasks[$field] ?? 0) ?? 0;
        $manual[$field] = max(0, $total - $counted[$field]);
    }
    return $manual;
}

function withZikr(array $tasks, array $manual, array $zikr): array {
    $counted = zikrTotals($zikr);
    $tasks['zikr'] = $zikr;
    $tasks['salawat'] = min(ZIKR_MAX_COUNT, $manual['salawat'] + $counted['salawat']);
    $tasks['tasbihat'] = min(ZIKR_MAX_COUNT, $manual['tasbihat'] + $counted['tasbihat']);
    return $tasks;
}

function applyZikrDeltas(array $zikr, $deltas): array {
    if (!is_array($deltas)) return $zikr;
    $applied = 0;
    foreach ($deltas as $key => $value) {
        if ($applied >= ZIKR_MAX_KEYS) break;
        $delta = zikrInt($value);
        if (!zikrKeyValid($key) || $delta === null) continue;
        $applied++;
        $count = min(ZIKR_MAX_COUNT, max(0, ($zikr[$key] ?? 0) + $delta));
        if ($count > 0) $zikr[$key] = $count; else unset($zikr[$key]);
    }
    return $zikr;
}
