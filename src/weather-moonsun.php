<?php

$latitude = $weather_station_latitude;
$longitude = $weather_station_longitude;
$zenith = $weather_station_zenith;

$calculate_days = 7;

include('lib_moon.php');
include('lib_moonphase.php');
/*
phase(): the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
illumination(): the illuminated fraction of the Moon (0 = New, 1 = Full).
age(): the age of the Moon, in days.
distance(): the distance of the Moon from the centre of the Earth (kilometres).
diameter(): the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
sundistance(): the distance to the Sun (kilometres).
sundiameter(): the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
new_moon(): the time of the last New Moon (UNIX timestamp).
next_new_moon(): the time of the next New Moon (UNIX timestamp).
full_moon(): the time of the Full Moon in the current lunar cycle (UNIX timestamp).
next_full_moon(): the time of the next Full Moon in the current lunar cycle (UNIX timestamp).
first_quarter(): the time of the first quarter in the current lunar cycle (UNIX timestamp).
next_first_quarter(): the time of the next first quarter in the current lunar cycle (UNIX timestamp).
last_quarter(): the time of the last quarter in the current lunar cycle (UNIX timestamp).
next_last_quarter(): the time of the next last quarter in the current lunar cycle (UNIX timestamp).
phase_name(): the phase name.
*/

$moonrise = array();
$moonset = array();
$sunrise = array();
$sunset = array();
$moonphase = array();
$moonphase_text = array();
$moonage = array();
$moonfull = array();
$moondistance = array();
$moonilluminated = array();

//$c_now = time();
$c_now = mktime(0,0,0,date('m'),date('d'),date('Y'));
$c_now_moonphase = mktime(12,0,0,date('m'),date('d'),date('Y'));

//$moon_basetime = mktime(10,29,59,12,11,2015);
$moon_basetime = mktime(0,56,36,7,20,2016);
$moon_days=29.530588861;

for ($i=0; $i<=$calculate_days; $i++) {

  $c_year = date('Y', $c_now);
	$c_month = date('m', $c_now);
	$c_day = date('d', $c_now);

	$tmp_var = (Moon::calculateMoonTimes($c_month, $c_day, $c_year, $latitude, $longitude));

	$moonrise[] = $tmp_var->moonrise;
	$moonset[] = $tmp_var->moonset;

	$sunrise[] = date_sunrise($c_now, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $zenith, 0);
	$sunset[] = date_sunset($c_now, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $zenith, 0);

	$moon = new Solaris\MoonPhase($c_now_moonphase);
	$moonphase_tmp = $moon->phase()*100;
	$moonphase[] = $moonphase_tmp;

	if ($moonphase_tmp <= 1 || $moonphase_tmp >= 99 ) $moonphase_text[] = 'Neumond';
	elseif ($moonphase_tmp > 1 && $moonphase_tmp < 49) $moonphase_text[] = 'Zunehmender Mond';
	elseif ($moonphase_tmp >= 49 && $moonphase_tmp <= 51) $moonphase_text[] = 'Vollmond';
	else $moonphase_text[] = 'Abnehmender Mond';

	$moonage_tmp = round(($moon_days /100 * $moonphase_tmp),1);
	if ($moonage_tmp <= 1) {
		$moonage_tmp.= ' Tag';
	} else {
		$moonage_tmp.= ' Tage';
	}
	$moonage[] = $moonage_tmp;

	if (date('%Y%m%d',$c_now)==date('%Y%m%d',$moon->full_moon())) {
		$moonfull[] = '<br />um '.strftime('%H:%M',$moon->full_moon());
	} elseif (date('%Y%m%d',$c_now)==date('%Y%m%d',$moon->new_moon())) {
		$moonfull[] = '<br />um '.strftime('%H:%M',$moon->new_moon());
	} else {
		$moonfull[] = '';
	}

	$moondistance[] = round(($moon->distance()/1000),1).' tkm';
	$moonilluminated[] = round(($moon->illumination()*100),0).' %';

	$c_now = $c_now + (60*60*24);
	$c_now_moonphase = $c_now_moonphase + (60*60*24);
}

