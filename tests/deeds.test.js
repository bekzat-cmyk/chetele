// Run: node --test tests/
const test = require('node:test');
const assert = require('node:assert/strict');
const { countDeeds, DAILY_MAX, maxFor } = require('../assets/deeds.js');

const fullDay = {
  tasks: { quran: '5', jauchan: '2', kitap: '1', salawat: '100', tasbihat: '33', tahajjud: 'yes', fast: 'yes', nafil: { asr: true, kuptan: true, avvabin: true, duha: true } },
  qaza: { 'Аср': '1' }
};

test('a day has twelve possible deeds', () => {
  assert.equal(DAILY_MAX, 12);
});

test('days before the fast became a deed were complete with eleven', () => {
  assert.equal(maxFor('2026-11-03'), 11);
  assert.equal(maxFor('2026-11-04'), 12);
});

test('the voluntary fast counts only when answered yes', () => {
  assert.equal(countDeeds({ fast: 'no' }, {}), 0);
  assert.equal(countDeeds({ fast: 'yes' }, {}), 1);
});

test('an empty day counts nothing', () => {
  assert.equal(countDeeds({}, {}), 0);
  assert.equal(countDeeds(undefined, undefined), 0);
});

test('a fully marked day counts every deed', () => {
  assert.equal(countDeeds(fullDay.tasks, fullDay.qaza), 12);
});

test('number deeds count only when above zero', () => {
  assert.equal(countDeeds({ quran: '0', jauchan: '', kitap: '-3', salawat: 'abc', tasbihat: 7 }, {}), 1);
});

test('tahajjud counts only when answered yes', () => {
  assert.equal(countDeeds({ tahajjud: 'no' }, {}), 0);
  assert.equal(countDeeds({ tahajjud: 'yes' }, {}), 1);
});

test('each nafil prayer counts on its own', () => {
  assert.equal(countDeeds({ nafil: { asr: true, duha: true, kuptan: false } }, {}), 2);
});

test('a legacy nafil flag of true means all four prayers', () => {
  assert.equal(countDeeds({ nafil: true }, {}), 4);
});

test('qaza counts as one deed however many prayers were made up', () => {
  assert.equal(countDeeds({}, { 'Багымдат': '2', 'Аср': '3' }), 1);
  assert.equal(countDeeds({}, { 'Багымдат': '0', 'Аср': '' }), 0);
});

test('zikr breakdown does not add deeds beyond salawat and tasbihat', () => {
  assert.equal(countDeeds({ tasbihat: '33', zikr: { subhanallah: 33 } }, {}), 1);
});

const deeds = require('../assets/deeds.js');

test('points: 10 per deed, tahajjud 50, the fast 100', () => {
  assert.equal(deeds.pointsFor({ quran: '3', salawat: '100' }, {}), 20);
  assert.equal(deeds.pointsFor({ tahajjud: 'yes' }, {}), 50);
  assert.equal(deeds.pointsFor({ fast: 'yes', tahajjud: 'yes', quran: '1' }, {}), 160);
  assert.equal(deeds.pointsFor({}, { Аср: '1' }), 10);
});

test('a full day is worth 250 points, 150 before the fast became a deed', () => {
  assert.equal(deeds.maxPoints('2026-11-19'), 250);
  assert.equal(deeds.maxPoints('2026-11-01'), 150);
});
