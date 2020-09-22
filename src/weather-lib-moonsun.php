<?php

$latitude = $weather_station_latitude;
$longitude = $weather_station_longitude;

$calculate_days = 30;

include('weather-lib-moon.php');
include(dirname(__FILE__).'/'.$lib_moonphase);
include(dirname(__FILE__).'/'.$lib_suncalc);

$c_now = mktime(0,0,0,date('m'),date('d'),date('Y'));
$c_now_moonphase = mktime(12,0,0,date('m'),date('d'),date('Y'));

$moon_basetime = mktime(0,56,36,7,20,2016);
$moon_days = 29.530588861;

$sun_data = array();
$moon_data = array();

for ($i=0; $i<=$calculate_days; $i++) {

  $c_year = date('Y', $c_now);
	$c_month = date('m', $c_now);
	$c_day = date('d', $c_now);

  $sun_data[$i] = date_sun_info($c_now, $latitude, $longitude);
	// astronomical_twilight_begin: 04:21:32
	// nautical_twilight_begin: 04:52:25
	// civil_twilight_begin: 05:24:08
  // sunrise: 05:52:11
	// transit: 10:46:46 // Zenith
  // sunset: 15:41:21
  // civil_twilight_end: 16:09:24
  // nautical_twilight_end: 16:41:06
  // astronomical_twilight_end: 17:12:00

  $sc = new SunCalc(new DateTime(gmdate("Y-m-d\TH:i:s\Z", $c_now)), $latitude, $longitude);
  $sunTimes = $sc->getSunTimes();
  // sunrise: sunrise (top edge of the sun appears on the horizon)
  // sunriseEnd: sunrise ends (bottom edge of the sun touches the horizon)
  // goldenHourEnd: morning golden hour (soft light, best time for photography) ends
  // solarNoon: solar noon (sun is in the highest position)
  // goldenHour: evening golden hour starts
  // sunsetStart: sunset starts (bottom edge of the sun touches the horizon)
  // sunset: sunset (sun disappears below the horizon, evening civil twilight starts)
  // dusk: dusk (evening nautical twilight starts)
  // nauticalDusk: nautical dusk (evening astronomical twilight starts)
  // night: night starts (dark enough for astronomical observations)
  // nadir: nadir (darkest moment of the night, sun is in the lowest position)
  // nightEnd: night ends (morning astronomical twilight starts)
  // nauticalDawn: nautical dawn (morning nautical twilight starts)
  // dawn: dawn (morning nautical twilight ends, morning civil twilight starts)

  $sun_data[$i]['morning_blue_hour_begin'] = $sun_data[$i]['civil_twilight_begin'];
  $sun_data[$i]['morning_blue_hour_end'] = $sun_data[$i]['sunrise']; // sunrise
  $sun_data[$i]['evening_blue_hour_begin'] = $sun_data[$i]['sunset']; // sunset
  $sun_data[$i]['evening_blue_hour_end'] = $sun_data[$i]['civil_twilight_end'];
//  $sun_data[$i]['morning_golden_hour_begin'] = $sunTimes['sunrise']->format('U');
  $sun_data[$i]['morning_golden_hour_begin'] = $sun_data[$i]['sunrise']; // sunrise
  $sun_data[$i]['morning_golden_hour_end'] = $sunTimes['goldenHourEnd']->format('U');
  $sun_data[$i]['evening_golden_hour_begin'] = $sunTimes['goldenHour']->format('U');
//  $sun_data[$i]['evening_golden_hour_end'] = $sunTimes['sunset']->format('U');
  $sun_data[$i]['evening_golden_hour_end'] = $sun_data[$i]['sunset']; // sunset
  $sun_data[$i]['date'] = $c_now;

	$tmp_moon = (Moon::calculateMoonTimes($c_month, $c_day, $c_year, $latitude, $longitude));
  $moon = new Solaris\MoonPhase($c_now_moonphase);
	$moonphase_tmp = $moon->phase()*100;
  $moonage_tmp = round(($moon_days /100 * $moonphase_tmp),1);
	if ($moonage_tmp <= 1) {
		$moonage_tmp.= '&nbsp;Tag';
	} else {
		$moonage_tmp.= '&nbsp;Tage';
	}
  if (date('%Y%m%d',$c_now)==date('%Y%m%d',$moon->full_moon())) {
		$moonphase_name = 'Vollmond<br />um '.strftime('%H:%M',$moon->full_moon());
	} elseif (date('%Y%m%d',$c_now)==date('%Y%m%d',$moon->new_moon())) {
		$moonphase_name = 'Neumond<br />um '.strftime('%H:%M',$moon->new_moon());
  } elseif (date('%Y%m%d',$c_now)==date('%Y%m%d',$moon->first_quarter())) {
		$moonphase_name = 'Erstes Virtel<br />um '.strftime('%H:%M',$moon->first_quarter());
  } elseif (date('%Y%m%d',$c_now)==date('%Y%m%d',$moon->last_quarter())) {
		$moonphase_name = 'Letztes Viertel<br />um '.strftime('%H:%M',$moon->last_quarter());
	} else {
		$moonphase_name = moonphase_name($moon->phase_name());
	}
  $moon_data[$i]['moonrise'] = $tmp_moon->moonrise;
  $moon_data[$i]['moonset'] = $tmp_moon->moonset;
  $moon_data[$i]['phase'] = $moonphase_tmp;
	$moon_data[$i]['phase_name'] = $moonphase_name;
  $moon_data[$i]['phase'] = round(($moon->phase()*100),0);
  $moon_data[$i]['age'] = $moonage_tmp;
  $moon_data[$i]['distance'] = round(($moon->distance()/1000),1).'&nbsp;tkm';
  $moon_data[$i]['illuminated'] = round(($moon->illumination()*100),0).'&nbsp;%';
  $moon_data[$i]['new_moon'] = $moon->new_moon();
  $moon_data[$i]['first_quarter'] = $moon->first_quarter();
  $moon_data[$i]['last_quarter'] = $moon->last_quarter();
  $moon_data[$i]['full_moon'] = $moon->full_moon();
  $moon_data[$i]['next_new_moon'] = $moon->next_new_moon();
  $moon_data[$i]['next_first_quarter'] = $moon->next_first_quarter();
  $moon_data[$i]['next_last_quarter'] = $moon->next_last_quarter();
  $moon_data[$i]['next_full_moon'] = $moon->next_full_moon();
  // phase(): the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
	// illumination(): the illuminated fraction of the Moon (0 = New, 1 = Full).
	// age(): the age of the Moon, in days.
	// distance(): the distance of the Moon from the centre of the Earth (kilometres).
	// diameter(): the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
	// sundistance(): the distance to the Sun (kilometres).
	// sundiameter(): the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
	// new_moon(): the time of the last New Moon (UNIX timestamp).
	// next_new_moon(): the time of the next New Moon (UNIX timestamp).
	// full_moon(): the time of the Full Moon in the current lunar cycle (UNIX timestamp).
	// next_full_moon(): the time of the next Full Moon in the current lunar cycle (UNIX timestamp).
	// first_quarter(): the time of the first quarter in the current lunar cycle (UNIX timestamp).
	// next_first_quarter(): the time of the next first quarter in the current lunar cycle (UNIX timestamp).
	// last_quarter(): the time of the last quarter in the current lunar cycle (UNIX timestamp).
	// next_last_quarter(): the time of the next last quarter in the current lunar cycle (UNIX timestamp).
	// phase_name(): the phase name.
	//  - New Moon - The Moon's unilluminated side is facing the Earth. The Moon is not visible (except during a solar eclipse).
	//  - Waxing Crescent - The Moon appears to be partly but less than one-half illuminated by direct sunlight. The fraction of the Moon's disk that is illuminated is increasing.
	//  - First Quarter - One-half of the Moon appears to be illuminated by direct sunlight. The fraction of the Moon's disk that is illuminated is increasing.
	//  - Waxing Gibbous - The Moon appears to be more than one-half but not fully illuminated by direct sunlight. The fraction of the Moon's disk that is illuminated is increasing.
	//  - Full Moon - The Moon's illuminated side is facing the Earth. The Moon appears to be completely illuminated by direct sunlight.
	//  - Waning Gibbous - The Moon appears to be more than one-half but not fully illuminated by direct sunlight. The fraction of the Moon's disk that is illuminated is decreasing.
	//  - Last Quarter - One-half of the Moon appears to be illuminated by direct sunlight. The fraction of the Moon's disk that is illuminated is decreasing.
	//  - Waning Crescent - The Moon appears to be partly but less than one-half illuminated by direct sunlight. The fraction of the Moon's disk that is illuminated is decreasing.
	$moon_data[$i]['date'] = $c_now;

	$c_now = $c_now + (60*60*24);
	$c_now_moonphase = $c_now_moonphase + (60*60*24);
}


