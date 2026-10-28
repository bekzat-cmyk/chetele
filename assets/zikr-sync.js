/* Zikr counter sync on the phone. Taps are kept as pending deltas per day and
   sent to the server, which is the only writer of the per-zikr counts.
   Used by index.html and by tests/. */
(function (root) {
  // Returns a new pending object with delta added; zero entries are dropped.
  function addPending(pending, date, id, delta) {
    const day = { ...((pending || {})[date] || {}) };
    const next = (day[id] || 0) + delta;
    if (next) day[id] = next; else delete day[id];
    const result = { ...(pending || {}) };
    if (Object.keys(day).length) result[date] = day; else delete result[date];
    return result;
  }

  function sums(zikr) {
    return Object.entries(zikr).reduce((total, [id, count]) => {
      total[id === 'salawat' ? 'salawat' : 'tasbihat'] += Number(count) || 0;
      return total;
    }, { salawat: 0, tasbihat: 0 });
  }

  // Only the per-zikr counts come from the server. The day totals are rebuilt as
  // the local manual part plus those counts, so a manual edit that has not been
  // synced yet is never overwritten. Taps still waiting are added on top.
  function adoptServer(local, serverZikr, waiting) {
    const day = local || {}, localZikr = day.zikr && typeof day.zikr === 'object' ? day.zikr : {};
    const localSums = sums(localZikr);
    const manual = {
      salawat: Math.max(0, (Number(day.salawat) || 0) - localSums.salawat),
      tasbihat: Math.max(0, (Number(day.tasbihat) || 0) - localSums.tasbihat)
    };
    const zikr = { ...(serverZikr && typeof serverZikr === 'object' ? serverZikr : {}) };
    Object.entries(waiting || {}).forEach(([id, delta]) => {
      const count = Math.max(0, (zikr[id] || 0) + delta);
      if (count) zikr[id] = count; else delete zikr[id];
    });
    const counted = sums(zikr);
    return { salawat: manual.salawat + counted.salawat, tasbihat: manual.tasbihat + counted.tasbihat, zikr };
  }

  const api = { addPending, adoptServer };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.ChetZikrSync = api;
})(typeof window !== 'undefined' ? window : globalThis);
