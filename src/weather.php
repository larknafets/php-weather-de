<?php

include(dirname(__FILE__).'/'.$buffer_lib);
$fp = @fsockopen('api.netatmo.com', 80, $errno, $errstr, 30);
if (!$fp) {
	$stat_netatmo='off';
	$netatmo_station_time = time();
} else {
	include(dirname(__FILE__).'/weather-data-netatmo.php');
	$weather_station_longitude = str_replace(',','.',$netatmo_station_place_longitude);
	$weather_station_latitude = str_replace(',','.',$netatmo_station_place_latitude);
	$stat_netatmo='on';
}
include(dirname(__FILE__).'/weather-lib-moonsun.php');
include(dirname(__FILE__).'/weather-data-wettercom.php');
include(dirname(__FILE__).'/weather-data-dwd.php');
if ($bsh_tides=='yes') {
	include(dirname(__FILE__).'/weather-data-bsh.php');
}

// Start page output

if ($dwd_alert_status==1) {
	echo '
<h2>Wetterwarnungen</h2>
<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Warnungen">
';
	if (count($dwd_alert_data)==0) {
		echo '<tr><td colspan="2">'.$dwd_alert_info.'</td></tr>';
	} else {
		foreach($dwd_alert_data as $dwd_alert) {
			$dwd_areacolor = '';
			foreach($tmp_alert_data->info->eventCode as $eventcode) {
			  if ($eventcode->valueName=='AREA_COLOR') {
		      $dwd_areacolor = $eventcode->value;
			  }
		  }
      if ($dwd_areacolor!='') {
			  echo '<tr><td colspan="2" bgcolor="rgb('.str_replace(' ',',',$dwd_areacolor).')">&nbsp;</td></tr>';
      } else {
        echo '<tr><td colspan="2">&nbsp;</td></tr>';
      }
      echo '
<tr><td colspan="2"><b><span class="big">'.$dwd_alert->info->headline.'</span></b></td></tr>
<tr><td colspan="2">';
			if ($dwd_alert->info->urgency=='Immediate') {
				echo 'Herausgegebene Warnung';
			} elseif ($dwd_alert->info->urgency=='Future') {
				echo 'Vorabinformation';
			} else {
				echo $dwd_alert->info->urgency;
			}
			echo ' / ';
			if ($dwd_alert->info->severity=='Minor') {
				echo 'Wetterwarnung';
			} elseif ($dwd_alert->info->severity=='Moderate') {
				echo 'Markante Wetterwarnung';
			} elseif ($dwd_alert->info->severity=='Severe') {
				echo 'Unwetterwarnung';
			} elseif ($dwd_alert->info->severity=='Extreme') {
				echo 'Extreme Unwetterwarnung';
			} else {
				echo $dwd_alert->info->severity;
			}
			echo '</td></tr>
<tr><td><span class="small">';
			if ($dwd_alert->msgType=='Alert') {
				echo 'Neuausgabe';
			} elseif ($dwd_alert->msgType=='Update') {
				echo 'Aktualisierung';
			} elseif ($dwd_alert->msgType=='Cancel') {
				echo 'Aufhebung';
			} else {
				echo $dwd_alert->msgType;
			}
			foreach($dwd_alert->info->area as $area) {
			  if ($area->geocode[0]->value==$dwd_warncellid) {
		      $dwd_areadesc = (string) $area->areaDesc;
			  }
			}
echo '</span></td><td><span class="small">am '.strftime('%d.%m.%Y %H.%M',strtotime($dwd_alert->sent)).' Uhr für '.$dwd_areadesc.'</span></td></tr>
<tr><td><span class="small">Zeitraum</span></td><td><b>'.strftime('%d.%m.%Y %H.%M',strtotime($dwd_alert->info->onset)).' - '.strftime('%d.%m.%Y %H.%M',strtotime($dwd_alert->info->expires)).'</b></td></tr>
<tr><td colspan="2">'.$dwd_alert->info->description.'</td></tr>
';
			if ($dwd_alert->info->instruction!='') {
				echo '<tr><td colspan="2"><span class="small">'.$dwd_alert->info->instruction.'</span></td></tr>';
			}
			echo '<tr><td colspan="2" align="right"><span class="small">'.$dwd_alert->info->senderName.'</span></td></tr>';
		}
	}
	echo '<tr><td colspan="2" align="right"><span class="small"><hr /><b>Datenquelle: </b><a rel="nofollow" target="_blank" title="Wettergefahren (Deutscher Wetterdienst)" href="http://www.wettergefahren.de/">Wettergefahren (Deutscher Wetterdienst)</a></span></td></tr>
</table><br /><br />

';
} else {
	echo '<p><div align="center">'.$dwd_alert_info.'</div></p>

';
}

/* ================================================= */

echo '<p><span class="small"><a href="#aktuell" title="Aktuell">Aktuell</a> | <a href="#vorhersage" title="Vorhersage">Vorhersage</a>';
if ($weather_maps_text!='') {
	echo ' | <a href="#wetterkarten" title="Wetterkarten">Wetterkarten</a>';
}
echo ' | <a href="#wetterstation" title="Wetterstation">Wetterstation</a></span></p>';

echo '
<a name="aktuell"></a>
<h2>Aktuell</h2>

<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Aktuell">

';

