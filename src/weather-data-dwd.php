<?php

$dwd_cache_download = dirname(__FILE__).'/'.'cache_dwd_download';
$dwd_cache_unzip = dirname(__FILE__).'/'.'cache_dwd_unzip';

// Standardvorhersagen (Forecast 0-2), Wetterlage (aktuell)
$dwd_http_dir_forecasts = 'https://opendata.dwd.de/weather/text_forecasts/html/';
$html_list = file_get_contents($dwd_http_dir_forecasts);
$html_list_count = preg_match_all('<a href=\x22(.+?)\x22>',$html_list,$http_matches);

$http_matches = str_replace('a href=','',$http_matches);
$http_matches = str_replace('\"','',$http_matches);
$http_file_list = $http_matches[1];

$actual_files = array(); // VHDL54_*_html - Wetterlage (VHDL54/aktuell)
$dwd_forecast = array();
$forecast_files_0 = array(); // VHDL50_*_html - Standardvorhersage (VHDL50/heute)
$forecast_files_1 = array(); // VHDL51_*_html - Standardvorhersage (VHDL51/morgen)
$forecast_files_2 = array(); // VHDL52_*_html - Standardvorhersage (VHDL52/übermorgen)

foreach($http_file_list AS $http_file) {
  $the_region = strpos($http_file,'VHDL50_'.$dwd_region.'_'.date('d'));
  if($the_region!==false) {
    $forecast_files_0[] = $http_file;
  }
  $the_region = strpos($http_file,'VHDL51_'.$dwd_region.'_'.date('d'));
  if($the_region!==false) {
    $forecast_files_1[] = $http_file;
  }
  $the_region = strpos($http_file,'VHDL52_'.$dwd_region.'_'.date('d'));
  if($the_region!==false) {
    $forecast_files_2[] = $http_file;
  }
  $the_region = strpos($http_file,'VHDL54_'.$dwd_region.'_');
  if($the_region!==false) {
    $actual_files[] = $http_file;
  }
}

// Standardvorhersagen (Forecast 0-2), Wetterlage (aktuell) - will be buffered
sort($actual_files);
$dwd_actual = get_file_buffer($dwd_http_dir_forecasts.$actual_files[count($actual_files)-1]);
$dwd_actual = utf8_encode(dwd_replace($dwd_actual));
sort($forecast_files_0);
$dwd_forecast_0 = get_file_buffer($dwd_http_dir_forecasts.$forecast_files_0[count($forecast_files_0)-1]);
$dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_0));
sort($forecast_files_1);
$dwd_forecast_1 = get_file_buffer($dwd_http_dir_forecasts.$forecast_files_1[count($forecast_files_1)-1]);
$dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_1));
sort($forecast_files_2);
$dwd_forecast_2 = get_file_buffer($dwd_http_dir_forecasts.$forecast_files_2[count($forecast_files_2)-1]);
$dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_2));

// Vor-/Warnungen
dwd_get_alerts();

$dwd_alert_info = '';
$alert_files = array(); // Vor-/Warnungen
$dwd_alert_data = array();
$alert_file_list = glob($dwd_cache_unzip.'/*');

// --- Check filtered file list for actual/relevant files
foreach($alert_file_list as $the_alert_file) {
  $alert_url = $the_alert_file;
	$tmp_alert_data = new SimpleXMLElement(file_get_contents($alert_url));
  $tmp_alert_expires = strtotime($tmp_alert_data->info->expires);
  foreach($tmp_alert_data->info->area as $area) {
    $tmp_alert_regioncode = $area->geocode[0]->value;
	  if ($tmp_alert_expires>=time() && $tmp_alert_regioncode==$dwd_city_code) {
		  $dwd_alert_data[] = $tmp_alert_data;
	  }
  }
}

if (count($dwd_alert_data)==0 && $dwd_alert_info=='') {
	$dwd_alert_info = 'Es liegen keine Wetterwarnungen für "'.$dwd_city_text.'" vor.';
  $dwd_alert_status = 0;
} else {
	$dwd_alert_info = 'Es liegen Wetterwarnungen für "'.$dwd_city_text.'" vor!';
  $dwd_alert_status = 1;
}

$headers = get_headers('https://opendata.dwd.de/',1);
if (!$headers) {
  $dwd_alert_info = 'Es konnte keine Verbindung zum Deutschen Wetterdienst aufgebaut werden!';
  $dwd_alert_status = 2;
}

// --- FUNCTIONS ---

