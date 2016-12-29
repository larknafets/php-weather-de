<?php

include(dirname(__FILE__).'/weather-moonsun.php');
include(dirname(__FILE__).'/weather-data-netatmo.php');
include(dirname(__FILE__).'/weather-data-wettercom.php');
include(dirname(__FILE__).'/weather-data-dwd.php');
if ($bsh_tides=='yes') {
	include(dirname(__FILE__).'/weather-data-bsh.php');
}

// Start page output

if ($warning_status==1) {
	echo '
<h2>Wetterwarnungen</h2>
<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Warnungen">
';

	if (count($warning_data)==0) {
		echo '<tr><td colspan="2">'.$warning_info.'</td></tr>';
	} else {
		foreach($warning_data as $warning) {
			echo '
<tr><td colspan="2" bgcolor="rgb('.str_replace(' ',',',$warning->info->eventCode[5]->value).')">&nbsp;</td></tr>
<tr><td colspan="2"><span class="big"><b>'.$warning->info->headline.'</b></span></td></tr>
<tr><td colspan="2"><b>';
			if ($warning->info->urgency=='Immediate') {
				echo 'Herausgegebene Warnung';
			} elseif ($warning->info->urgency=='Future') {
				echo 'Vorabinformation';
			} else {
				echo $warning->info->urgency;
			}
			echo '</b></td></tr>
<tr><td><span class="small">Ausgegeben:</span></td><td><span class="small">'.strftime('%d.%m.%Y %H.%M',strtotime($warning->sent)).'</span></td></tr>
<tr><td>Gebiet:</td><td>'.$warning->info->area->areaDesc.'</td></tr>
<tr><td>Beginn:</td><td>'.strftime('%d.%m.%Y %H.%M',strtotime($warning->info->onset)).'</td></tr>
<tr><td>Ende:</td><td>'.strftime('%d.%m.%Y %H.%M',strtotime($warning->info->expires)).'</td></tr>
<tr><td colspan="2"><b>'.$warning->info->description.'</b></td></tr>
<tr><td colspan="2">'.$warning->info->instruction.'</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
';
		}
	}
	echo '<tr><td colspan="2" align="right"><hr /><span class="small"><a rel="nofollow" target="_blank" title="Wettergefahren (DWD)" href="http://www.wettergefahren.de/">Wettergefahren (DWD)</a></span></td></tr>
</table><br /><br />
';
} else {
	echo '<p><div align="center">'.$warning_info.'</div></p>';
}

/* ================================================= */

echo '<p><span class="small"><a href="#aktuell" title="Aktuell">Aktuell</a>
| <a href="#vorhersage" title="Vorhersage">Vorhersage</a>
| <a href="#wetterkarten" title="Wetterkarten">Wetterkarten</a>
| <a href="#wetterstation" title="Wetterstation">Wetterstation</a>
</span></p>';

echo '
<a name="aktuell"></a>
<h2>Aktuell</h2>

<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Aktuell">

<tr>
<td colspan="3"><span class="big"><b>'.strftime('%A, der %d.%m.%Y um %H:%M',intval($netatmo_station_time)).' Uhr</b></span><br />&nbsp;</td>
</tr>

<tr>
<td>Temperatur</td>
<td align="center"><span class="big"><b>'.$netatmo_temperature.'&deg;C</b></span>
<br /><span class="small">'.float_prefix($netatmo_temperature_trend_value).' /3h</span></td>
<td align="center"><i class="wi '.netatmo_replace($netatmo_temperature_trend).'"></i></td>
</tr>
';

if ($netatmo_ws_wind_id!='') {
	echo '<tr>
<td>Windchill</td>
<td align="center"><span class="big"><b>'.round(calculate_windchill($netatmo_temperature, $netatmo_wind_strength),1).'&deg;C</b></span></td>
<td align="center">&nbsp;</td>
</tr>';
}

echo'
<tr>
<td>Hitzeindex</td>
<td align="center"><span class="big"><b>'.round(calculate_heatindex($netatmo_temperature, $netatmo_humidity),1).'&deg;C</b></span></td>
<td align="center">&nbsp;</td>
</tr>

<tr>
<td>Taupunkt</td>
<td align="center"><span class="big"><b>'.round(calculate_dewpoint($netatmo_temperature, $netatmo_humidity),1).'&deg;C</b></span></td>
<td align="center">&nbsp;</td>
</tr>