if ($stat_netatmo=='off') {
	echo '
  <tr>
  <td colspan="4"><span class="big"><b>'.strftime('%A, der %d.%m.%Y um %H:%M',intval($netatmo_station_time)).' Uhr</b></span><br />&nbsp;</td>
  </tr>

  <tr><td colspan="4"><p><div align="center">Es konnte keine Verbindung zur Wetterstation hergestellt werden!</div></p></td></tr>';
} else {
	if (intval($netatmo_station_time)<(time()-(60*60*12*1))) {
    echo '
<tr>
<td colspan="4"><span class="big"><b>'.strftime('%A, der %d.%m.%Y um %H:%M',intval(time())).' Uhr</b></span><br />&nbsp;</td>
</tr>
';
		echo '
<tr>
<td colspan="4"><p><div align="center">Es liegen keine aktuellen Stationsdaten vor!<br />Letzte Meldung am '.strftime('%d.%m.%Y um %H:%M',intval($netatmo_station_time)).' Uhr.</div></p></td>
</tr>
';
	} else {
    echo '
<tr>
<td colspan="4"><span class="big"><b>'.strftime('%A, der %d.%m.%Y um %H:%M',intval($netatmo_station_time)).' Uhr</b></span><br />&nbsp;</td>
</tr>
';
    if ($netatmo_outdoor_module==true && intval($netatmo_outdoor_lastseen)>(time()-(60*60*12*1))) {
      echo '
<tr>
<td>Temperatur</td>
<td align="center"><span class="big"><b>'.$netatmo_temperature.unit('temp').'</b></span><br /><span class="small">'.$netatmo_temperature_3hrs.unit('temp').'&nbsp;/3h</span></td>
<td align="center"><i class="wi '.weather_icon($netatmo_temperature_trend).'"></i></td>
<td align="center"><span class="small">min.&nbsp;'.$netatmo_temperature_min.unit('temp').' @'.strftime('%H:%M',intval($netatmo_temperature_min_time)).'<br />max.&nbsp;'.$netatmo_temperature_max.unit('temp').' @'.strftime('%H:%M',intval($netatmo_temperature_max_time)).'</span></td>
</tr>
';
		}

		if ( ($netatmo_outdoor_module==true && intval($netatmo_outdoor_lastseen)>(time()-(60*60*12*1))) AND ($netatmo_wind_module==true && intval($netatmo_wind_lastseen)>(time()-(60*60*12*1))) ) {
			echo '
<tr>
<td>Gefühlte Temperatur&sup1;</td>
<td align="center"><span class="big"><b>'.round(calculate_feels_like_temperature($netatmo_temperature, $netatmo_humidity, $netatmo_wind_strength),1).unit('temp').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>
<tr>
<td>Australische scheinbare Temperatur&sup1;</td>
<td align="center"><span class="big"><b>'.round(calculate_australian_apparent_temperature($netatmo_temperature, $netatmo_humidity, $netatmo_wind_strength),1).unit('temp').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>
';
		}

		if ($netatmo_wind_module==true && intval($netatmo_wind_lastseen)>(time()-(60*60*12*1))) {
			echo '
<tr>
<td>Windchill&sup1;</td>
<td align="center"><span class="big"><b>'.round(calculate_windchill($netatmo_temperature, $netatmo_wind_strength),1).unit('temp').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>
';
		}

		if ($netatmo_outdoor_module==true && intval($netatmo_outdoor_lastseen)>(time()-(60*60*12*1))) {
			echo '
<tr>
<td>Hitzeindex&sup1;</td>
<td align="center"><span class="big"><b>'.round(calculate_heatindex($netatmo_temperature, $netatmo_humidity),1).unit('temp').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>

<tr>
<td>Taupunkt&sup1;</td>
<td align="center"><span class="big"><b>'.round(calculate_dewpoint($netatmo_temperature, $netatmo_humidity),1).unit('temp').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>

<tr>
<td>Luftdruck</td>
<td align="center"><span class="big"><b>'.$netatmo_pressure.unit('pressure').'</b></span><br /><span class="small">'.$netatmo_pressure_3hrs.unit('pressure').'&nbsp;/3h</span></td>
<td align="center"><i class="wi '.weather_icon($netatmo_pressure_trend).'"></i></td>
<td align="center">&nbsp;</td>
</tr>

<tr>
<td>Luftfeuchtigkeit</td>
<td align="center"><span class="big"><b>'.$netatmo_humidity.unit('hum').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>

<tr>
<td>Luftdichte&sup1;</td>
<td align="center"><span class="big"><b>'.round($air_density).unit('airdensity').'</b></span></td>
<td colspan="2" align="left"><span class="small">Sättigungsdruck:&nbsp;'.round($saturated_vapor_pressure).unit('pressure').'<br />Dampfdruck:&nbsp;'.round($dry_vapor_pressure_equivalent).unit('pressure').' </span></td>
</tr>

<tr>
<td>Theta-E&sup1;</td>
<td align="center"><span class="big"><b>'.round($equivalent_potential_temperature).unit('temp').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>

<tr>
<td>Schneefallgrenze&sup1;</td>
<td align="center"><span class="big"><b>'.number_format(round($snow_line),0,',','.').unit('altitude').'</b></span></td>
<td colspan="2" align="center">&nbsp;</td>
</tr>
';
		}

		if ($netatmo_rain_module==true && intval($netatmo_rain_lastseen)>(time()-(60*60*12*1))) {
			echo '
<tr>
<td>Niederschlag</td>
<td align="center"><span class="big"><b>'.round($netatmo_rain,1).unit('rain').'</b></span></td>
<td align="center">&nbsp;</td>
<td align="center"><span class="small">'.round($netatmo_rain_1hrs,1).unit('rain').'&nbsp;/1h<br />'.round($netatmo_rain_24hrs,1).unit('rain').'&nbsp;/24h</span></td>
</tr>
';
		}

		if ($netatmo_wind_module==true && intval($netatmo_wind_lastseen)>(time()-(60*60*12*1))) {
			echo '
<tr>
<td>Wind</td>
<td align="center"><span class="big"><b>'.$netatmo_wind_strength.unit('strength').'</b> </span><span class="small">Böen: '.$netatmo_gust_strength.unit('strength').'</span><br /><span class="small">'.wind_strength($netatmo_wind_strength)[1].' aus '.wind_direction($netatmo_wind_angle).' ('.$netatmo_wind_angle.unit('angle').')</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($netatmo_wind_strength)[0].'"></i><i class="wi wi-wind from-'.$netatmo_wind_angle.'-deg"></i></td>
<td align="center"><span class="small">max.&nbsp;'.$netatmo_wind_strength_max.unit('strength').' @'.strftime('%H:%M',intval($netatmo_wind_strength_max_time)).'</span></td>
</tr>
';
		}

	}

}

