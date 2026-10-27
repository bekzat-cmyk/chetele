// Run: node --test tests/
const test = require('node:test');
const assert = require('node:assert/strict');
const { countDeeds, DAILY_MAX } = require('../assets/deeds.js');

const fullDay = {
  tasks: { quran: '5', jauchan: '2', kitap: '1', salawat: '100', tasbihat: '33', tahajjud: 'yes', nafil: { asr: true, kuptan: true, avvabin: true, duha: true } },
  qaza: { 'Аср': '1' }
};

test('a day has eleven possible deeds', () => {
  assert.equal(DAILY_MAX, 11);
});

test('an empty day counts nothing', () => {
  assert.equal(countDeeds({}, {}), 0);
  assert.equal(countDeeds(undefined, undefined), 0);
});

test('a fully marked day counts every deed', () => {
  assert.equal(countDeeds(fullDay.tasks, fullDay.qaza), 11);
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
