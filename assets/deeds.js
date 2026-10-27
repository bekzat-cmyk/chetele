/* Deed counting rules for one day: the single place the app decides how many
   of the eleven daily deeds are done. Used by index.html and by tests/. */
(function (root) {
  const NUMBER_DEEDS = ['quran', 'jauchan', 'kitap', 'salawat', 'tasbihat'];
  const NAFIL_IDS = ['asr', 'kuptan', 'avvabin', 'duha'];
  // Number deeds, tahajjud, each nafil prayer, and qaza as one deed.
  const DAILY_MAX = NUMBER_DEEDS.length + 1 + NAFIL_IDS.length + 1;

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
      + nafilDone(day.nafil)
      + (Object.values(madeUp).some(isPositive) ? 1 : 0);
  }

  const api = { countDeeds, DAILY_MAX, NAFIL_IDS };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.ChetDeeds = api;
})(typeof window !== 'undefined' ? window : globalThis);
