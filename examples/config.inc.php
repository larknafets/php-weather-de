<?php

/*
Weather icons font from Eric Flowers needs to be loaded for output.
Make sure to laod the font before.
-- https://erikflowers.github.io/weather-icons/
*/

// To use local timezone, setlocale needs to be called.
// -- setlocale(LC_ALL,langLocale('de'));

// Netatmo Weather Station
// -- An API account is needed.
// -- https://dev.netatmo.com/
$netatmo_client_id = 'client id';
$netatmo_client_secret = 'client secret';
$netatmo_username = 'your email';
$netatmo_password = 'your password';
// Netatmo-API-PHP is used.
// -- https://github.com/Netatmo/Netatmo-API-PHP
$netatmo_nawsapiclient = '../netatmo/Clients/NAWSApiClient.php'; // Path to NAWSApiClient.php
$netatmo_ws_id = 'xx:xx:xx:xx:xx:xx'; // ID of your weather station basis
$netatmo_ws_out_id = 'xx:xx:xx:xx:xx:xx'; // ID of your wind module
$netatmo_ws_rain_id = 'xx:xx:xx:xx:xx:xx'; // ID of your rain gauge; leave empty if none
$netatmo_ws_wind_id = 'xx:xx:xx:xx:xx:xx'; // ID of your wind gauge; leave empty if none


// DWD - Deutscher Wetter Dienst
// -- An free GDS account is needed to access dwd ftp server.
// -- http://www.dwd.de/DE/leistungen/gds/gds.html
$dwd_ftp_server = 'ftp-outgoingxxx.dwd.de';
$dwd_ftp_user = 'user';
$dwd_ftp_password = 'password';
$dwd_location = 'HA';
// EM = Nordrhein-Westfalen
// HA = Hamburg, Bremen, Niedersachsen
// LZ = Sachsen-Anhalt, Sachsen, Thüringen
// MS = Bayern
// OF = Hessen, Rheinland-Pfalz, Sarland
// PD = Mecklenburg-Vorpommern, Berlin, Brandenburg
// SU = Baden-Württemberg
$dwd_city_code = 'BRK';
$dwd_city_text = 'Kreis Wesermarsch - Küste';
// See gds/help/legend_warnings.pdf / column: DWD-Kennung
$dwd_location_warn = 'DWHG';
// DWHG = Hamburg / Niedersachsen/Bremen
// DWHH	= Hamburg /	Schleswig-Holstein/Hamburg
// DWPG	= Potsdam /	Berlin/Brandenburg
// DWPH	= Potsdam /	Mecklenburg-Vorpommern
// DWEH	= Essen /	Nordrhein-Westfalen
// DWLG	= Leipzig /	Sachsen
// DWLH	= Leipzig /	Sachsen-Anhalt
// DWLI	= Leipzig /	Thüringen
// DWOH	= Offenbach /	Hessen
// DWOI	= Offenbach /	Rheinland-Pfalz/Saarland
// DWSG	= Stuttgart /	Baden-Württemberg
// DWMG	= München /	Bayern
// See: gds/help/legend_warnings.xls / table: Warnlage_Vorabinfo

// Use buffer library for DWD data
// -- https://github.com/leo/buffer
$buffer_lib = '../buffer.php';
$buffer_cache_time = 60*45; // 45 min.
$buffer_cache_dir = '/../../cache_buffer';


// Wetter.com
// -- An API account is needed.
$wettercom_project = 'project';
$wettercom_api_key = 'api key';
$wettercom_citycode = 'city code';
// Get citycode:
//$search_plz = '12345';
//$search_checksum = md5($wettercom_project.$wettercom_api_key.$search_plz);
//$search_url = 'http://api.wetter.com/location/plz/search/'.$search_plz.'/project/'.$wettercom_project.'/cs/'.$search_checksum;
//die($search_url);


// BSH - tides
// -- Tides are only allowed to be shown for 7 days for free.
// -- http://www.bsh.de/de/Meeresdaten/Vorhersagen/Gezeiten/index.jsp
// -- You need only to keep the tides data. So skip the first lines of the original file to not run into errors.
$bsh_tides = 'no'; // yes/no
$bsh_tides_file = '../data_tides.txt';


// Your Weather Station
// -- Location data, description, weathermap data
$weather_station_latitude = 53.777777;
$weather_station_longitude = 8.222222;
$weather_station_zenith = 90+(50/60);
$weather_station_amazon_tag = ''; // Used for netatmo weather station links, like "xxxxx-21"
// Short description if needed.
$weather_station_text='<p>
Die private Wetterstation befindet sich in 12345 Musterstad und ist seit Juli 2015 in Betrieb.
</p>';
$weather_maps_images_width = 500;
// Optional to show maps from i.e. DWD; leave empty for none.
$weather_maps_text='<p>
<a href="https://www.meteopool.org/de" title="Quelle des Niederschlagsradar: meteopool.org"><img src="https://www.meteopool.org/export/homepagewetter.php?content=wetterradar_270x338" title="Niederschlagsradar(Wetterradar) für Deutschland" alt="Niederschlagsradar(Wetterradar) für Deutschland" /></a>
<a href="https://www.meteopool.org/de" title="Quelle der Blitzkarte: meteopool.org"><img src="https://www.meteopool.org/export/homepagewetter.php?content=blitzkarte_270x338" title="Blitzkarte für Deutschland, letzte 2 Stunden" alt="Blitzkarte für Deutschland, letzte 2 Stunden" /></a>
<br /><span class="foot">&nbsp;Quelle: <a href="https://www.meteopool.org/de/" target="_blank" title="MeteoPool.org" class="foot">MeteoPool.org</a></span>
</p>';


// Output table width and cellpadding
$table_width = '80%';
$table_cellpadding = 5;

?>
