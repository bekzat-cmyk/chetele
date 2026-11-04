const test = require('node:test');
const assert = require('node:assert/strict');
const { dayKind, nextSunnah, hint } = require('../assets/fasting.js');

const day = (y, m, d) => new Date(y, m - 1, d, 12);

test('Mondays and Thursdays are Sunnah days', () => {
  assert.equal(dayKind(day(2026, 11, 2)), 'monday');
  assert.equal(dayKind(day(2026, 11, 5)), 'thursday');
});

test('the next Sunnah day after a Wednesday is the Thursday', () => {
  const next = nextSunnah(day(2026, 11, 4));
  assert.equal(next.date.getDate(), 5);
});

test('the white days are found by the Hijri calendar', () => {
  // 13 Jumada al-Akhirah 1448 in Umm al-Qura is 23 November 2026.
  assert.equal(dayKind(day(2026, 11, 23)), 'white');
});

test('Ramadan and Eid are not shown as voluntary fasts', () => {
  assert.equal(dayKind(day(2026, 2, 25)), 'ramadan');
  assert.equal(hint(day(2026, 3, 20)).forbidden, true);
});

test('a plain day points to the next Sunnah day', () => {
  const line = hint(day(2026, 11, 4));
  assert.equal(line.today, false);
  assert.match(line.next, /четверг/);
});
