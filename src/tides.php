<?php

include(dirname(__FILE__).'/weather-moonsun.php');
if ($bsh_tides=='yes') {
	include(dirname(__FILE__).'/weather-data-bsh.php');
}

// Start page output

echo '
<table cellpadding="'.$table_cellpadding.'" cellspacing="0" width="'.$table_width.'" summary="Vorhersage">

';

for ($i=0; $i<=6; $i++) {
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
<td colspan="5"><br /><big><b>'.$today.strftime('%A, der %d.%m.%Y',intval($tide_date[$i])).'</b></big><br />&nbsp;</td>
</tr>
';

if ($bsh_tides=='yes') {
	echo '
<tr>
<td>Gezeiten</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][0][3].'"></i><br />'.$tide_text[$tide_date[$i]][0][1].'</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][1][3].'"></i><br />'.$tide_text[$tide_date[$i]][1][1].'</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][2][3].'"></i><br />'.$tide_text[$tide_date[$i]][2][1].'</td>
<td align="center"><i class="wi '.$tide_text[$tide_date[$i]][3][3].'"></i><br />'.$tide_text[$tide_date[$i]][3][1].'</td>
</tr>
';
}

echo '
<tr>
<td>Sonne</td>
<td align="center"><i class="wi wi-sunrise"></i><br />'.strftime('%H:%M',intval($sunrise[$i])).'</td>
<td align="center"><i class="wi wi-sunset"></i><br />'.strftime('%H:%M',intval($sunset[$i])).'</td>
<td align="center">&nbsp;</td>
<td align="center">&nbsp;</td>
</tr>

<tr>
<td>Mond</td>
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
echo '<td align="center"><i class="wi '.moonphase_icon($moonphase[$i]).'"></i><br /><small>'.$moonphase_text[$i].$moonfull[$i].'</small></td>
<td align="center">&nbsp;</td>
</tr>

<tr>
<td colspan="5"><hr></td>
</tr>
';
}

echo '
<tr>
<td colspan="5"><div class="foot" align="right">
Gezeiten: Die Veröffentlichung erfolgt mit Genehmigung des <a class="foot" rel="nofollow" target="_blank" title="BSH" href="http://www.bsh.de/">BSH</a>
| Sonne/Mond: berechnet
</div></td>
</tr>

</table>
';

?>