echo '
<tr>
<td>';
if ($bsh_tides=='yes') { echo 'Gezeiten<br />'; }
echo 'Sonne/Mond&sup1;</td>
';
if ($bsh_tides=='yes') {
	echo '<td align="center">';
	if (intval($tide_text[$tide_date[0]][0][3])>=intval($netatmo_station_time)) {
		echo '<span class="small">Demnächst: <i class="wi '.weather_icon($tide_text[$tide_date[0]][0][2]).'"></i></span> '.$tide_text[$tide_date[0]][0][0];
	} else
	if (intval($tide_text[$tide_date[0]][0][3])<=intval($netatmo_station_time) && intval($tide_text[$tide_date[0]][1][3])>=intval($netatmo_station_time)) {
		echo '<span class="small">Zuletzt: <i class="wi '.weather_icon($tide_text[$tide_date[0]][0][2]).'"></i></span> '.$tide_text[$tide_date[0]][0][0].'<br /><span class="small">Demnächst: <i class="wi '.weather_icon($tide_text[$tide_date[0]][1][2]).'"></i></span> '.$tide_text[$tide_date[0]][1][0];
	} else
	if (intval($tide_text[$tide_date[0]][1][3])<=intval($netatmo_station_time) && intval($tide_text[$tide_date[0]][2][3])>=intval($netatmo_station_time)) {
		echo '<span class="small">Zuletzt: <i class="wi '.weather_icon($tide_text[$tide_date[0]][1][2]).'"></i></span> '.$tide_text[$tide_date[0]][1][0].'<br /><span class="small">Demnächst: <i class="wi '.weather_icon($tide_text[$tide_date[0]][2][2]).'"></i></span> '.$tide_text[$tide_date[0]][2][0];
	} else
	if (intval($tide_text[$tide_date[0]][2][3])<=intval($netatmo_station_time) && intval($tide_text[$tide_date[0]][3][3])>=intval($netatmo_station_time) && intval($tide_text[$tide_date[0]][3][3])>0) {
		echo '<span class="small">Zuletzt: <i class="wi '.weather_icon($tide_text[$tide_date[0]][2][2]).'"></i></span> '.$tide_text[$tide_date[0]][2][0].'<br /><span class="small">Demnächst: <i class="wi '.weather_icon($tide_text[$tide_date[0]][3][2]).'"></i></span> '.$tide_text[$tide_date[0]][3][0];
	} else
	if (intval($tide_text[$tide_date[0]][3][3])<=intval($netatmo_station_time) && intval($tide_text[$tide_date[0]][3][3])>0) {
		echo '<span class="small">Zuletzt: <i class="wi '.weather_icon($tide_text[$tide_date[0]][3][2]).'"></i></span> '.$tide_text[$tide_date[0]][3][0];
	} else
	if (intval($tide_text[$tide_date[0]][2][3])<=intval($netatmo_station_time) && intval($tide_text[$tide_date[0]][3][3])==0) {
		echo '<span class="small">Zuletzt: <i class="wi '.weather_icon($tide_text[$tide_date[0]][2][2]).'"></i></span> '.$tide_text[$tide_date[0]][2][0];
	}
	echo '</td>';
} else {
	echo '<td align="center">&nbsp;</td>';
}
echo '
<td align="center">';
if (intval($sun_data[0]['sunrise'])>=intval($netatmo_station_time)) {
	echo '<i class="wi wi-sunrise"></i><br />'.strftime('%H:%M',intval($sun_data[0]['sunrise']));
} else
if (intval($sun_data[0]['sunset'])>=intval($netatmo_station_time)) {
	echo '<i class="wi wi-sunset"></i><br />'.strftime('%H:%M',intval($sun_data[0]['sunset']));
} else { echo '<i class="wi wi-stars"></i>'; }
echo '</td>
<td align="center">';
if (intval($moon_data[0]['moonrise'])>=intval($netatmo_station_time) && intval($moon_data[0]['moonrise'])>intval($moon_data[0]['moonset'])) {
	echo '<i class="wi wi-moonrise"></i><br />'.strftime('%H:%M',intval($moon_data[0]['moonrise']));
} else
if (intval($moon_data[0]['moonset'])>=intval($netatmo_station_time)) {
	echo '<i class="wi wi-moonset"></i><br />'.strftime('%H:%M',intval($moon_data[0]['moonset']));
}
echo '</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td colspan="2"><span class="small">';


if (intval($moon_data[0]['new_moon'])<intval($netatmo_station_time)) {
	$the_next_new_moon = $moon_data[0]['next_new_moon'];
} else {
	$the_next_new_moon = $moon_data[0]['new_moon'];
}

