// Run: node --test tests/
const test = require('node:test');
const assert = require('node:assert/strict');
const { addPending, adoptServer, trimToTotal, matchSalawat } = require('../assets/zikr-sync.js');

test('taps on one day add up per zikr', () => {
  let pending = {};
  pending = addPending(pending, '2026-10-28', 'subhanallah', 1);
  pending = addPending(pending, '2026-10-28', 'subhanallah', 1);
  pending = addPending(pending, '2026-10-28', 'salawat', 3);
  assert.deepEqual(pending, { '2026-10-28': { subhanallah: 2, salawat: 3 } });
});

test('a tap and its undo cancel out and leave nothing to send', () => {
  let pending = addPending({}, '2026-10-28', 'subhanallah', 1);
  pending = addPending(pending, '2026-10-28', 'subhanallah', -1);
  assert.deepEqual(pending, {});
});

test('adding does not change the previous pending object', () => {
  const before = { '2026-10-28': { subhanallah: 1 } };
  addPending(before, '2026-10-28', 'subhanallah', 1);
  assert.deepEqual(before, { '2026-10-28': { subhanallah: 1 } });
});

test('counts from the server replace the local ones when nothing is waiting', () => {
  const local = { salawat: '105', tasbihat: '66', zikr: { salawat: 5, subhanallah: 66 } };
  assert.deepEqual(adoptServer(local, { salawat: 5, subhanallah: 66 }, {}), { salawat: 105, tasbihat: 66, zikr: { salawat: 5, subhanallah: 66 } });
});

test('counts added on another phone show up and keep the local manual part', () => {
  const local = { salawat: '105', tasbihat: '0', zikr: { salawat: 5 } };
  assert.deepEqual(adoptServer(local, { salawat: 8, subhanallah: 33 }, {}), { salawat: 108, tasbihat: 33, zikr: { salawat: 8, subhanallah: 33 } });
});

test('taps made while the request was in flight stay on top of the server counts', () => {
  const local = { salawat: '0', tasbihat: '68', zikr: { subhanallah: 68 } };
  assert.deepEqual(adoptServer(local, { subhanallah: 66 }, { subhanallah: 2 }), { salawat: 0, tasbihat: 68, zikr: { subhanallah: 68 } });
});

test('a manual edit not yet synced is not overwritten by the server answer', () => {
  const local = { salawat: '120', tasbihat: '', zikr: { salawat: 5 } };
  assert.deepEqual(adoptServer(local, { salawat: 5 }, {}), { salawat: 120, tasbihat: 0, zikr: { salawat: 5 } });
});

test('a lower typed total trims the counted zikrs, largest first', () => {
  const { zikr, deltas } = trimToTotal({ subhanallah: 20, alhamdulillah: 13, salawat: 5 }, 'tasbihat', 10);
  assert.deepEqual(zikr, { alhamdulillah: 10, salawat: 5 });
  assert.deepEqual(deltas, { subhanallah: -20, alhamdulillah: -3 });
});

test('a total at or above the counted zikrs changes nothing', () => {
  const { zikr, deltas } = trimToTotal({ salawat: 5, subhanallah: 3 }, 'salawat', 8);
  assert.deepEqual(zikr, { salawat: 5, subhanallah: 3 });
  assert.deepEqual(deltas, {});
});

test('salawat on the main screen becomes the salawat counter, up or down', () => {
  assert.deepEqual(matchSalawat({ salawat: 3, subhanallah: 2 }, '36'), { zikr: { salawat: 36, subhanallah: 2 }, delta: 33 });
  assert.deepEqual(matchSalawat({ salawat: 36 }, '10'), { zikr: { salawat: 10 }, delta: -26 });
  assert.deepEqual(matchSalawat({ salawat: 5 }, ''), { zikr: {}, delta: -5 });
});