function moonphase_icon($moon_percent_tmp) {
//	$moon_icon_set = '-alt';
	$moon_icon_set = '';
	$moon_phase = '';
	$moon_percent = intval($moon_percent_tmp);
	if ($moon_percent >= 0 && $moon_percent <= 1) { // 1 - new moon
		$moon_phase = 'wi-moon'.$moon_icon_set.'-new';
	}
	elseif ($moon_percent >= 2 && $moon_percent <= 5) { // 2
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-crescent-1';
	}
	elseif ($moon_percent >= 6 && $moon_percent <= 9) { // 3
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-crescent-2';
	}
	elseif ($moon_percent >= 10 && $moon_percent <= 12) { // 4
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-crescent-3';
	}
	elseif ($moon_percent >= 13 && $moon_percent <= 15) { // 5
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-crescent-4';
	}
	elseif ($moon_percent >= 16 && $moon_percent <= 19) { // 6
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-crescent-5';
	}
	elseif ($moon_percent >= 20 && $moon_percent <= 23) { // 7
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-crescent-6';
	}
	elseif ($moon_percent >= 24 && $moon_percent <= 26) { // 8 - first quarter
		$moon_phase = 'wi-moon'.$moon_icon_set.'-first-quarter';
	}
	elseif ($moon_percent >= 27 && $moon_percent <= 30) { // 9
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-1';
	}
	elseif ($moon_percent >= 31 && $moon_percent <= 34) { // 10
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-2';
	}
	elseif ($moon_percent >= 35 && $moon_percent <= 37) { // 11
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-3';
	}
	elseif ($moon_percent >= 38 && $moon_percent <= 40) { // 12
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-4';
	}
	elseif ($moon_percent >= 41 && $moon_percent <= 44) { // 13
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-5';
	}
	elseif ($moon_percent >= 45 && $moon_percent <= 48) { // 14
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-6';
	}
	elseif ($moon_percent >= 49 && $moon_percent <= 51) { // 15 - full moon
		$moon_phase = 'wi-moon'.$moon_icon_set.'-full';
	}
	elseif ($moon_percent >= 52 && $moon_percent <= 55) { // 16
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-1';
	}
	elseif ($moon_percent >= 56 && $moon_percent <= 59) { // 17
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-2';
	}
	elseif ($moon_percent >= 60 && $moon_percent <= 62) { // 18
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-3';
	}
	elseif ($moon_percent >= 63 && $moon_percent <= 65) { // 19
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-4';
	}
	elseif ($moon_percent >= 66 && $moon_percent <= 69) { // 20
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-5';
	}
	elseif ($moon_percent >= 70 && $moon_percent <= 73) { // 21
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-6';
	}
	elseif ($moon_percent >= 74 && $moon_percent <= 76) { // 22 - third quarter
		$moon_phase = 'wi-moon'.$moon_icon_set.'-third-quarter';
	}
	elseif ($moon_percent >= 77 && $moon_percent <= 80) { // 23
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-crescent-1';
	}
	elseif ($moon_percent >= 81 && $moon_percent <= 84) { // 24
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-crescent-2';
	}
	elseif ($moon_percent >= 85 && $moon_percent <= 87) { // 25
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-crescent-3';
	}
	elseif ($moon_percent >= 88 && $moon_percent <= 90) { // 26
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-crescent-4';
	}
	elseif ($moon_percent >= 91 && $moon_percent <= 94) { // 27
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-crescent-5';
	}
	elseif ($moon_percent >= 95 && $moon_percent <= 98) { // 28
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-crescent-6';
	}
	elseif ($moon_percent >= 99 && $moon_percent <= 100) { // 1 - new moon
		$moon_phase = 'wi-moon'.$moon_icon_set.'-new';
	}
	else {
		$moon_phase = 'wi-na';
	}
	return $moon_phase;
}

?>