if (intval($moon_data[0]['full_moon'])<intval($netatmo_station_time)) {
	$the_next_full_moon = $moon_data[0]['next_full_moon'];
} else {
	$the_next_full_moon = $moon_data[0]['full_moon'];
}

if (intval($the_next_new_moon) < intval($the_next_full_moon)) {
	echo '
Nächster Neumond: '.strftime('%d.%m.%Y',intval($the_next_new_moon)).'
<br />
Nächster Vollmond: '.strftime('%d.%m.%Y',intval($the_next_full_moon)).'
';
} else {
  echo '
Nächster Vollmond: '.strftime('%d.%m.%Y',intval($the_next_full_moon)).'
<br />
Nächster Neumond: '.strftime('%d.%m.%Y',intval($the_next_new_moon)).'
';
}
echo '</span></td>
</tr>
';

if ($dwd_alert_status!=2) {
  echo '
<tr>
<td colspan="4">Wetterlage</td>
</tr>
<tr>
<td colspan="4"><span class="small">'.$dwd_actual.'</span></td>
</tr>';
}

echo '
<!-- Credits -->
<tr>
<td colspan="4" align="right"><span class="small"><hr /><b>Datenquellen: </b><a title="Private Wetterstation" href="#wetterstation">Private Wetterstation '.$netatmo_station_name.'</a> | &sup1;&nbsp;<a title="berechnet" href="#berechnete_werte">berechnet</a> | Wetterlage: <a rel="nofollow" target="_blank" title="Deutscher Wetterdienst" href="http://www.dwd.de/">Deutscher Wetterdienst</a>';
if ($bsh_tides=='yes') {
	echo ' | '.$bsh_credit;
}
echo '</span></td>
</tr>
</table>
';

/* ================================================= */

echo '
<a name="vorhersage"></a>
<h2>Vorhersage</h2>

<p><span class="small"><a href="#heute" title="Heute">Heute</a> | <a href="#morgen" title="Morgen">Morgen</a> | <a href="#uebermorgen" title="Übermorgen">Übermorgen</a></span></p>

<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Vorhersage">
';

for ($i=0; $i<=2; $i++) {
	$today = '';
	$today_anchor = '';
	switch ($i) {
	  case 0:
			$today = 'HEUTE - ';
			$today_anchor = '<a name="heute"></a>';
			break;
		case 1:
			$today = 'Morgen - ';
			$today_anchor = '<a name="morgen"></a>';
      break;
    case 2:
			$today = 'Übermorgen - ';
			$today_anchor = '<a name="uebermorgen"></a>';
      break;
	}
	echo '
<!-- Vorhersage: '.strftime('%A, der %d.%m.%Y',intval($sun_data[$i]['date'])).' -->
<tr>
<td colspan="5"><br /><br />'.$today_anchor.'<span class="big"><b>'.$today.strftime('%A, der %d.%m.%Y',intval($sun_data[$i]['date'])).'</b></span><br />&nbsp;</td>
</tr>

<tr>
<th width="8%">&nbsp;</th>
<th width="23%">Morgens</th>
<th width="23%">Mittags</th>
<th width="23%">Abends</th>
<th width="23%">Nachts</th>
</tr>

<tr>
<td>&nbsp;</td>
<td colspan="4"><hr /></td>
</tr>

<!-- Temperatur -->
<tr>
<td align="center" rowspan="2"><i class="wi wi-thermometer"></i></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[0]->tx.unit('temp').'</b></span></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[1]->tx.unit('temp').'</b></span></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[2]->tx.unit('temp').'</b></span></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[3]->tx.unit('temp').'</b></span></td>
</tr>
<tr>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[0]->tn.unit('temp').'</span></td>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[1]->tn.unit('temp').'</span></td>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[2]->tn.unit('temp').'</span></td>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[3]->tn.unit('temp').'</span></td>
</tr>

<!-- Wolken, Regen, Sonne -->
<tr>
<td>&nbsp;</td>
<td align="center"><i class="wi '.weather_icon(strval($forecast_data->forecast[0]->date[$i]->time[0]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[0]->w_txt.'</span></td>
<td align="center"><i class="wi '.weather_icon(strval($forecast_data->forecast[0]->date[$i]->time[1]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[1]->w_txt.'</span></td>
<td align="center"><i class="wi '.weather_icon(strval($forecast_data->forecast[0]->date[$i]->time[2]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[2]->w_txt.'</span></td>
<td align="center"><i class="wi '.weather_icon(strval($forecast_data->forecast[0]->date[$i]->time[3]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[3]->w_txt.'</span></td>
</tr>

<!-- Regenwahrscheinlichkeit -->
<tr>
<td align="center"><i class="wi wi-umbrella"></i></td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[0]->pc.unit('hum').'</td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[1]->pc.unit('hum').'</td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[2]->pc.unit('hum').'</td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[3]->pc.unit('hum').'</td>
</tr>

<!-- Wind -->
<tr>
<td align="center"><i class="wi wi-strong-wind"></i></td>
<td align="center">
<i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[0]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[0]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[0]->ws.unit('strength').'
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[0]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[0]->wd_txt.'</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[1]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[1]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[1]->ws.unit('strength').'
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[1]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[1]->wd_txt.'</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[2]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[2]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[2]->ws.unit('strength').'
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[2]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[2]->wd_txt.'</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[3]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[3]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[3]->ws.unit('strength').'
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[3]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[3]->wd_txt.'</span></td>
</tr>
';

if ($dwd_alert_status!=2) {
  echo '
<!-- DWD Vorhersagetext -->
<tr>
<td valign="top">&nbsp;</td>
<td colspan="4"><span class="small">'.$dwd_forecast[$i].'</span></td>
</tr>
';
}

echo '
<tr>
<td>&nbsp;</td>
<td colspan="4"><hr /></td>
</tr>
';

echo '
<!-- Sonne -->
<tr>
<td align="center"><i class="wi wi-day-sunny"></i></td>
<td align="left"><span class="small">
Blaue Stunde:<br />'.strftime('%H:%M',intval($sun_data[$i]['morning_blue_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['morning_blue_hour_end'])).'<br />
Goldene Stunde:<br />'.strftime('%H:%M',intval($sun_data[$i]['morning_golden_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['morning_golden_hour_end'])).'
</span></td>
<td align="center"><i class="wi wi-sunrise"></i><br />'.strftime('%H:%M',intval($sun_data[$i]['sunrise'])).'<br /><span class="small">(Zenit: '.strftime('%H:%M',intval($sun_data[$i]['transit'])).')</span></td>
<td align="center"><i class="wi wi-sunset"></i><br />'.strftime('%H:%M',intval($sun_data[$i]['sunset'])).'</td>
<td align="left"><span class="small">
Goldene Stunde:<br />'.strftime('%H:%M',intval($sun_data[$i]['evening_golden_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['evening_golden_hour_end'])).'<br />
Blaue Stunde:<br />'.strftime('%H:%M',intval($sun_data[$i]['evening_blue_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['evening_blue_hour_end'])).'
</span></td>
</tr>