<tr>
<td>Luftdruck</td>
<td align="center"><span class="big"><b>'.$netatmo_pressure.' mbar</b></span>
<br /><span class="small">'.float_prefix($netatmo_pressure_trend_value).' /3h</span></td>
<td align="center"><i class="wi '.netatmo_replace($netatmo_pressure_trend).'"></i></td>
</tr>

<tr>
<td>Luftfeuchtigkeit</td>
<td align="center"><span class="big"><b>'.$netatmo_humidity.'%</b></span></td>
<td align="center">&nbsp;</td>
</tr>
';

if ($netatmo_ws_rain_id!='') {
	echo '
<tr>
<td>Niederschlag</td>
<td align="center"><span class="big"><b>'.round($netatmo_rain_1hrs,1).' mm</b></span> <span class="small">/1h</span>
<br /><span class="small">'.round($netatmo_rain_24hrs,1).' mm /24h</span></td>
<td align="center">&nbsp;</td>
</tr>';
}

if ($netatmo_ws_wind_id!='') {
	echo '
<tr>
<td>Wind</td>
<td align="center"><span class="big"><b>'.$netatmo_wind_strength.' km/h</b></span> <span class="small">Böen: '.$netatmo_gust_strength.' km/h</span>
<br /><span class="small">'.wind_strength($netatmo_wind_strength)[1].' aus '.wind_direction($netatmo_wind_angle).' ('.$netatmo_wind_angle.'&deg;)</span></td>
<td align="center">
<i class="wi wi-wind-beaufort-'.wind_strength($netatmo_wind_strength)[0].'"></i>
<i class="wi wi-wind from-'.$netatmo_wind_angle.'-deg"></i></td>
</tr>';
}

echo'
<tr><td colspan="3" align="right"><hr /><span class="small">
<a title="Private Wetterstation" href="#wetterstation">Private Wetterstation '.$netatmo_station_name.'</a>
</span></td></tr>

<tr><td colspan="3"><span class="small">&nbsp;</span></td></tr>
<tr><td colspan="3">Wetterlage</td></tr>
<tr><td colspan="3"><span class="small">'.$dwd_actual.'</span></td></tr>
<tr><td colspan="3" align="right"><hr /><span class="small"><a rel="nofollow" target="_blank" title="Deutscher Wetterdienst" href="http://www.dwd.de/">Deutscher Wetterdienst</a></span></td></tr>
</table>
';

/* ================================================= */

echo '
<a name="vorhersage"></a>
<h2>Vorhersage</h2>

<p><span class="small"><a href="#heute" title="Heute">Heute</a> | <a href="#morgen" title="Morgen">Morgen</a> | <a href="#uebermorgen" title="Übermorgen">übermorgen</a></span></p>

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
<tr>
<td colspan="5"><br />'.$today_anchor.'<span class="big"><b>'.$today.strftime('%A, der %d.%m.%Y',intval($forecast_data->forecast[0]->date[$i]->d)).'</b></span><br />&nbsp;</td>
</tr>

<tr>
<th width="20%">&nbsp;</th>
<th width="20%">Morgens</th>
<th width="20%">Mittags</th>
<th width="20%">Abends</th>
<th width="20%">Nachts</th>
</tr>

<tr>
<td>&nbsp;</td>
<td colspan="4"><hr /></td>
</tr>

<!-- Temperatur -->
<tr>
<td rowspan="2"><i class="wi wi-thermometer"></i></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[0]->tx.'&deg;C</b></span></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[1]->tx.'&deg;C</b></span></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[2]->tx.'&deg;C</b></span></td>
<td align="center"><span class="big"><b>'.$forecast_data->forecast[0]->date[$i]->time[3]->tx.'&deg;C</b></span></td>
</tr>
<tr>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[0]->tn.'&deg;C</span></td>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[1]->tn.'&deg;C</span></td>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[2]->tn.'&deg;C</span></td>
<td align="center"><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[3]->tn.'&deg;C</span></td>
</tr>