function dwd_get_alerts() {
  global $dwd_cache_download, $dwd_cache_unzip;
  $dwd_http_file_alerts = 'https://opendata.dwd.de/weather/alerts/cap/COMMUNEUNION_DWD_STAT/Z_CAP_C_EDZW_LATEST_PVW_STATUS_PREMIUMDWD_COMMUNEUNION_DE.zip';
  $headers = get_headers($dwd_http_file_alerts,1);
  if ($headers) {
    $dwd_http_file_alerts_lastmodified = strtotime($headers['Last-Modified']);

    umask(0022); // 644
    if (!is_dir($dwd_cache_download)) { mkdir($dwd_cache_download); }
    if (!is_dir($dwd_cache_unzip)) { mkdir($dwd_cache_unzip); }

    $local_file = $dwd_cache_download.'/'.$dwd_http_file_alerts_lastmodified.'.zip';

    if (!file_exists($local_file)) {
	    $files = glob($dwd_cache_download.'/*');
	    foreach($files as $file) {
        if(is_file($file)) unlink($file);
	    }

      file_put_contents($local_file, fopen($dwd_http_file_alerts, 'r'));

      $files = glob($dwd_cache_unzip.'/*');
      foreach($files as $file){
        if(is_file($file)) unlink($file);
      }

      dwd_unzip($local_file, $dwd_cache_unzip.'/');
    }
  }
}

function dwd_unzip($zipFile, $unzipDir) {
  umask(0022); // 644
	$zip = new ZipArchive;
	$result = $zip->open($zipFile);
	if($result!==true){
    return false;
  } else {
    $zip->extractTo($unzipDir);
    $zip->close();
    return true;
  }
}

function dwd_replace($the_string) {
  // html/text
  $replace_from = array('<br \/>','<h2>','<\/h2>','<br><\/br>','<p>','<\/p>','<pre style="font-family: sans-serif">','<br \/><pre style="font-family: sans-serif">','<\/pre>','<strong>','<\/strong>');
  $replace_to = array('<br />','<b>','</b>','','','','','','','<b>','</b>');
  // DWD region -> location
  // Region: see ftp: gds/help/legend_warnings.xls / table: Warnlage_Vorabinfo
  $replace_from[] = 'DWHG'; // DWHG = Hamburg / Niedersachsen/Bremen
	$replace_to[] = 'HA'; // HA = Hamburg, Bremen, Niedersachsen
  $replace_from[] = 'DWHH'; // DWHH	= Hamburg /	Schleswig-Holstein/Hamburg
	$replace_to[] = 'HA'; // HA = Hamburg, Bremen, Niedersachsen
  $replace_from[] = 'DWPG'; // DWPG	= Potsdam /	Berlin/Brandenburg
	$replace_to[] = 'PD'; // PD = Mecklenburg-Vorpommern, Berlin, Brandenburg
  $replace_from[] = 'DWPH'; // DWPH	= Potsdam /	Mecklenburg-Vorpommern
	$replace_to[] = 'PD'; // PD = Mecklenburg-Vorpommern, Berlin, Brandenburg
  $replace_from[] = 'DWEH'; // DWEH	= Essen /	Nordrhein-Westfalen
	$replace_to[] = 'EM'; // EM = Nordrhein-Westfalen
  $replace_from[] = 'DWLG'; // DWLG	= Leipzig /	Sachsen
	$replace_to[] = 'LZ'; // LZ = Sachsen-Anhalt, Sachsen, Thüringen
  $replace_from[] = 'DWLH'; // DWLH	= Leipzig /	Sachsen-Anhalt
	$replace_to[] = 'LZ'; // LZ = Sachsen-Anhalt, Sachsen, Thüringen
  $replace_from[] = 'DWLI'; // DWLI	= Leipzig /	Thüringen
	$replace_to[] = 'LZ'; // LZ = Sachsen-Anhalt, Sachsen, Thüringen
  $replace_from[] = 'DWOH'; // DWOH	= Offenbach /	Hessen
	$replace_to[] = 'OF'; // OF = Hessen, Rheinland-Pfalz, Sarland
  $replace_from[] = 'DWOI'; // DWOI	= Offenbach /	Rheinland-Pfalz/Saarland
	$replace_to[] = 'OF'; // OF = Hessen, Rheinland-Pfalz, Sarland
  $replace_from[] = 'DWSG'; // DWSG	= Stuttgart /	Baden-Württemberg
	$replace_to[] = 'SU'; // SU = Baden-Württemberg
  $replace_from[] = 'DWMG'; // DWMG	= München /	Bayern
	$replace_to[] = 'MS'; // MS = Bayern
  // - replace -
  $total = count($replace_from);
  for ($i=0; $i<$total; $i++) {
    $the_string = preg_replace('/'.$replace_from[$i].'/',$replace_to[$i],$the_string);
  }
  return $the_string;
}

?>