<!-- Mond -->
<tr>
<td align="center"><i class="wi wi-night-clear"></i></td>
';
if ($moon_data[$i]['moonrise'] > $moon_data[$i]['moonset']) {
	echo '<td align="center"><i class="wi wi-moonset"></i><br />'.strftime('%H:%M',intval($moon_data[$i]['moonset'])).'</td>
<td align="center"><i class="wi wi-moonrise"></i><br />'.strftime('%H:%M',intval($moon_data[$i]['moonrise'])).'</td>
';
} else {
	echo '<td align="center"><i class="wi wi-moonrise"></i><br />'.strftime('%H:%M',intval($moon_data[$i]['moonrise'])).'</td>
<td align="center"><i class="wi wi-moonset"></i><br />'.strftime('%H:%M',intval($moon_data[$i]['moonset'])).'</td>
';
}
echo '<td align="center"><i class="wi '.moonphase_icon($moon_data[$i]['phase']).'"></i><br /><span class="small">'.$moon_data[$i]['phase_name'].'<br />('.$moon_data[$i]['illuminated'].' sichtbar)</span></td>
<td align="left"><span class="small">Mondalter: '.$moon_data[$i]['age'].'<br />Mondphase: '.$moon_data[$i]['phase'].'&nbsp;%<br />Entfernung: '.$moon_data[$i]['distance'].'</span></td>
</tr>
';

if ($bsh_tides=='yes' && count($tide_text)>0) {
	echo '
<!-- Gezeiten -->
<tr align="center">
<td><i class="wi wi-flood"></i></td>
<td align="center"><i class="wi '.weather_icon($tide_text[$tide_date[$i]][0][2]).'"></i><br />'.$tide_text[$tide_date[$i]][0][1].'</td>
<td align="center"><i class="wi '.weather_icon($tide_text[$tide_date[$i]][1][2]).'"></i><br />'.$tide_text[$tide_date[$i]][1][1].'</td>
<td align="center"><i class="wi '.weather_icon($tide_text[$tide_date[$i]][2][2]).'"></i><br />'.$tide_text[$tide_date[$i]][2][1].'</td>
<td align="center"><i class="wi '.weather_icon($tide_text[$tide_date[$i]][3][2]).'"></i><br />'.$tide_text[$tide_date[$i]][3][1].'</td>
</tr>
';
}

echo '
<tr>
<td colspan="5"><hr /></td>
</tr>
';
}

echo '
<!-- Credits -->
<tr>
<td colspan="5" align="right"><span class="small"><b>Datenquellen: </b>
'.$wettercom_credit.' | Sonne/Mond: <a title="berechnet" href="#berechnete_werte">berechnet</a> | Wetterlage: <a rel="nofollow" target="_blank" title="Deutscher Wetterdienst" href="http://www.dwd.de/">Deutscher Wetterdienst</a>';
if ($bsh_tides=='yes') {
	echo ' | '.$bsh_credit;
}
echo '</span></td>
</tr>

</table>
';

/* ================================================= */

if ($weather_maps_text!='') {
	echo '
<a name="wetterkarten"></a>
<h2>Wetterkarten</h2>

'.$weather_maps_text.'
';
}

/* ================================================= */

