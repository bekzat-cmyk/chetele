// Run: TZ=Asia/Bishkek node --test tests/
const test = require('node:test');
const assert = require('node:assert/strict');
const { CITIES, timesFor, nextPrayer } = require('../assets/prayer-times.js');

const bishkek = CITIES.find(city => city.id === 'bishkek');
const minutes = date => date.getUTCHours() * 60 + date.getUTCMinutes();
// Kyrgyzstan is UTC+6 all year, so the expected local times are written as UTC+6.
const local = (h, m) => ((h - 6) * 60 + m + 1440) % 1440;
const byId = (list, id) => list.find(item => item.id === id).time;

// Rows of the Muftiyat of Kyrgyzstan timetable (muftiyat.kg) for 2026:
// date, then Fajr, Sunrise, Zuhr, Asr, Maghrib, Isha.
const MUFTIYAT = {
  bishkek: [[2026, 1, 1, '06:50 08:32 13:05 15:57 17:44 19:08'], [2026, 6, 21, '03:05 05:23 13:03 18:23 20:50 22:40'], [2026, 10, 30, '05:57 07:32 12:45 16:16 18:04 19:22']],
  osh: [[2026, 1, 1, '06:54 08:32 13:12 16:13 17:59 19:19'], [2026, 6, 21, '03:32 05:38 13:11 18:26 20:49 22:30'], [2026, 10, 30, '06:04 07:36 12:52 16:29 18:15 19:30']],
  karakol: [[2026, 1, 1, '06:34 08:16 12:50 15:43 17:30 18:53'], [2026, 6, 21, '02:53 05:09 12:48 18:07 20:34 22:22'], [2026, 10, 30, '05:42 07:17 12:30 16:02 17:50 19:07']]
};

test('the times stay within a minute of the Muftiyat timetable', () => {
  for (const [id, rows] of Object.entries(MUFTIYAT)) {
    const city = CITIES.find(item => item.id === id);
    for (const [y, m, d, line] of rows) {
      const day = timesFor(new Date(Date.UTC(y, m - 1, d, 6)), city);
      line.split(' ').forEach((expected, index) => {
        const [h, min] = expected.split(':').map(Number);
        const diff = Math.abs(minutes(day[index].time) - local(h, min));
        assert.ok(Math.min(diff, 1440 - diff) <= 1, `${id} ${y}-${m}-${d} ${day[index].id}: ${day[index].time.toISOString()} vs ${expected}`);
      });
    }
  }
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
  const shafi = byId(timesFor(date, bishkek, { asrShadow: 1 }), 'asr');
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
