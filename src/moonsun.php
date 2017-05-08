<?php

include(dirname(__FILE__).'/weather-data-netatmo.php');
$weather_station_longitude = str_replace(',','.',$netatmo_station_place_longitude);
$weather_station_latitude = str_replace(',','.',$netatmo_station_place_latitude);
include(dirname(__FILE__).'/weather-lib-moonsun.php');


// Start page output

echo '
<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Vorhersage">

<tr height="1">
<th width="15%"></th>
<th width="17%"></th>
<th width="17%"></th>
<th width="17%"></th>
<th width="17%"></th>
<th width="17%"></th>
</tr>

';

for ($i=0; $i<=30; $i++) {
	$today = '';
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
<td colspan="6"><a name="'.strftime('%Y-%m-%d',intval($sun_data[$i]['date'])).'"></a><br /><big><b>'.$today.strftime('%A, der %d.%m.%Y',intval($sun_data[$i]['date'])).'</b></big><br />&nbsp;</td>
</tr>
';

echo '
<tr>
<td rowspan="2">Sonne</td>
<td><span class="small">
AD: '.strftime('%H:%M',intval($sun_data[$i]['astronomical_twilight_begin'])).'<br />
ND: '.strftime('%H:%M',intval($sun_data[$i]['nautical_twilight_begin'])).'<br />
BD: '.strftime('%H:%M',intval($sun_data[$i]['civil_twilight_begin'])).'
</span></td>
<td align="center"><i class="wi wi-sunrise"></i><br />'.strftime('%H:%M',intval($sun_data[$i]['sunrise'])).'</span></td>
<td align="center"><span class="small">Zenit:</span><br />'.strftime('%H:%M',intval($sun_data[$i]['transit'])).'</td>
<td align="center"><i class="wi wi-sunset"></i><br />'.strftime('%H:%M',intval($sun_data[$i]['sunset'])).'</td>
<td><span class="small">
BD: '.strftime('%H:%M',intval($sun_data[$i]['civil_twilight_end'])).'<br />
ND: '.strftime('%H:%M',intval($sun_data[$i]['nautical_twilight_end'])).'<br />
AD: '.strftime('%H:%M',intval($sun_data[$i]['astronomical_twilight_end'])).'
</span></td>
</tr>
<tr>
<td colspan="2" align="left"><span class="small">
Blaue Stunde: '.strftime('%H:%M',intval($sun_data[$i]['morning_blue_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['morning_blue_hour_end'])).'<br />
Goldene Stunde: '.strftime('%H:%M',intval($sun_data[$i]['morning_golden_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['morning_golden_hour_end'])).'
</span></td>
<td>&nbsp;</td>
<td colspan="2" align="left"><span class="small">
Goldene Stunde: '.strftime('%H:%M',intval($sun_data[$i]['evening_golden_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['evening_golden_hour_end'])).'<br />
Blaue Stunde: '.strftime('%H:%M',intval($sun_data[$i]['evening_blue_hour_begin'])).' - '.strftime('%H:%M',intval($sun_data[$i]['evening_blue_hour_end'])).'
</span></td>
</tr>



<tr>
<td>Mond</td>
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
<td colspan="2" align="left"><span class="small">Mondalter: '.$moon_data[$i]['age'].'<br />Mondphase: '.$moon_data[$i]['phase'].'&nbsp;%<br />Entfernung: '.$moon_data[$i]['distance'].'</span></td>
</tr>

<tr>
<td colspan="6"><hr></td>
</tr>
';
}

echo '
<tr>
<td colspan="6"><div class="small" align="right">';
echo 'Sonne/Mond: berechnet</div></td>
</tr>

</table>
';

?>