if (isset($weather_station_amazon_tag) && $weather_station_amazon_tag!='') {
	$weather_station_amazon_tag = '&tag='.$weather_station_amazon_tag;
}
echo '
<a name="wetterstation"></a>
<h2>Wetterstation</h2>
'.$weather_station_text.'
<p>
Die private Wetterstation befindet sich in '.$netatmo_station_place_city.', Position: '.round($netatmo_station_place_latitude,2).'&deg; Nord '.round($netatmo_station_place_longitude,2).'&deg; Ost, Höhe: '.$netatmo_station_place_altitude.'.
</p>
<p>
Verwendet wird eine
<a rel="nofollow" href="http://www.amazon.de/gp/product/B0098MGWA8/ref=as_li_tl?ie=UTF8&camp=1638&creative=6742&creativeASIN=B0098MGWA8&linkCode=as2'.$weather_station_amazon_tag.'" target="_blank">Netatmo Wetterstation</a> mit
';
if ($netatmo_rain_module==true) {
	echo '<a rel="nofollow" href="http://www.amazon.de/gp/product/B016OHME1A/ref=as_li_tl?ie=UTF8&camp=1638&creative=6742&creativeASIN=B016OHME1A&linkCode=as2'.$weather_station_amazon_tag.'" target="_blank">Wind-</a> ';
}
echo 'und <a rel="nofollow" href="http://www.amazon.de/gp/product/B00J5OHDGG/ref=as_li_tl?ie=UTF8&camp=1638&creative=6742&creativeASIN=B00J5OHDGG&linkCode=as2'.$weather_station_amazon_tag.'" target="_blank">Regenmesser</a>.
Die Daten werden alle 10 Minuten aktualisiert. Die Darstellung erfolgt mi Hilfe von
<a rel="nofollow" href="https://github.com/larknafets/php-weather-de" target="_blank" title="GitHub: php-weather-de">PHP Scripten</a>.
</p>
<p>
Die Aufstellung der Meßgeräte entspricht <u>nicht</u> den Anforderungen des Deutschen Wetterdienstes. Die Werte sind entsprechend ungenau.
</p>

<p class="small">
<a href="javascript:weather_legend()">Legende anzeigen/ausblenden</a>
</p>

<a name="berechnete_werte"></a>
<div id="weather_legend" style="display:none;">
<span class="small">
Es werden folgende Daten von der Station erfasst:
<ul>
<li>Temperatur</li>
<li>Luftfeuchtigkeit</li>
<li>Luftdruck</li>
<li>Regenmenge</li>
<li>Windrichtung</li>
<li>Windgeschwindigkeit</li>
<li>Windböen</li>
</ul>
Folgende Daten werden berechnet:
<ul>';
if ($netatmo_wind_module==true) {
	echo '
<li>Windchill / gefühlte Temperatur</li>';
}
echo '
<li>Hitzeindex</li>
<li>Taupunkt</li>
<li>Luftdichte, Sättigungsdruck, Dampfdruck</li>
<li>Theta-E</li>
<li>Schneefallgrenze</li>
<li>Sonnenauf- und -untergang</li>
<li>Mondauf- und -untergang</li>
<li>Mondphase</li>
</ul>';
if ($netatmo_wind_module==true) {
	echo '
<b>Windchill</b><br />
Der Windchill ist ein Maß für die gefühlte Temperatur. Er berechnet sich aus der Windstärke und der Lufttemperatur. Durch die Verdunstung über die Haut kommt es zu einer Abkühlung. Dieser Effekt wird duch den Wind verstärkt. Es scheint kühler zu sein, als es das Thermometer anzeigt.
<br />Die Berechnung erfolgt unterhalb einer Temperatur von 10 &deg;C und einer Windgeschwindigkeit von mindestens 4,8 km/h.
<br /><br />';
}
echo '
<b>Hitzeindex</b><br />
Der Hitzeindex ist eine weitere gefühlte Temperatur, die sich aus der Lufttemperatur und der Luftfeuchte ergibt. Da die Verdunstung über die Haut bei hohen Feuchtewerten (Schwüle) langsamer geht, ist auch der Kühlungseffekt schwächer. Es kommt einem heißer vor als es tatsächlich ist (drückende Hitze).
<br />Die Berechnung erfolgt ab einer Temperatur von 26,7 &deg;C und einer Luftfeuchtigkeit von mindestens 40 %.
<br /><br />
<b>Taupunkt</b><br />
Der Taupunkt errechnet sich aus der aktuellen Lufttemperatur und Feuchte. Der Taupunkt gibt die Temperatur an, auf die man die Luft bei konstantem Wasserdampfgehalt abkühlen muß, damit die Luftfeuchtigkeit 100 % beträgt. Die Luft ist dann mit Wasserdampf gesättigt. Bei einer weiteren Abkühlung würde sich der Wasserdampf als Nebel, Tau oder Reif aus der Luft ausscheiden.
<br /><br />
<b>Sonne/Mond</b><br />
Goldene Stunde nennt man die Zeit direkt nach Sonnenaufgang und direkt vor Sonnenuntergang. Die Blaue Stunde findet hingegen vor Sonnenaufgang und nach Sonnenuntergang statt. Beide Begriffe beziehen sich auf die besondere Färbung des Himmels.<br />
AD: Astronomische Dämmerung (Beginn/Ende)<br />
ND: Nautische Dämmerung (Beginn/Ende)<br />
BD: Bügerliche Dämmerung (Beginn/Ende)<br />
</span>
</div>

<script type="text/javascript">
  function weather_legend() {
    if(document.getElementById("weather_legend").style.display == "none") {
      document.getElementById("weather_legend").style.display = "block";
    } else {
      document.getElementById("weather_legend").style.display = "none";
    }
  }
</script>


';

// Clean-up buffer files in cache
delete_old_buffer_files();



// --- FUNCTIONS ---

function get_file_buffer($request_url) {
  global $buffer_cache_time, $buffer_cache_dir;
  if(!isset($buffer_cache_time)) { $buffer_cache_time = 60*45; } // 45 minutes default, if not given
  if(!isset($buffer_cache_dir)) { $buffer_cache_dir = 'cache_buffer'; }
  if (class_exists('Buffer')) {
    $cache = new Buffer();
    $content = $cache->data($request_url,$buffer_cache_time,dirname(__FILE__).'/'.$buffer_cache_dir);
  } else {
    $stream_options = array('ssl'=>array('verify_peer'=>false,'verify_peer_name'=>false));
    $content = file_get_contents($request_url, false, stream_context_create($stream_options));
  }
  return $content;
}

