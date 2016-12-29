<?php

require_once(dirname(__FILE__).'/'.$netatmo_nawsapiclient);

$config = array();
$config['client_id'] = $netatmo_client_id;
$config['client_secret'] = $netatmo_client_secret;
$config['scope'] = 'read_station';

$username = $netatmo_username;
$pwd = $netatmo_password;

$client = new NAWSApiClient($config);
$client->setVariable('username', $username);
$client->setVariable('password', $pwd);
try {
  $tokens = $client->getAccessToken();
  $refresh_token = $tokens['refresh_token'];
  $access_token = $tokens['access_token'];
}
catch(NAClientException $ex) {
  die('An error occcured while trying to retrive your tokens!');
}

$ws_id = $netatmo_ws_id;
$ws_out_id = $netatmo_ws_out_id;
$ws_rain_id = $netatmo_ws_rain_id;
$ws_wind_id = $netatmo_ws_wind_id;

$data = $client->getData(NULL, FALSE);
foreach($data['devices'] as $device) {
	if ($device['_id']==$ws_id) {
		$netatmo_station_name = $device['station_name'];
		$netatmo_station_time = $device['dashboard_data']['time_utc'];
		$netatmo_pressure = $device['dashboard_data']['Pressure'];
		$netatmo_pressure_trend = $device['dashboard_data']['pressure_trend'];
		foreach($device['modules'] as $module) {
			if ($module['_id']==$ws_out_id) {
	      $netatmo_outdoor_batterystatus = $module['battery_vp'];
	      $netatmo_outdoor_wifistatus = $module['wifi_status'];
	      $netatmo_outdoor_firmware = $module['firmware'];
				$netatmo_outdoor_time = $module['dashboard_data']['time_utc'];
				$netatmo_temperature = $module['dashboard_data']['Temperature'];
		    $netatmo_temperature_trend = $module['dashboard_data']['temp_trend'];
		    $netatmo_humidity = $module['dashboard_data']['Humidity'];
			}
			if ($module['_id']==$ws_rain_id && $ws_rain_id!='') {
	      $netatmo_rain_batterystatus = $module['battery_vp'];
	      $netatmo_rain_wifistatus = $module['wifi_status'];
	      $netatmo_rain_firmware = $module['firmware'];
		    $netatmo_rain_time = $module['dashboard_data']['time_utc'];
		    $netatmo_rain_1hrs = $module['dashboard_data']['sum_rain_1'];
		    $netatmo_rain_24hrs = $module['dashboard_data']['sum_rain_24'];
			}
			if ($module['_id']==$ws_wind_id && $ws_wind_id!='') {
	      $netatmo_wind_batterystatus = $module['battery_vp'];
	      $netatmo_wind_wifistatus = $module['wifi_status'];
	      $netatmo_wind_firmware = $module['firmware'];
		    $netatmo_wind_time = $module['dashboard_data']['time_utc'];
		    $netatmo_wind_strength = $module['dashboard_data']['WindStrength'];
		    $netatmo_wind_angle = $module['dashboard_data']['WindAngle'];
		    $netatmo_gust_strength = $module['dashboard_data']['GustStrength'];
	      $netatmo_gust_angle = $module['dashboard_data']['GustAngle'];
			}
		}
	}
}

$optimized = FALSE;
$real_time = FALSE;
$device = $ws_id;

$limit = 1;
$scale = "3hours";
$date_begin = time() -3*3600; // 3 hours ago
$date_end = time(); //now

$module = NULL;
$type = 'Pressure';
$netatmo_pressure_3hrs_tmp = $client->getMeasure($device, $module, $scale, $type, $date_begin, $date_end, $limit, $optimized, $real_time);

foreach ($netatmo_pressure_3hrs_tmp as $date => $values) {
  foreach ($values as $value) {
		$netatmo_pressure_3hrs = $value;
  }
}

$module = $ws_out_id;
$type = 'Temperature';
$netatmo_temperature_3hrs_tmp = $client->getMeasure($device, $module, $scale, $type, $date_begin, $date_end, $limit, $optimized, $real_time);

foreach ($netatmo_temperature_3hrs_tmp as $date => $values) {
  foreach ($values as $value) {
		$netatmo_temperature_3hrs = $value;
  }
}

$netatmo_pressure_trend_value = $netatmo_pressure - $netatmo_pressure_3hrs;
$netatmo_temperature_trend_value = $netatmo_temperature - $netatmo_temperature_3hrs;

// --- FUNCTIONS ---

function float_prefix($int) {
	$int = round($int,1);
	return ($int>0)?"+$int":"$int";
}