// --- FUNCTIONS ---

function moonphase_name($the_field) {
	$replace_from = array();
	$replace_to = array();
	// Moonphase english -> german
	$replace_from[] = 'New Moon';
	$replace_to[] = 'Neumond';
  $replace_from[] = 'Waxing Crescent';
	$replace_to[] = 'Zunehmender Sichelmond';
  $replace_from[] = 'First Quarter';
	$replace_to[] = 'Zunehmender Mond';
  $replace_from[] = 'Waxing Gibbous';
	$replace_to[] = 'Zunehmender Dreiviertelmond';
  $replace_from[] = 'Full Moon';
	$replace_to[] = 'Vollmond';
  $replace_from[] = 'Waning Gibbous';
	$replace_to[] = 'Abnehmender Dreiviertelmond';
  $replace_from[] = 'Last Quarter';
	$replace_to[] = 'Abnehmender Mond';
  $replace_from[] = 'Waning Crescent';
	$replace_to[] = 'Abnehmender Sichelmond';
  // - replace -
  $total = count($replace_from);
  for ($i=0; $i<$total; $i++) {
    $the_field = preg_replace('/'.$replace_from[$i].'/',$replace_to[$i],$the_field);
  }
  return $the_field;
}

function moonphase_icon($moon_percent_tmp) {
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
	elseif ($moon_percent >= 41 && $moon_percent <= 43) { // 13
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-5';
	}
	elseif ($moon_percent >= 44 && $moon_percent <= 47) { // 14
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waxing-gibbous-6';
	}
	elseif ($moon_percent >= 48 && $moon_percent <= 53) { // 15 - full moon
		$moon_phase = 'wi-moon'.$moon_icon_set.'-full';
	}
	elseif ($moon_percent >= 54 && $moon_percent <= 56) { // 16
		$moon_phase = 'wi-moon'.$moon_icon_set.'-waning-gibbous-1';
	}
	elseif ($moon_percent >= 57 && $moon_percent <= 59) { // 17
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
	elseif ($moon_percent >= 74 && $moon_percent <= 76) { // 22 - last quarter
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