function delete_old_buffer_files() {
	if(!isset($buffer_cache_dir)) { $buffer_cache_dir = 'cache_buffer'; }
	$files = glob(dirname(__FILE__).'/'.$buffer_cache_dir.'/'."*");
	$now = time();
	foreach ($files as $file) {
		if (is_file($file)) {
			if ($now - filemtime($file) >= 60*6*24*2) { // 2 days
				unlink($file);
			}
		}
	}
}

function unit($key) {
	$type_unit = array(
		'temp' => '&nbsp;&deg;C',
		'hum' => '&nbsp;%',
		'strength' => '&nbsp;km/h',
		'angle' => '&deg;',
		'rain' => '&nbsp;mm',
		'pressure' => '&nbsp;mbar',
		'altitude' => '&nbsp;m',
		'airdensity' => '&nbsp;kg/m&sup3;'
	);
	foreach($type_unit as $type => $unit) {
		if(preg_match('/'.$type.'/', $key)) {
			return $unit;
		}
	}
}

function wind_strength($the_wind_strength) {
	$wind_bft = array();
	if ($the_wind_strength>=0 and $the_wind_strength<=1) {
		$wind_bft[0]=0;
		$wind_bft[1]='Windstille, Flaute';
	} elseif ($the_wind_strength>=1 and $the_wind_strength<=5) {
		$wind_bft[0]=1;
		$wind_bft[1]='leiser Zug';
	} elseif ($the_wind_strength>=6 and $the_wind_strength<=11) {
		$wind_bft[0]=2;
		$wind_bft[1]='leichte Brise';
	} elseif ($the_wind_strength>=12 and $the_wind_strength<=19) {
		$wind_bft[0]=3;
		$wind_bft[1]='schwache Brise';
	} elseif ($the_wind_strength>=20 and $the_wind_strength<=28) {
		$wind_bft[0]=4;
		$wind_bft[1]='mäßige Brise';
	} elseif ($the_wind_strength>=29 and $the_wind_strength<=38) {
		$wind_bft[0]=5;
		$wind_bft[1]='frische Brise';
	} elseif ($the_wind_strength>=39 and $the_wind_strength<=49) {
		$wind_bft[0]=6;
		$wind_bft[1]='starker Wind';
	} elseif ($the_wind_strength>=50 and $the_wind_strength<=61) {
		$wind_bft[0]=7;
		$wind_bft[1]='steifer Wind';
	} elseif ($the_wind_strength>=62 and $the_wind_strength<=74) {
		$wind_bft[0]=8;
		$wind_bft[1]='stürmischer Wind';
	} elseif ($the_wind_strength>=75 and $the_wind_strength<=88) {
		$wind_bft[0]=9;
		$wind_bft[1]='Sturm';
	} elseif ($the_wind_strength>=89 and $the_wind_strength<=102) {
		$wind_bft[0]=10;
		$wind_bft[1]='schwerer Sturm';
	} elseif ($the_wind_strength>=103 and $the_wind_strength<=117) {
		$wind_bft[0]=11;
		$wind_bft[1]='orkanartiger Sturm';
	} elseif ($the_wind_strength>=117) {
		$wind_bft[0]=12;
		$wind_bft[1]='Orkan';
	} else {
		$wind_bft[0]=0;
		$wind_bft[1]='';
	}
	return $wind_bft;
}

function wind_direction($the_wind_direction) {
	if ($the_wind_direction<33.75) {
		$wd='N';
	} elseif ($the_wind_direction>=11.25 and $the_wind_direction<33.75) {
		$wd='NNO';
	} elseif ($the_wind_direction>=33.75 and $the_wind_direction<56.25) {
		$wd='NO';
	} elseif ($the_wind_direction>=56.25 and $the_wind_direction<78.75) {
		$wd='ONO';
	} elseif ($the_wind_direction>=78.75 and $the_wind_direction<101.25) {
		$wd='O';
	} elseif ($the_wind_direction>=101.25 and $the_wind_direction<123.75) {
		$wd='OSO';
	} elseif ($the_wind_direction>=123.75 and $the_wind_direction<146.25) {
		$wd='SO';
	} elseif ($the_wind_direction>=146.25 and $the_wind_direction<168.75) {
		$wd='SSO';
	} elseif ($the_wind_direction>=168.75 and $the_wind_direction<191.25) {
		$wd='S';
	} elseif ($the_wind_direction>=191.25 and $the_wind_direction<213.75) {
		$wd='SSW';
	} elseif ($the_wind_direction>=213.75 and $the_wind_direction<236.75) {
		$wd='SW';
	} elseif ($the_wind_direction>=236.75 and $the_wind_direction<258.75) {
		$wd='WSW';
	} elseif ($the_wind_direction>=258.75 and $the_wind_direction<281.25) {
		$wd='W';
	} elseif ($the_wind_direction>=281.25 and $the_wind_direction<303.75) {
		$wd='WNW';
	} elseif ($the_wind_direction>=303.75 and $the_wind_direction<326.25) {
		$wd='NW';
	} elseif ($the_wind_direction>=326.75 and $the_wind_direction<348.25) {
		$wd='NNW';
	} else if ($the_wind_direction>=348.75) {
		$wd='N';
	}
	return $wd;
}