<!-- Wolken, Regen, Sonne -->
<tr>
<td>&nbsp;</td>
<td align="center"><i class="wi '.weathercom_replace(strval($forecast_data->forecast[0]->date[$i]->time[0]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[0]->w_txt.'</span></td>
<td align="center"><i class="wi '.weathercom_replace(strval($forecast_data->forecast[0]->date[$i]->time[1]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[1]->w_txt.'</span></td>
<td align="center"><i class="wi '.weathercom_replace(strval($forecast_data->forecast[0]->date[$i]->time[2]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[2]->w_txt.'</span></td>
<td align="center"><i class="wi '.weathercom_replace(strval($forecast_data->forecast[0]->date[$i]->time[3]->w)).'"></i><br /><span class="small">'.$forecast_data->forecast[0]->date[$i]->time[3]->w_txt.'</span></td>
</tr>

<!-- Regenwahrscheinlichkeit -->
<tr>
<td><i class="wi wi-umbrella"></i></td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[0]->pc.'%</td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[1]->pc.'%</td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[2]->pc.'%</td>
<td align="center">'.$forecast_data->forecast[0]->date[$i]->time[3]->pc.'%</td>
</tr>

<!-- Wind -->
<tr>
<td><i class="wi wi-strong-wind"></i></td>
<td align="center">
<i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[0]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[0]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[0]->ws.' km/h
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[0]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[0]->wd_txt.'</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[1]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[1]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[1]->ws.' km/h
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[1]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[1]->wd_txt.'</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[2]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[2]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[2]->ws.' km/h
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[2]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[2]->wd_txt.'</span></td>
<td align="center"><i class="wi wi-wind-beaufort-'.wind_strength($forecast_data->forecast[0]->date[$i]->time[3]->ws)[0].'"></i>
<i class="wi wi-wind from-'.$forecast_data->forecast[0]->date[$i]->time[3]->wd.'-deg"></i>
<br />'.$forecast_data->forecast[0]->date[$i]->time[3]->ws.' km/h
<br /><span class="small">'.wind_strength($forecast_data->forecast[0]->date[$i]->time[3]->ws)[1].'</span>
<br /><span class="small">aus '.$forecast_data->forecast[0]->date[$i]->time[3]->wd_txt.'</span></td>
</tr>

<!-- DWD Vorhersagetext -->
<tr>
<td valign="top">&nbsp;</td>
<td colspan="4"><span class="small">'.$dwd_forecast[$i].'</span></td>
</tr>

<tr>
<td>&nbsp;</td>
<td colspan="4"><hr /></td>
</tr>

<!-- Sonne -->
<tr>
<td><i class="wi wi-day-sunny"></i></td>
<td align="center"><i class="wi wi-sunrise"></i><br />'.strftime('%H:%M',intval($sunrise[$i])).'</td>
<td align="center"><i class="wi wi-sunset"></i><br />'.strftime('%H:%M',intval($sunset[$i])).'</td>
<td align="center">&nbsp;</td>
<td align="center">&nbsp;</td>
</tr>

<!-- Mond -->
<tr>
<td><i class="wi wi-night-clear"></i></td>
';
if ($moonrise[$i] > $moonset[$i]) {
echo '<td align="center"><i class="wi wi-moonset"></i><br />'.strftime('%H:%M',intval($moonset[$i])).'</td>
<td align="center"><i class="wi wi-moonrise"></i><br />'.strftime('%H:%M',intval($moonrise[$i])).'</td>
';
} else {
echo '<td align="center"><i class="wi wi-moonrise"></i><br />'.strftime('%H:%M',intval($moonrise[$i])).'</td>
<td align="center"><i class="wi wi-moonset"></i><br />'.strftime('%H:%M',intval($moonset[$i])).'</td>
';
}
echo '<td align="center"><i class="wi '.moonphase_icon($moonphase[$i]).'"></i><br /><span class="small">'.$moonphase_text[$i].$moonfull[$i].'</span></td>
<td align="center"><span class="small">Mondalter: '.$moonage[$i].'<br />Entfernung: '.$moondistance[$i].'<br />Sichtbar: '.$moonilluminated[$i].'</span></td>
</tr>
';

if ($bsh_tides=='yes' && count($tide_text)>0) {
	echo '<!-- Gezeiten -->
<tr>
<td><i class="wi wi-flood"></i></td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][0][3].'"></i><br />'.$tide_text[$tide_date[$i]][0][1].'</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][1][3].'"></i><br />'.$tide_text[$tide_date[$i]][1][1].'</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][2][3].'"></i><br />'.$tide_text[$tide_date[$i]][2][1].'</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][3][3].'"></i><br />'.$tide_text[$tide_date[$i]][3][1].'</td>
</tr>
';
}

