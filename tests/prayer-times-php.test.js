/* The server (api/prayer-times.php) must send a prayer notification at the minute the app
   shows, so both computations are compared for every city and every day of a year. */
const test = require('node:test');
const assert = require('node:assert');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const P = require('../assets/prayer-times.js');

test('server prayer times match the app for every city and day of 2026', () => {
  const days = [];
  for (let d = new Date(2026, 0, 1, 12); d.getFullYear() === 2026; d.setDate(d.getDate() + 1)) days.push(new Date(d));
  const key = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const script = `require ${JSON.stringify(path.join(__dirname, '../api/prayer-times.php'))};
    $cities = json_decode($argv[1], true); $days = json_decode($argv[2], true); $out = [];
    foreach ($cities as $c) foreach ($days as $day) $out[$c['id'] . '|' . $day] = prayerTimesFor($day, (float)$c['lat'], (float)$c['lng']);
    echo json_encode($out);`;
  let server;
  try {
    server = JSON.parse(execFileSync('php', ['-r', script, JSON.stringify(P.CITIES), JSON.stringify(days.map(key))], { maxBuffer: 64 * 1024 * 1024 }).toString());
  } catch (error) {
    if (error.code === 'ENOENT') return; // no PHP on this machine
    throw error;
  }
  let compared = 0;
  for (const city of P.CITIES) for (const day of days) {
    const app = P.timesFor(day, city), php = server[`${city.id}|${key(day)}`];
    for (const item of app) {
      assert.strictEqual(php[item.id] * 1000, item.time.getTime(), `${city.name} ${key(day)} ${item.id}`);
      compared++;
    }
  }
  assert.ok(compared > 25000);
});

test('a prayer notification is due at its time, or the chosen minutes before it', () => {
  const bishkek = P.CITIES[0];
  const day = new Date(2026, 10, 14, 12);
  const times = Object.fromEntries(P.timesFor(day, bishkek).map(item => [item.id, item.time.getTime() / 1000]));
  const run = cases => {
    const script = `require ${JSON.stringify(path.join(__dirname, '../api/prayer-times.php'))};
      $out = []; foreach (json_decode($argv[1], true) as $c) $out[] = prayerDue($c[0], 'Asia/Bishkek', (int)$c[1]);
      echo json_encode($out);`;
    try { return JSON.parse(execFileSync('php', ['-r', script, JSON.stringify(cases)]).toString()); }
    catch (error) { if (error.code === 'ENOENT') return null; throw error; }
  };
  const on = { enabled: true, before: 0, prayers: {}, lat: bishkek.lat, lng: bishkek.lng };
  const result = run([
    [on, times.asr],                                   // right at Asr
    [on, times.asr + 4 * 60],                          // still within the window
    [on, times.asr + 6 * 60],                          // too late: nothing
    [{ ...on, before: 10 }, times.asr - 10 * 60],      // ten minutes ahead
    [{ ...on, before: 10 }, times.asr],                // the moment itself is past the window then
    [{ ...on, prayers: { asr: false } }, times.asr],   // Asr switched off
    [{ ...on, enabled: false }, times.asr],            // notifications off
    [on, times.sunrise]                                // sunrise is not a prayer
  ]);
  if (!result) return;
  assert.deepStrictEqual(result.map(r => r && r.id), ['asr', 'asr', null, 'asr', null, null, null, null]);
  assert.strictEqual(result[0].marker, '2026-11-14:asr');
});
