// Run: TZ=Asia/Bishkek node --test tests/
const test = require('node:test');
const assert = require('node:assert/strict');
const { CITIES, timesFor, nextPrayer } = require('../assets/prayer-times.js');

const bishkek = CITIES.find(city => city.id === 'bishkek');
const minutes = date => date.getUTCHours() * 60 + date.getUTCMinutes();
// Bishkek is UTC+6 all year, so the expected local times are written as UTC+6.
const local = (h, m) => ((h - 6) * 60 + m + 1440) % 1440;
const byId = (list, id) => list.find(item => item.id === id).time;

test('Bishkek on 2 September 2026 matches a published timetable within five minutes', () => {
  // ihadis.com (MWL angles): Fajr 04:46, Zuhr 13:02, Maghrib 19:35, Isha 21:15.
  const day = timesFor(new Date(Date.UTC(2026, 8, 2, 6)), bishkek);
  const near = (actual, h, m) => assert.ok(Math.abs(minutes(actual) - local(h, m)) <= 5, `${actual.toISOString()} vs ${h}:${m}`);
  near(byId(day, 'fajr'), 4, 46);
  near(byId(day, 'dhuhr'), 13, 2);
  near(byId(day, 'maghrib'), 19, 35);
  near(byId(day, 'isha'), 21, 15);
});

test('the day lists six times in order, sunrise is not a prayer', () => {
  const day = timesFor(new Date(Date.UTC(2026, 9, 21, 6)), bishkek);
  assert.deepEqual(day.map(item => item.name), ['Фаджр', 'Восход', 'Зухр', 'Аср', 'Магриб', 'Иша']);
  assert.equal(day.find(item => item.id === 'sunrise').prayer, false);
  for (let i = 1; i < day.length; i++) assert.ok(day[i].time > day[i - 1].time);
});

test('the Hanafi asr comes later than the Shafi one', () => {
  const date = new Date(Date.UTC(2026, 8, 2, 6));
  const hanafi = byId(timesFor(date, bishkek), 'asr');
  const shafi = byId(timesFor(date, bishkek, { fajr: 18, isha: 17, asrShadow: 1 }), 'asr');
  assert.ok(hanafi > shafi);
});

test('after Isha the next prayer is tomorrow\'s Fajr', () => {
  const lateEvening = new Date(Date.UTC(2026, 9, 21, 17, 30)); // 23:30 in Bishkek
  const next = nextPrayer(lateEvening, bishkek);
  assert.equal(next.id, 'fajr');
  assert.equal(next.tomorrow, true);
  assert.ok(next.time > lateEvening);
});

test('in the afternoon the next prayer is still today', () => {
  const afternoon = new Date(Date.UTC(2026, 9, 21, 8, 0)); // 14:00 in Bishkek
  const next = nextPrayer(afternoon, bishkek);
  assert.equal(next.id, 'asr');
  assert.equal(next.tomorrow, false);
});
