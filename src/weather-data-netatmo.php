<?php

require_once(dirname(__FILE__).'/'.$netatmo_nawsapiclient);

$config = array();
$config['client_id'] = $netatmo_client_id;
$config['client_secret'] = $netatmo_client_secret;
$config['scope'] = 'read_station';

$client = new NAWSApiClient($config);
$client->setVariable('username',$netatmo_username);
$client->setVariable('password',$netatmo_password);
try {
  $tokens = $client->getAccessToken();
  $refresh_token = $tokens['refresh_token'];
  $access_token = $tokens['access_token'];
}
catch(NAClientException $ex) {
  echo 'Netatmo Wetterstation Daten: Es ist ein Fehler bei der Authorisation aufgetreten.';
}

$netatmo_outdoor_module = false;
$netatmo_rain_module = false;
$netatmo_wind_module = false;

$data = $client->getData(NULL, FALSE);
foreach($data['devices'] as $device) {
	if ($device['_id']==$netatmo_ws_id) {
		$netatmo_station_name = $device['station_name'];
		$netatmo_station_laststatusstore = $device['last_status_store'];
		$netatmo_station_wifistatus = $device['wifi_status'];
		$netatmo_station_place_city = $device['place']['city'];
		$netatmo_station_place_country = $device['place']['country'];
		$netatmo_station_place_timezone = $device['place']['timezone'];
		$netatmo_station_place_altitude = $device['place']['altitude'].'m';
		$netatmo_station_place_longitude = $device['place']['location'][0];
		$netatmo_station_place_latitude = $device['place']['location'][1];
		$netatmo_station_time = $device['dashboard_data']['time_utc'];
		$netatmo_pressure = $device['dashboard_data']['Pressure'];
		$netatmo_pressure_trend = $device['dashboard_data']['pressure_trend'];
		foreach($device['modules'] as $module) {
			if ($module['type']=='NAModule1') {
        $netatmo_outdoor_id = $module['_id'];
        $netatmo_outdoor_module = true;
				$netatmo_outdoor_name = $module['module_name'];
				$netatmo_outdoor_lastseen = $module['last_seen'];
	      $netatmo_outdoor_batterystatus = $module['battery_percent'];
				$netatmo_outdoor_battery = $module['battery_vp'];
	      $netatmo_outdoor_rfstatus = $module['rf_status'];
	      $netatmo_outdoor_firmware = $module['firmware'];
				$netatmo_outdoor_time = $module['dashboard_data']['time_utc'];
				$netatmo_temperature = $module['dashboard_data']['Temperature'];
		    $netatmo_temperature_trend = $module['dashboard_data']['temp_trend'];
				$netatmo_temperature_min = $module['dashboard_data']['min_temp'];
				$netatmo_temperature_min_time = $module['dashboard_data']['date_min_temp'];
				$netatmo_temperature_max = $module['dashboard_data']['max_temp'];
				$netatmo_temperature_max_time = $module['dashboard_data']['date_max_temp'];
		    $netatmo_humidity = $module['dashboard_data']['Humidity'];
			} else
			if ($module['type']=='NAModule3') {
        $netatmo_rain_id = $module['_id'];
				$netatmo_rain_module = true;
				$netatmo_rain_name = $module['module_name'];
				$netatmo_rain_lastseen = $module['last_seen'];
	      $netatmo_rain_batterystatus = $module['battery_percent'];
				$netatmo_rain_battery = $module['battery_vp'];
	      $netatmo_rain_rfstatus = $module['rf_status'];
	      $netatmo_rain_firmware = $module['firmware'];
		    $netatmo_rain_time = $module['dashboard_data']['time_utc'];
				$netatmo_rain = $module['dashboard_data']['Rain'];
		    $netatmo_rain_1hrs = $module['dashboard_data']['sum_rain_1'];
		    $netatmo_rain_24hrs = $module['dashboard_data']['sum_rain_24'];
			} else
			if ($module['type']=='NAModule2') {
        $netatmo_wind_id = $module['_id'];
				$netatmo_wind_module = true;
				$netatmo_wind_name = $module['module_name'];
				$netatmo_wind_lastseen = $module['last_seen'];
	      $netatmo_wind_batterystatus = $module['battery_percent'];
				$netatmo_wind_battery = $module['battery_vp'];
	      $netatmo_wind_rfstatus = $module['rf_status'];
	      $netatmo_wind_firmware = $module['firmware'];
		    $netatmo_wind_time = $module['dashboard_data']['time_utc'];
		    $netatmo_wind_strength = $module['dashboard_data']['WindStrength'];
				$netatmo_wind_strength_max = $module['dashboard_data']['max_wind_str'];
				$netatmo_wind_strength_max_time = $module['dashboard_data']['date_max_wind_str'];
		    $netatmo_wind_angle = $module['dashboard_data']['WindAngle'];
		    $netatmo_gust_strength = $module['dashboard_data']['GustStrength'];
	      $netatmo_gust_angle = $module['dashboard_data']['GustAngle'];
			}
		}
	}
}

if (isset($netatmo_temperature) && isset($netatmo_humidity) && isset($netatmo_pressure)) {
	$Pnn = 1023; // Druck auf NN
	// Luftdichte (kg/m³)
	$air_density = round((($Pnn*1000)/(287.058*($netatmo_temperature+273.15)))/10,2);
	// Sättigungsdruck (hPa/mbar)
	if ($netatmo_temperature > 0) {
		$saturated_vapor_pressure = round(exp(19.016-(4064.95/($netatmo_temperature+236.25))),2); // für Temperatur über 0°C
	} else {
		$saturated_vapor_pressure = round(61.1657 * exp(22.509*(1-273.15/($netatmo_temperature+273.15))),2); // für Temperatur unter 0°C
	}
	// Dampfdruck
	$dry_vapor_pressure_equivalent = round($saturated_vapor_pressure-($saturated_vapor_pressure-(($saturated_vapor_pressure/100)*$netatmo_humidity)),2);
	// Aequivalent-Temperatur in Grad - Theta E - Feuchteenergie - Equivalent Potential Temperature
	// - https://storm-chasers.de/lexicon/Entry/49-Theta-E/
	// - http://www.wetterstationen.info/forum/allgemeines-softwareforum/schneefallgrenze-genau-berechnen-(theta_e)/
	$equivalent_potential_temperature = ($Pnn/100)+($netatmo_temperature+2.5*(0.622*(($dry_vapor_pressure_equivalent/$netatmo_pressure)*1000)));
	//Schneefallgrenze
	$snow_line = round(($equivalent_potential_temperature-12)*(1000/12),0);
}

$optimized = FALSE;
$real_time = FALSE;
$device = $netatmo_ws_id;

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

$module = $netatmo_outdoor_id;
$type = 'Temperature';
$netatmo_temperature_3hrs_tmp = $client->getMeasure($device, $module, $scale, $type, $date_begin, $date_end, $limit, $optimized, $real_time);

foreach ($netatmo_temperature_3hrs_tmp as $date => $values) {
  foreach ($values as $value) {
		$netatmo_temperature_3hrs = $value;
  }
}

// --- FUNCTIONS ---

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

?>
