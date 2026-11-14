<?php
declare(strict_types=1);

/* Prayer times on the server, for prayer notifications. The same method and rounding as
   assets/prayer-times.js (the Muftiyat of Kyrgyzstan's timetable), so a notification comes
   at the minute the app shows. tests/prayer-times-php.test.js checks the two agree. */

const PRAYER_METHOD = ['fajr' => 18.0, 'isha' => 16.0, 'asrShadow' => 2.0, 'asrMinutes' => 1.5, 'maghribMinutes' => 7];
const PRAYER_IDS = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
const PRAYER_NAMES = ['fajr' => 'Фаджр', 'sunrise' => 'Восход', 'dhuhr' => 'Зухр', 'asr' => 'Аср', 'maghrib' => 'Магриб', 'isha' => 'Иша'];

function prayerFix(float $a, float $b): float { return fmod(fmod($a, $b) + $b, $b); }

/* Declination and equation of time (hours) for a Julian day. */
function prayerSun(float $jd): array {
    $d = $jd - 2451545;
    $g = prayerFix(357.529 + 0.98560028 * $d, 360);
    $q = prayerFix(280.459 + 0.98564736 * $d, 360);
    $l = prayerFix($q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(deg2rad(2 * $g)), 360);
    $e = 23.439 - 0.00000036 * $d;
    $ra = prayerFix(rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($l)), cos(deg2rad($l)))) / 15, 24);
    return ['decl' => rad2deg(asin(sin(deg2rad($e)) * sin(deg2rad($l)))), 'eqt' => $q / 15 - $ra];
}

/* Hours from solar noon until the sun is `angle` degrees below the horizon. */
function prayerHoursFromNoon(float $angle, float $decl, float $lat): float {
    $cos = (-sin(deg2rad($angle)) - sin(deg2rad($decl)) * sin(deg2rad($lat))) / (cos(deg2rad($decl)) * cos(deg2rad($lat)));
    return rad2deg(acos(min(1.0, max(-1.0, $cos)))) / 15;
}

function prayerAsrHours(float $shadow, float $decl, float $lat): float {
    $altitude = rad2deg(atan(1 / ($shadow + tan(deg2rad(abs($lat - $decl))))));
    return prayerHoursFromNoon(-$altitude, $decl, $lat);
}

/* Times for the calendar day $date (Y-m-d) at a place, as Unix timestamps (seconds, whole
   minutes), keyed fajr, sunrise, dhuhr, asr, maghrib, isha. */
function prayerTimesFor(string $date, float $lat, float $lng): array {
    [$y, $m, $d] = array_map('intval', explode('-', $date));
    $utcMidnight = gmmktime(0, 0, 0, $m, $d, $y);
    $jd = $utcMidnight / 86400 + 2440587.5 + 0.5 - $lng / 360;
    $sun = prayerSun($jd);
    $noon = 12 - $lng / 15 - $sun['eqt'];
    $hours = [
        'fajr' => $noon - prayerHoursFromNoon(PRAYER_METHOD['fajr'], $sun['decl'], $lat),
        'sunrise' => $noon - prayerHoursFromNoon(0.833, $sun['decl'], $lat),
        'dhuhr' => $noon,
        'asr' => $noon + prayerAsrHours(PRAYER_METHOD['asrShadow'], $sun['decl'], $lat) + PRAYER_METHOD['asrMinutes'] / 60,
        'maghrib' => $noon + prayerHoursFromNoon(0.833, $sun['decl'], $lat),
        'isha' => $noon + prayerHoursFromNoon(PRAYER_METHOD['isha'], $sun['decl'], $lat),
    ];
    $times = [];
    foreach ($hours as $id => $value) {
        // The Muftiyat drops the seconds of the sun-angle times and rounds noon and Asr.
        $minutes = ($utcMidnight * 1000 + $value * 3600000) / 60000;
        $minute = in_array($id, ['dhuhr', 'asr'], true) ? round($minutes) : floor($minutes);
        $times[$id] = (int)(($minute + ($id === 'maghrib' ? PRAYER_METHOD['maghribMinutes'] : 0)) * 60);
    }
    return $times;
}

/* Which prayer notification is due at $now (Unix seconds) for these settings, or null: the
   prayer's moment (its time, or `before` minutes ahead) has come within the last $window
   minutes. Tomorrow's Fajr counts too, for a reminder set ahead of a Fajr after midnight. */
function prayerDue($prayer, string $timezone, int $now, int $window = 5): ?array {
    if (!is_array($prayer) || ($prayer['enabled'] ?? false) !== true) return null;
    $lat = (float)($prayer['lat'] ?? 42.8746); $lng = (float)($prayer['lng'] ?? 74.5698);
    $before = in_array($prayer['before'] ?? 0, [0, 5, 10, 15], true) ? (int)$prayer['before'] : 0;
    $local = (new DateTimeImmutable('@' . $now))->setTimezone(new DateTimeZone($timezone));
    foreach ([$local->modify('-1 day'), $local, $local->modify('+1 day')] as $date) {
        $day = $date->format('Y-m-d');
        $times = prayerTimesFor($day, $lat, $lng);
        foreach (PRAYER_IDS as $id) {
            if (($prayer['prayers'][$id] ?? true) === false) continue;
            $due = $times[$id] - $before * 60;
            if ($now >= $due && $now < $due + $window * 60) return ['id' => $id, 'day' => $day, 'time' => $times[$id], 'before' => $before, 'marker' => $day . ':' . $id];
        }
    }
    return null;
}