function weather_icon($the_field) {
	$replace_from = array();
	$replace_to = array();
	// Netatmo weather station
	$replace_from[] = 'up'; // trend up
	$replace_to[] = 'wi-direction-up-right';
	$replace_from[] = 'stable'; // trend stable
	$replace_to[] = 'wi-direction-right';
	$replace_from[] = 'down'; // trend down
	$replace_to[] = 'wi-direction-down-right';
	// BSH
	$replace_from[] = 'H'; // high tide
	$replace_to[] = 'wi-direction-up';
	$replace_from[] = 'N'; // low tide
	$replace_to[] = 'wi-direction-down';
	// Wetter.com
	$replace_from[] = '999'; // keine Angabe
	$replace_to[] = 'wi-na';
	$replace_from[] = '96'; // starkes Gewitter
	$replace_to[] = 'wi-thunderstorm';
	$replace_from[] = '95'; // leichtes Gewitter
	$replace_to[] = 'wi-storm-showers';
	$replace_from[] = '90'; // Gewitter
	$replace_to[] = 'wi-thunderstorm';
	$replace_from[] = '86'; // mäßiger oder starker Schnee - Schauer
	$replace_to[] = 'wi-snow';
	$replace_from[] = '85'; // leichter Schnee - Schauer
	$replace_to[] = 'wi-snow';
	$replace_from[] = '84'; // starker Schnee / Regen - Schauer
	$replace_to[] = 'wi-snow';
	$replace_from[] = '83'; // leichter Schnee / Regen - Schauer
	$replace_to[] = 'wi-snow';
	$replace_from[] = '82'; // starker Regen - Schauer
	$replace_to[] = 'wi-showers';
	$replace_from[] = '81'; // Regen - Schauer
	$replace_to[] = 'wi-showers';
	$replace_from[] = '80'; // leichter Regen - Schauer
	$replace_to[] = 'wi-showers';
	$replace_from[] = '75'; // starker Schneefall
	$replace_to[] = 'wi-snow';
	$replace_from[] = '73'; // mäßiger Schneefall
	$replace_to[] = 'wi-snow';
	$replace_from[] = '71'; // leichter Schneefall
	$replace_to[] = 'wi-snow';
	$replace_from[] = '70'; // leichter Schneefall
	$replace_to[] = 'wi-snow';
	$replace_from[] = '69'; // starker Schnee-Regen
	$replace_to[] = 'wi-sleet';
	$replace_from[] = '68'; // leichter Schnee-Regen
	$replace_to[] = 'wi-sleet';
	$replace_from[] = '67'; // mäßiger oder starker Regen, gefrierend
	$replace_to[] = 'wi-rain';
	$replace_from[] = '66'; // leichter Regen, gefrierend
	$replace_to[] = 'wi-rain';
	$replace_from[] = '65'; // starker Regen
	$replace_to[] = 'wi-rain';
	$replace_from[] = '63'; // mäßiger Regen
	$replace_to[] = 'wi-rain';
	$replace_from[] = '61'; // leichter Regen
	$replace_to[] = 'wi-rain';
	$replace_from[] = '60'; // leichter Regen
	$replace_to[] = 'wi-rain';
	$replace_from[] = '57'; // starker Sprühregen, gefrierend
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '56'; // leichter Sprühregen, gefrierend
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '55'; // starker Sprühregen
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '53'; // Sprühregen
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '51'; // leichter Sprühregen
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '50'; // Sprühregen
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '49'; // Nebel mit Reifbildung
	$replace_to[] = 'wi-fog';
	$replace_from[] = '48'; // Nebel mit Reifbildung
	$replace_to[] = 'wi-fog';
	$replace_from[] = '45'; // Nebel
	$replace_to[] = 'wi-fog';
	$replace_from[] = '40'; // Nebel
	$replace_to[] = 'wi-fog';
	$replace_from[] = '30'; // bedeckt
	$replace_to[] = 'wi-cloudy';
	$replace_from[] = '20'; // wolkig
	$replace_to[] = 'wi-cloudy';
	$replace_from[] = '10'; // leicht bewölkt
	$replace_to[] = 'wi-cloud';
	$replace_from[] = '9'; // Gewitter
	$replace_to[] = 'wi-thunderstorm';
	$replace_from[] = '8'; // Schauer
	$replace_to[] = 'wi-rain';
	$replace_from[] = '7'; // Schnee
	$replace_to[] = 'wi-snow';
	$replace_from[] = '6'; // Regen
	$replace_to[] = 'wi-rain';
	$replace_from[] = '5'; // Sprühregen
	$replace_to[] = 'wi-sprinkle';
	$replace_from[] = '4'; // Nebel
	$replace_to[] = 'wi-fog';
	$replace_from[] = '3'; // bedeckt
	$replace_to[] = 'wi-cloudy';
	$replace_from[] = '2'; // wolkig
	$replace_to[] = 'wi-cloudy';
	$replace_from[] = '1'; // leicht bewölkt
	$replace_to[] = 'wi-cloud';
	$replace_from[] = '0'; // sonnig
	$replace_to[] = 'wi-day-sunny';
	// - replace -
  $total = count($replace_from);
  for ($i=0; $i<$total; $i++) {
    $the_field = preg_replace('/'.$replace_from[$i].'/',$replace_to[$i],$the_field);
  }
  return $the_field;
}


// ---- BEGIN ---- not used ----
function float_prefix($int) {
	$int = round($int,1);
	return ($int>0)?"+$int":"$int";
}

// Netatmo weather station

function wifi_status($the_value) {
	if ($the_value>=86) { $the_text = 'bad'; }
	else if ($the_value<86 && $the_value>=71) { $the_text = 'average'; }
	else if ($the_value<71 && $the_value>=56) { $the_text = 'average'; }
	else if ($the_value<=56 ) { $the_text = 'good'; }
	return $the_text;
}

function battery_vp_status($the_value, $the_module) {
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
// ---- END ---- not used ----


?>
