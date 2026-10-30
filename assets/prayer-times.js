/* Prayer times for one day and place, computed on the phone so they work offline.
   Calibrated to the timetable of the Muftiyat of Kyrgyzstan (muftiyat.kg) for 2026:
   Fajr at 18° and Isha at 16° below the horizon, Hanafi Asr (shadow twice the object)
   plus 1.5 minutes, Maghrib 7 minutes after sunset. Across 12 cities and a whole year
   this stays within one minute of their table. Used by index.html and tests/. */
(function (root) {
  const METHOD = { fajr: 18, isha: 16, asrShadow: 2, asrMinutes: 1.5, maghribMinutes: 7 };
  const METHOD_LABEL = 'как в расписании Духовного управления мусульман Кыргызстана (muftiyat.kg)';

  const CITIES = [
    { id: 'bishkek', name: 'Бишкек', lat: 42.8746, lng: 74.5698 },
    { id: 'osh', name: 'Ош', lat: 40.5283, lng: 72.7985 },
    { id: 'jalal-abad', name: 'Джалал-Абад', lat: 40.9333, lng: 73.0017 },
    { id: 'karakol', name: 'Каракол', lat: 42.4907, lng: 78.3936 },
    { id: 'tokmok', name: 'Токмок', lat: 42.8421, lng: 75.3015 },
    { id: 'naryn', name: 'Нарын', lat: 41.4287, lng: 75.9911 },
    { id: 'talas', name: 'Талас', lat: 42.5228, lng: 72.2427 },
    { id: 'batken', name: 'Баткен', lat: 40.0629, lng: 70.8194 },
    { id: 'kara-balta', name: 'Кара-Балта', lat: 42.8146, lng: 73.8481 },
    { id: 'balykchy', name: 'Балыкчы', lat: 42.4600, lng: 76.1870 },
    { id: 'cholpon-ata', name: 'Чолпон-Ата', lat: 42.6493, lng: 77.0821 },
    { id: 'uzgen', name: 'Узген', lat: 40.7699, lng: 73.3007 },
    { id: 'kyzyl-kiya', name: 'Кызыл-Кия', lat: 40.2567, lng: 72.1279 }
  ];

  // Sunrise is listed for the schedule, but it is not a prayer.
  const NAMES = [
    { id: 'fajr', name: 'Фаджр', prayer: true },
    { id: 'sunrise', name: 'Восход', prayer: false },
    { id: 'dhuhr', name: 'Зухр', prayer: true },
    { id: 'asr', name: 'Аср', prayer: true },
    { id: 'maghrib', name: 'Магриб', prayer: true },
    { id: 'isha', name: 'Иша', prayer: true }
  ];

  const rad = d => d * Math.PI / 180;
  const deg = r => r * 180 / Math.PI;
  const fix = (a, b) => ((a % b) + b) % b;

  // Declination and equation of time (hours) for a Julian day.
  function sun(jd) {
    const d = jd - 2451545;
    const g = fix(357.529 + 0.98560028 * d, 360);
    const q = fix(280.459 + 0.98564736 * d, 360);
    const l = fix(q + 1.915 * Math.sin(rad(g)) + 0.020 * Math.sin(rad(2 * g)), 360);
    const e = 23.439 - 0.00000036 * d;
    const ra = fix(deg(Math.atan2(Math.cos(rad(e)) * Math.sin(rad(l)), Math.cos(rad(l)))) / 15, 24);
    return { decl: deg(Math.asin(Math.sin(rad(e)) * Math.sin(rad(l)))), eqt: q / 15 - ra };
  }

  // Hours from solar noon until the sun is `angle` degrees below the horizon.
  function hoursFromNoon(angle, decl, lat) {
    const cos = (-Math.sin(rad(angle)) - Math.sin(rad(decl)) * Math.sin(rad(lat)))
      / (Math.cos(rad(decl)) * Math.cos(rad(lat)));
    return deg(Math.acos(Math.min(1, Math.max(-1, cos)))) / 15;
  }

  function asrHours(shadow, decl, lat) {
    const altitude = deg(Math.atan(1 / (shadow + Math.tan(rad(Math.abs(lat - decl))))));
    return hoursFromNoon(-altitude, decl, lat);
  }

  /* Times for the calendar day of `date` (its year, month and day are read in the
     phone's time zone), as Date objects. */
  function timesFor(date, place, options = {}) {
    const method = { ...METHOD, ...options };
    const y = date.getFullYear(), m = date.getMonth() + 1, day = date.getDate();
    const utcMidnight = Date.UTC(y, m - 1, day);
    const jd = utcMidnight / 86400000 + 2440587.5 + 0.5 - place.lng / 360;
    const { decl, eqt } = sun(jd);
    const noon = 12 - place.lng / 15 - eqt;
    const sunset = noon + hoursFromNoon(0.833, decl, place.lat);
    const hours = {
      fajr: noon - hoursFromNoon(method.fajr, decl, place.lat),
      sunrise: noon - hoursFromNoon(0.833, decl, place.lat),
      dhuhr: noon,
      asr: noon + asrHours(method.asrShadow, decl, place.lat) + method.asrMinutes / 60,
      maghrib: sunset,
      isha: noon + hoursFromNoon(method.isha, decl, place.lat)
    };
    // The Muftiyat drops the seconds of the sun-angle times and rounds noon and Asr.
    const whole = { dhuhr: Math.round, asr: Math.round };
    return NAMES.map(item => {
      const minute = (whole[item.id] || Math.floor)((utcMidnight + hours[item.id] * 3600000) / 60000);
      const extra = item.id === 'maghrib' ? method.maghribMinutes : 0;
      return { ...item, time: new Date((minute + extra) * 60000) };
    });
  }

  // The next prayer after `now`, looking into tomorrow after Isha.
  function nextPrayer(now, place) {
    const today = timesFor(now, place).filter(item => item.prayer);
    const upcoming = today.find(item => item.time > now);
    if (upcoming) return { ...upcoming, tomorrow: false };
    const tomorrow = new Date(now);
    tomorrow.setDate(now.getDate() + 1);
    return { ...timesFor(tomorrow, place).find(item => item.prayer), tomorrow: true };
  }

  const api = { CITIES, METHOD, METHOD_LABEL, NAMES, timesFor, nextPrayer };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  else root.ChetPrayer = api;
})(typeof window !== 'undefined' ? window : globalThis);
