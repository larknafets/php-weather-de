<?php

$FTPServer = $dwd_ftp_server;
$FTPUser = $dwd_ftp_user;
$FTPPasswort = $dwd_ftp_password;

// Vorwarnungen - wird nicht zwischengespeichert
$OrtCode = $dwd_city_code;
$OrtText = $dwd_city_text;
$DWDStandort = $dwd_location;

// Standardvorhersage (Code 50), Wetter-/Warnlage (Code 54)
$DWDStandort2 = $dwd_location_warn;

$meineWarnings = array();
$VerzeichnisWarnings = 'gds/specials/alerts/cap/'.$DWDStandort.'/';
$meineStandardvorhersagen_0 = array(); // VHDL50_*_html
$meineStandardvorhersagen_1 = array(); // VHDL51_*_html
$meineStandardvorhersagen_2 = array(); // VHDL52_*_html
$VerzeichnisStandardvorhersagen = 'gds/specials/forecasts/text/';
$meineWarnWetterLage = array(); // VHDL54_*_html
$VerzeichnisWarnWetterLage = 'gds/specials/forecasts/text/';

$warning_info = '';

$VerbindungsID = ftp_connect($FTPServer);
$LoginErgebnis = ftp_login($VerbindungsID, $FTPUser, $FTPPasswort);
ftp_pasv($VerbindungsID, true);

if($VerbindungsID && $LoginErgebnis) {
  // Vorwarnungen
  $Dateiliste = ftp_nlist($VerbindungsID, $VerzeichnisWarnings);
  foreach($Dateiliste AS $Datei) {
    $meineRegion = strpos($Datei,'_'.$OrtCode);
    if(!$meineRegion===FALSE ) {
      $meineWarnings[] = $Datei;
    }
  }
  // Standardvorhersagen
  $Dateiliste = ftp_nlist($VerbindungsID, $VerzeichnisStandardvorhersagen);
  foreach($Dateiliste AS $Datei) {
    $meineRegion = strpos($Datei,'VHDL50_'.$DWDStandort2.'_'.date('d'));
    if(!$meineRegion===FALSE ) {
      $meineStandardvorhersagen_0[] = $Datei;
    }
    $meineRegion = strpos($Datei,'VHDL51_'.$DWDStandort2.'_'.date('d'));
    if(!$meineRegion===FALSE ) {
      $meineStandardvorhersagen_1[] = $Datei;
    }
    $meineRegion = strpos($Datei,'VHDL52_'.$DWDStandort2.'_'.date('d'));
    if(!$meineRegion===FALSE ) {
      $meineStandardvorhersagen_2[] = $Datei;
    }
  }
  // Wetter-/Warnlage
  $Dateiliste = ftp_nlist($VerbindungsID, $VerzeichnisWarnWetterLage);
  foreach($Dateiliste AS $Datei) {
    $meineRegion = strpos($Datei,'VHDL54_'.$DWDStandort2.'_');
    if(!$meineRegion===FALSE ) {
      $meineWarnWetterLage[] = $Datei;
    }
  }
}
else {
	$warning_info = 'DWD Daten: Es konnte keine Verbindung mit dem FTP-Server hergestellt werden.';
  $dwd_forecast[] = $warning_info;
  $warning_status = 2;
}

$warning_data = array();
foreach($meineWarnings as $warningfile) {
	$warning_url = 'ftp://'.$FTPUser.':'.urlencode($FTPPasswort).'@'.$FTPServer.'/gds/'.$warningfile;
	$tmp_warning_data = new SimpleXMLElement(file_get_contents($warning_url));
	$tmp_warning_expires = strtotime($tmp_warning_data->info->expires);
	if ($tmp_warning_expires>=time()) {
		$warning_data[] = $tmp_warning_data;
	}
}
if (count($warning_data)==0 && $warning_info=='') {
	$warning_info = 'Es liegen keine Wetterwarnungen für "'.$OrtText.'" vor.';
  $warning_status = 0;
} else {
	$warning_info = 'Es liegen Wetterwarnungen für "'.$OrtText.'" vor!';
  $warning_status = 1;
}

sort($meineWarnWetterLage);
$dwd_actual = get_file_buffer('ftp://'.$FTPUser.':'.urlencode($FTPPasswort).'@'.$FTPServer.'/gds/'.$meineWarnWetterLage[count($meineWarnWetterLage)-1]);
$dwd_actual = utf8_encode(dwd_replace($dwd_actual));
sort($meineStandardvorhersagen_0);
$dwd_forecast_0 = get_file_buffer('ftp://'.$FTPUser.':'.urlencode($FTPPasswort).'@'.$FTPServer.'/gds/'.$meineStandardvorhersagen_0[count($meineStandardvorhersagen_0)-1]);
$dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_0));
sort($meineStandardvorhersagen_1);
$dwd_forecast_1 = get_file_buffer('ftp://'.$FTPUser.':'.urlencode($FTPPasswort).'@'.$FTPServer.'/gds/'.$meineStandardvorhersagen_1[count($meineStandardvorhersagen_1)-1]);
$dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_1));
sort($meineStandardvorhersagen_2);
$dwd_forecast_2 = get_file_buffer('ftp://'.$FTPUser.':'.urlencode($FTPPasswort).'@'.$FTPServer.'/gds/'.$meineStandardvorhersagen_2[count($meineStandardvorhersagen_2)-1]);
$dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_2));

// --- FUNCTIONS ---

function dwd_replace($the_string) {
    $replace_from = array('<br \/>','<h2>','<\/h2>','<br><\/br>','<p>','<\/p>','<pre style="font-family: sans-serif">','<br \/><pre style="font-family: sans-serif">','<\/pre>','<strong>','<\/strong>');
    $replace_to = array('<br />','<b>','</b>','','','','','','','<b>','</b>');
    $total = count($replace_from);
    for ($i=0; $i<$total; $i++) {
        $the_string = preg_replace('/'.$replace_from[$i].'/',$replace_to[$i],$the_string);
    }
    return $the_string;
}

?>