echo '<tr>
<td colspan="5"><hr /></td>
</tr>
';
}

echo '
<!-- Credits -->
<tr>
<td colspan="5" align="right"><span class="small">
<a rel="nofollow" target="_blank" title="'.$forecast_data->credit[0]->text.'" href="'.$forecast_data->credit[0]->link.'">'.$forecast_data->credit[0]->text.'</a>
| Text: <a rel="nofollow" target="_blank" title="Deutscher Wetterdienst" href="http://www.dwd.de/">Deutscher Wetterdienst</a>
| Sonne/Mond: berechnet';
if ($bsh_tides=='yes') {
	echo '<br />Gezeiten: Die Veröffentlichung erfolgt mit Genehmigung des <a rel="nofollow" target="_blank" title="BSH" href="http://www.bsh.de/">BSH</a>';
}
echo'
</span></td>
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
Verwendet wird eine
<a rel="nofollow" href="http://www.amazon.de/gp/product/B0098MGWA8/ref=as_li_tl?ie=UTF8&camp=1638&creative=6742&creativeASIN=B0098MGWA8&linkCode=as2'.$weather_station_amazon_tag.'" target="_blank">Netatmo Wetterstation</a> mit
';
if ($netatmo_ws_rain_id!='') {
	echo '<a rel="nofollow" href="http://www.amazon.de/gp/product/B016OHME1A/ref=as_li_tl?ie=UTF8&camp=1638&creative=6742&creativeASIN=B016OHME1A&linkCode=as2'.$weather_station_amazon_tag.'" target="_blank">Wind-</a> ';
}
echo 'und <a rel="nofollow" href="http://www.amazon.de/gp/product/B00J5OHDGG/ref=as_li_tl?ie=UTF8&camp=1638&creative=6742&creativeASIN=B00J5OHDGG&linkCode=as2'.$weather_station_amazon_tag.'" target="_blank">Regenmesser</a>.
Die Daten werden alle 10 Minuten aktualisiert.
</p>
<p>
Die Aufstellung der Meßgeräte entspricht <u>nicht</u> den Anforderungen des Deutschen Wetterdienstes. Die Werte sind entsprechend ungenau.
</p>
<p>
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
</p>
<p>
Folgende Daten werden berechnet:
<ul>
';
if ($netatmo_ws_wind_id!='') {
	echo '<li>Windchill / gefühlte Temperatur</li>';
}
echo '
<li>Hitzeindex</li>
<li>Taupunkt</li>
<li>Sonnenauf- und -untergang</li>
<li>Mondauf- und -untergang</li>
<li>Mondphase</li>
</ul>
</p>

<a name="berechneteWerte"></a>
';
if ($netatmo_ws_wind_id!='') {
	echo '
<p>
<b>Windchill</b><br />
Der Windchill ist ein Maß für die gefühlte Temperatur. Er berechnet sich aus der Windstärke und der Lufttemperatur. Durch die Verdunstung über die Haut kommt es zu einer Abkühlung. Dieser Effekt wird duch den Wind verstärkt. Es scheint kühler zu sein, als es das Thermometer anzeigt.
<br />Die Berechnung erfolgt unterhalb einer Temperatur von 10&deg;C und einer Windgeschwindigkeit von mindestens 4,8 km/h.
</p>
';
}
echo '
<p>
<b>Hitzeindex</b><br />
Der Hitzeindex ist eine weitere gefühlte Temperatur, die sich aus der Lufttemperatur und der Luftfeuchte ergibt. Da die Verdunstung über die Haut bei hohen Feuchtewerten (Schwüle) langsamer geht, ist auch der Kühlungseffekt schwächer. Es kommt einem heißer vor als es tatsächlich ist (drückende Hitze).
<br />Die Berechnung erfolgt ab einer Temperatur von 26,7&deg;C und einer Luftfeuchtigkeit von mindestens 40%.
</p>
<p>
<b>Taupunkt</b><br />
Der Taupunkt errechnet sich aus der aktuellen Lufttemperatur und Feuchte. Der Taupunkt gibt die Temperatur an, auf die man die Luft bei konstantem Wasserdampfgehalt abkühlen muß, damit die Luftfeuchtigkeit 100% beträgt. Die Luft ist dann mit Wasserdampf gesättigt. Bei einer weiteren Abkühlung würde sich der Wasserdampf als Nebel, Tau oder Reif aus der Luft ausscheiden.
</p>
';

