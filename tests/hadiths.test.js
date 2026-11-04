const test = require('node:test');
const assert = require('node:assert');
const { HADITHS, hadithForDate } = require('../assets/hadiths.js');

test('every hadith has a text, a source, a meaning and a practice', () => {
  for (const hadith of HADITHS) {
    for (const field of ['text', 'source', 'explanation', 'practice']) assert.ok(String(hadith[field] || '').trim(), `${hadith.text} lacks ${field}`);
    assert.match(hadith.source, /№\d+$/);
  }
});

test('no hadith text appears twice', () => {
  const texts = HADITHS.map(hadith => hadith.text);
  assert.strictEqual(new Set(texts).size, texts.length);
});

test('each day of a full cycle shows a different hadith', () => {
  const seen = new Set();
  for (let i = 0; i < HADITHS.length; i++) seen.add(hadithForDate(new Date(2026, 10, 4 + i)).text);
  assert.strictEqual(seen.size, HADITHS.length);
});

test('the new year continues the list instead of starting again', () => {
  const last = HADITHS.indexOf(hadithForDate(new Date(2026, 11, 31)));
  const first = HADITHS.indexOf(hadithForDate(new Date(2027, 0, 1)));
  assert.strictEqual(first, (last + 1) % HADITHS.length);
});
