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

  /* A day total typed lower than what the counter already counted for that field
     (salawat or tasbihat) takes the difference off the counted zikrs, largest first.
     Returns the trimmed counts and the negative deltas to send to the server. */
  function trimToTotal(zikr, field, total) {
    const counts = { ...(zikr && typeof zikr === 'object' ? zikr : {}) };
    const ids = Object.keys(counts).filter((id) => (id === 'salawat') === (field === 'salawat'))
      .sort((a, b) => (Number(counts[b]) || 0) - (Number(counts[a]) || 0));
    let extra = ids.reduce((sum, id) => sum + (Number(counts[id]) || 0), 0) - Math.max(0, Number(total) || 0);
    const deltas = {};
    ids.forEach((id) => {
      if (extra <= 0) return;
      const take = Math.min(extra, Number(counts[id]) || 0);
      extra -= take;
      const left = (Number(counts[id]) || 0) - take;
      if (left) counts[id] = left; else delete counts[id];
      if (take) deltas[id] = -take;
    });
    return { zikr: counts, deltas };
  }

  /* Salawat is one zikr, so the day's salawat and its counter are the same number.
     Returns the counts with salawat set to the day total and the delta for the server. */
  function matchSalawat(zikr, total) {
    const counts = { ...(zikr && typeof zikr === 'object' ? zikr : {}) };
    const want = Math.max(0, Number(total) || 0), delta = want - (Number(counts.salawat) || 0);
    if (want) counts.salawat = want; else delete counts.salawat;
    return { zikr: counts, delta };
  }

  const api = { addPending, adoptServer, trimToTotal, matchSalawat };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.ChetZikrSync = api;
})(typeof window !== 'undefined' ? window : globalThis);
