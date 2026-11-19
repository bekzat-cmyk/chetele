/* Deed counting rules for one day: the single place the app decides how many
   of the eleven daily deeds are done. Used by index.html and by tests/. */
(function (root) {
  const NUMBER_DEEDS = ['quran', 'jauchan', 'kitap', 'salawat', 'tasbihat'];
  const NAFIL_IDS = ['asr', 'kuptan', 'avvabin', 'duha'];
  // Number deeds, tahajjud, the voluntary fast, each nafil prayer, and qaza as one deed.
  const DAILY_MAX = NUMBER_DEEDS.length + 1 + 1 + NAFIL_IDS.length + 1;
  // The fast became a deed on this day; earlier days were complete with one deed less.
  const FAST_FROM = '2026-11-04';
  function maxFor(dateKey) { return dateKey && dateKey < FAST_FROM ? DAILY_MAX - 1 : DAILY_MAX; }

  function isPositive(value) {
    const number = Number(value);
    return Number.isFinite(number) && number > 0;
  }

  // Old entries stored nafil as a single true flag meaning all four prayers.
  function nafilDone(nafil) {
    if (nafil && typeof nafil === 'object') return NAFIL_IDS.filter(id => nafil[id]).length;
    return nafil === true ? NAFIL_IDS.length : 0;
  }

  function countDeeds(tasks, qaza) {
    const day = tasks && typeof tasks === 'object' ? tasks : {};
    const madeUp = qaza && typeof qaza === 'object' ? qaza : {};
    return NUMBER_DEEDS.filter(id => isPositive(day[id])).length
      + (day.tahajjud === 'yes' ? 1 : 0)
      + (day.fast === 'yes' ? 1 : 0)
      + nafilDone(day.nafil)
      + (Object.values(madeUp).some(isPositive) ? 1 : 0);
  }

  // Rating points: 10 for each deed, the night prayer 50 and the voluntary fast 100.
  const TAHAJJUD_POINTS = 50, FAST_POINTS = 100;
  function pointsFor(tasks, qaza) {
    const day = tasks && typeof tasks === 'object' ? tasks : {};
    return countDeeds(day, qaza) * 10
      + (day.tahajjud === 'yes' ? TAHAJJUD_POINTS - 10 : 0)
      + (day.fast === 'yes' ? FAST_POINTS - 10 : 0);
  }
  function maxPoints(dateKey) {
    return maxFor(dateKey) * 10 + TAHAJJUD_POINTS - 10 + (dateKey && dateKey < FAST_FROM ? 0 : FAST_POINTS - 10);
  }

  const api = { countDeeds, pointsFor, maxPoints, DAILY_MAX, NAFIL_IDS, FAST_FROM, TAHAJJUD_POINTS, FAST_POINTS, maxFor };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.ChetDeeds = api;
})(typeof window !== 'undefined' ? window : globalThis);