function netatmo_replace($the_field) {
	$replace_from = array('up'); // trend up
	$replace_to = array('wi-direction-up-right');
	$replace_from[] = 'stable'; // trend stable
	$replace_to[] = 'wi-direction-right';
	$replace_from[] = 'down'; // trend down
	$replace_to[] = 'wi-direction-down-right';

  $total = count($replace_from);
  for ($i=0; $i<$total; $i++) {
    $the_field = preg_replace('/'.$replace_from[$i].'/',$replace_to[$i],$the_field);
  }
  return $the_field;
}

function calculate_windchill($w_temp, $w_wind) {
  // $w_temp in Celsius, $w_wind in km/h
  // => http://www.freemathhelp.com/wind-chill.html
	if ($w_wind<177 && $w_wind>=4.8 && $w_temp>(-50) && $w_temp<=10) {
    $w_chill = 13.12 + 0.6215 * $w_temp - 11.37 * pow($w_wind,0.16) + 0.3965 * $w_temp * pow($w_wind,0.16);
  } else {
    $w_chill = $w_temp;
	}
  return $w_chill;
}

function calculate_heatindex($w_temp, $w_humidity) {
  // w_$w_temptemp in Celsius, $w_humidity in %
  // => https://www.easycalculation.com/weather/Heat-index.php
  if ($w_temp>=26.7 && $w_humidity>=40) {
    $t = ($w_temp)*(9/5)+32; // convert to fahrenheit
    $r = ($w_humidity);
    $t2 = pow($t,2);
    $rh2 = pow($r,2);
    $index = -42.379 + 2.04901523*$t + 10.14333127*$r - 0.22475541*$t*$r - 6.83783e-03*$t2 - 5.481717e-02*$rh2 + 1.22874e-03*$t2*$r + 8.5282e-04*$t*$rh2 - 1.99e-06*$t2*$rh2;
    $heatindex = 5/9*($index-32); // convert to celsius
  } else {
    $heatindex = $w_temp;
	}
  return $heatindex;
}

function calculate_dewpoint($MeasuredAirTempC, $MeasuredHumidityPercent) {
  // $MeasuredAirTempC in Celsius, $MeasuredHumidityPercent in %
  // => http://www.opto22.com/community/showthread.php?t=588
	// These algorithms are based on the Magnus-Tetens formula.
	// Calculate dew point temperature using measured air temperature
	// and measured relative humidity. Air temperature must be between
	// 0C and 60C. Relative humidity must be between 1% and 100%.
	if ($MeasuredAirTempC<0) {
		$MeasuredAirTempC;
	}
	$DewPointFactorA = 17.27;
	$DewPointFactorB = 237.7;
	$DewPointResultAlpha = ($DewPointFactorA * $MeasuredAirTempC) / ($DewPointFactorB + $MeasuredAirTempC) + log($MeasuredHumidityPercent / 100);
	$CalcDewPointValue = ($DewPointFactorB * $DewPointResultAlpha) / ($DewPointFactorA - $DewPointResultAlpha);
	return $CalcDewPointValue;
}

// not used
function battery_status($the_value, $the_module) {
	$the_text = '';
	if ($the_module=='wind') {
		if ($the_value>=6000) { $the_text = 'max'; }
		else if ($the_value<6000 && $the_value>=5590) { $the_text = 'full'; }
		else if ($the_value<5590 && $the_value>=5180) { $the_text = 'high'; }
		else if ($the_value<5180 && $the_value>=4770) { $the_text = 'medium'; }
		else if ($the_value<4770 && $the_value>=4360) { $the_text = 'low'; }
		else if ($the_value<4360) { $the_text = 'very low'; }
	} else if ($the_module=='in') {
		if ($the_value>=6000) { $the_text = 'max'; }
		else if ($the_value<6000 && $the_value>=5640) { $the_text = 'full'; }
		else if ($the_value<5540 && $the_value>=5280) { $the_text = 'high'; }
		else if ($the_value<5280 && $the_value>=4920) { $the_text = 'medium'; }
		else if ($the_value<4920 && $the_value>=4560) { $the_text = 'low'; }
		else if ($the_value<4560) { $the_text = 'very low'; }
	} else {
		if ($the_value>=6000) { $the_text = 'max'; }
		else if ($the_value<6000 && $the_value>=5500) { $the_text = 'full'; }
		else if ($the_value<5500 && $the_value>=5000) { $the_text = 'high'; }
		else if ($the_value<5000 && $the_value>=4500) { $the_text = 'medium'; }
		else if ($the_value<4500 && $the_value>=4000) { $the_text = 'low'; }
		else if ($the_value<4000) { $the_text = 'very low'; }
	}
	return $the_text;
}

// not used
function wifi_status($the_value) {
	if ($the_value>=86) { $the_text = 'bad'; }
	else if ($the_value<86 && $the_value>=71) { $the_text = 'average'; }
	else if ($the_value<71 && $the_value>=56) { $the_text = 'average'; }
	else if ($the_value<=56 ) { $the_text = 'good'; }
	return $the_text;
}

?>