// --- FUNCTIONS ---

function wind_strength($the_wind_strength) {
	$wind_bft = array();
	if ($the_wind_strength>=0 and $the_wind_strength<=1) {
		$wind_bft[0]=0;
		$wind_bft[1]="Windstille, Flaute";
	} elseif ($the_wind_strength>=1 and $the_wind_strength<=5) {
		$wind_bft[0]=1;
		$wind_bft[1]="leiser Zug";
	} elseif ($the_wind_strength>=6 and $the_wind_strength<=11) {
		$wind_bft[0]=2;
		$wind_bft[1]="leichte Brise";
	} elseif ($the_wind_strength>=12 and $the_wind_strength<=19) {
		$wind_bft[0]=3;
		$wind_bft[1]="schwache Brise";
	} elseif ($the_wind_strength>=20 and $the_wind_strength<=28) {
		$wind_bft[0]=4;
		$wind_bft[1]="mäßige Brise";
	} elseif ($the_wind_strength>=29 and $the_wind_strength<=38) {
		$wind_bft[0]=5;
		$wind_bft[1]="frische Brise";
	} elseif ($the_wind_strength>=39 and $the_wind_strength<=49) {
		$wind_bft[0]=6;
		$wind_bft[1]="starker Wind";
	} elseif ($the_wind_strength>=50 and $the_wind_strength<=61) {
		$wind_bft[0]=7;
		$wind_bft[1]="steifer Wind";
	} elseif ($the_wind_strength>=62 and $the_wind_strength<=74) {
		$wind_bft[0]=8;
		$wind_bft[1]="stürmischer Wind";
	} elseif ($the_wind_strength>=75 and $the_wind_strength<=88) {
		$wind_bft[0]=9;
		$wind_bft[1]="Sturm";
	} elseif ($the_wind_strength>=89 and $the_wind_strength<=102) {
		$wind_bft[0]=10;
		$wind_bft[1]="schwerer Sturm";
	} elseif ($the_wind_strength>=103 and $the_wind_strength<=117) {
		$wind_bft[0]=11;
		$wind_bft[1]="orkanartiger Sturm";
	} elseif ($the_wind_strength>=117) {
		$wind_bft[0]=12;
		$wind_bft[1]="Orkan";
	} else {
		$wind_bft[0]=0;
		$wind_bft[1]="";
	}
	return $wind_bft;
}

function wind_direction($the_wind_direction) {
	if ($the_wind_direction>=348.75 and $the_wind_direction<=11.25) {
		$wd="N";
	} elseif ($the_wind_direction>=11.25 and $the_wind_direction<=33.75) {
		$wd="NNO";
	} elseif ($the_wind_direction>=33.75 and $the_wind_direction<=56.25) {
		$wd="NO";
	} elseif ($the_wind_direction>=56.25 and $the_wind_direction<=78.75) {
		$wd="ONO";
	} elseif ($the_wind_direction>=78.75 and $the_wind_direction<=101.25) {
		$wd="O";
	} elseif ($the_wind_direction>=101.25 and $the_wind_direction<=123.75) {
		$wd="OSO";
	} elseif ($the_wind_direction>=123.75 and $the_wind_direction<=146.25) {
		$wd="SO";
	} elseif ($the_wind_direction>=146.25 and $the_wind_direction<=168.75) {
		$wd="SSO";
	} elseif ($the_wind_direction>=168.75 and $the_wind_direction<=191.25) {
		$wd="S";
	} elseif ($the_wind_direction>=191.25 and $the_wind_direction<=213.75) {
		$wd="SSW";
	} elseif ($the_wind_direction>=213.75 and $the_wind_direction<=236.75) {
		$wd="SW";
	} elseif ($the_wind_direction>=236.75 and $the_wind_direction<=258.75) {
		$wd="WSW";
	} elseif ($the_wind_direction>=258.75 and $the_wind_direction<=281.25) {
		$wd="W";
	} elseif ($the_wind_direction>=281.25 and $the_wind_direction<=303.75) {
		$wd="WNW";
	} elseif ($the_wind_direction>=303.75 and $the_wind_direction<=326.25) {
		$wd="NW";
	} elseif ($the_wind_direction>=326.75 and $the_wind_direction<=348.25) {
		$wd="NNW";
	} else {
		$wd="";
	}
	return $wd;
}

?>
