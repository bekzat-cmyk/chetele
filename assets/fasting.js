/* Voluntary fasting: which days the Sunnah encourages, by the Umm al-Qura Hijri
   calendar the phone knows. Mondays and Thursdays, the white days (13, 14, 15),
   Arafah (9 Dhul-Hijjah), Tasu'a and Ashura (9, 10 Muharram). Ramadan is
   obligatory and the two Eids with the days of Tashriq are not fasted. */
(function (root) {
  let hijriFormat = null;
  try { hijriFormat = new Intl.DateTimeFormat('en-u-ca-islamic-umalqura', { day: 'numeric', month: 'numeric' }); } catch { hijriFormat = null; }

  function hijri(date) {
    if (!hijriFormat) return null;
    try {
      const parts = Object.fromEntries(hijriFormat.formatToParts(date).map(part => [part.type, part.value]));
      const day = Number(parts.day), month = Number(parts.month);
      return Number.isFinite(day) && Number.isFinite(month) ? { day, month } : null;
    } catch { return null; }
  }

  /* The kind of fasting day `date` is, or '' for an ordinary day. */
  function dayKind(date) {
    const h = hijri(date);
    if (h) {
      if ((h.month === 10 && h.day === 1) || (h.month === 12 && h.day >= 10 && h.day <= 13)) return 'eid';
      if (h.month === 9) return 'ramadan';
      if (h.month === 12 && h.day === 9) return 'arafah';
      if (h.month === 1 && (h.day === 9 || h.day === 10)) return 'ashura';
      if (h.day >= 13 && h.day <= 15) return 'white';
    }
    const weekday = date.getDay();
    if (weekday === 1) return 'monday';
    if (weekday === 4) return 'thursday';
    return '';
  }

  const SUNNAH = ['monday', 'thursday', 'white', 'arafah', 'ashura'];
  const LABEL = { monday: 'понедельник', thursday: 'четверг', white: 'белый день', arafah: 'день Арафа', ashura: 'Ашура' };

  /* The next day from tomorrow on that the Sunnah encourages fasting. */
  function nextSunnah(from) {
    for (let i = 1; i <= 30; i++) {
      const date = new Date(from.getFullYear(), from.getMonth(), from.getDate() + i, 12);
      const kind = dayKind(date);
      if (SUNNAH.includes(kind)) return { date, kind };
    }
    return null;
  }

  /* One line for the fasting card: today's kind, or the next Sunnah day. */
  function hint(date, locale = 'ru-RU') {
    const kind = dayKind(date);
    if (kind === 'ramadan') return { text: 'Идёт Рамадан — пост обязателен.', today: true };
    if (kind === 'eid') return { text: 'Сегодня праздник — в этот день не постятся.', today: true, forbidden: true };
    if (SUNNAH.includes(kind)) return { text: `Сегодня сунна поститься: ${LABEL[kind]}.`, today: true };
    const next = nextSunnah(date);
    const when = next ? new Intl.DateTimeFormat(locale, { weekday: 'long', day: 'numeric', month: 'long' }).format(next.date) : '';
    const extra = next && !['monday', 'thursday'].includes(next.kind) ? ` · ${LABEL[next.kind]}` : '';
    return { text: 'Сунна — поститься в понедельник, четверг и 13–15 числа лунного месяца.', next: when ? when + extra : '', today: false };
  }

  const api = { hijri, dayKind, nextSunnah, hint };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.ChetFasting = api;
})(typeof window !== 'undefined' ? window : globalThis);
