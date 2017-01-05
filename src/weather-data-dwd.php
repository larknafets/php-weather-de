<?php

$dwd_ftp_dir_alerts = 'gds/specials/alerts/cap/'.dwd_replace($dwd_region).'/';
$dwd_ftp_dir_forecasts = 'gds/specials/forecasts/text/';

$dwd_alert_info = '';
$actual_files = array(); // VHDL54_*_html - Wetterlage (VHDL54/aktuell)
$forecast_files_0 = array(); // VHDL50_*_html - Standardvorhersage (VHDL50/heute)
$forecast_files_1 = array(); // VHDL51_*_html - Standardvorhersage (VHDL51/morgen)
$forecast_files_2 = array(); // VHDL52_*_html - Standardvorhersage (VHDL52/übermorgen)
$dwd_forecast = array();
$alert_files = array(); // Vor-/Warnungen
$dwd_alert_data = array();

$dwd_connection_id = ftp_connect($dwd_ftp_server);
$dwd_connection_login_result = ftp_login($dwd_connection_id,$dwd_ftp_user,$dwd_ftp_password);
ftp_pasv($dwd_connection_id, true);

if($dwd_connection_id && $dwd_connection_login_result) {
  // --- Get file list from ftp and build up filtered list
  // Vor-/Warnungen
  $ftp_file_list = ftp_nlist($dwd_connection_id,'-t '.$dwd_ftp_dir_alerts);
  foreach($ftp_file_list AS $ftp_file) {
    $the_region = strpos($ftp_file,'_'.$dwd_city_code);
    if(!$the_region===FALSE) {
      $alert_files[] = $ftp_file;
    }
  }
  // Standardvorhersagen (Forecast 0-2), Wetterlage (aktuell)
  $ftp_file_list = ftp_nlist($dwd_connection_id, $dwd_ftp_dir_forecasts);
  foreach($ftp_file_list AS $ftp_file) {
    $the_region = strpos($ftp_file,'VHDL50_'.$dwd_region.'_'.date('d'));
    if(!$the_region===FALSE ) {
      $forecast_files_0[] = $ftp_file;
    }
    $the_region = strpos($ftp_file,'VHDL51_'.$dwd_region.'_'.date('d'));
    if(!$the_region===FALSE ) {
      $forecast_files_1[] = $ftp_file;
    }
    $the_region = strpos($ftp_file,'VHDL52_'.$dwd_region.'_'.date('d'));
    if(!$the_region===FALSE ) {
      $forecast_files_2[] = $ftp_file;
    }
    $the_region = strpos($ftp_file,'VHDL54_'.$dwd_region.'_');
    if(!$the_region===FALSE ) {
      $actual_files[] = $ftp_file;
    }
  }
  // --- Check filtered file list for actual/relevant files
  // Vor-/Warnungen - will NOT be buffered
  foreach($alert_files as $the_alert_file) {
  	$alert_url = 'ftp://'.$dwd_ftp_user.':'.urlencode($dwd_ftp_password).'@'.$dwd_ftp_server.'/gds/'.$the_alert_file;
  	$tmp_alert_data = new SimpleXMLElement(file_get_contents($alert_url));
  	$tmp_alert_expires = strtotime($tmp_alert_data->info->expires);
  	if ($tmp_alert_expires>=time()) {
  		$dwd_alert_data[] = $tmp_alert_data;
  	}
  }
  if (count($dwd_alert_data)==0 && $dwd_alert_info=='') {
  	$dwd_alert_info = 'Es liegen keine Wetterwarnungen für "'.$dwd_city_text.'" vor.';
    $dwd_alert_status = 0;
  } else {
  	$dwd_alert_info = 'Es liegen Wetterwarnungen für "'.$dwd_city_text.'" vor!';
    $dwd_alert_status = 1;
  }
  // Standardvorhersagen (Forecast 0-2), Wetterlage (aktuell) - will be buffered
  sort($actual_files);
  $dwd_actual = get_file_buffer('ftp://'.$dwd_ftp_user.':'.urlencode($dwd_ftp_password).'@'.$dwd_ftp_server.'/gds/'.$actual_files[count($actual_files)-1]);
  $dwd_actual = utf8_encode(dwd_replace($dwd_actual));
  sort($forecast_files_0);
  $dwd_forecast_0 = get_file_buffer('ftp://'.$dwd_ftp_user.':'.urlencode($dwd_ftp_password).'@'.$dwd_ftp_server.'/gds/'.$forecast_files_0[count($forecast_files_0)-1]);
  $dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_0));
  sort($forecast_files_1);
  $dwd_forecast_1 = get_file_buffer('ftp://'.$dwd_ftp_user.':'.urlencode($dwd_ftp_password).'@'.$dwd_ftp_server.'/gds/'.$forecast_files_1[count($forecast_files_1)-1]);
  $dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_1));
  sort($forecast_files_2);
  $dwd_forecast_2 = get_file_buffer('ftp://'.$dwd_ftp_user.':'.urlencode($dwd_ftp_password).'@'.$dwd_ftp_server.'/gds/'.$forecast_files_2[count($forecast_files_2)-1]);
  $dwd_forecast[] = utf8_encode(dwd_replace($dwd_forecast_2));
} else {
  // In case ftp access fails
	$dwd_alert_info = 'DWD Daten: Es konnte keine Verbindung mit dem FTP-Server hergestellt werden.';
  $dwd_forecast[] = $dwd_alert_info;
  $dwd_forecast[] = $dwd_alert_info;
  $dwd_forecast[] = $dwd_alert_info;
  $dwd_alert_status = 2;
}

// --- FUNCTIONS ---

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
