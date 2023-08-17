<?php

/*
Weather icons font from Eric Flowers needs to be loaded for output.
Make sure to laod the font before.
-- https://erikflowers.github.io/weather-icons/
*/


// To use local timezone, setlocale needs to be called.
// -- setlocale(LC_ALL,langLocale('de'));
// -- date_default_timezone_set('Europe/Berlin');


// Netatmo weather station
// -- An API account is needed.
// -- https://dev.netatmo.com/
$netatmo_client_id = 'client id';
$netatmo_client_secret = 'client secret';
$netatmo_username = 'your email'; // depricated
$netatmo_password = 'your password'; // deprecated
// Generate tokens first with you app: https://dev.netatmo.com/apps/
$netatmo_access_token = 'access|token';
$netatmo_refresh_token = 'refresh|token';

// Netatmo-API-PHP is used.
// -- https://github.com/Netatmo/Netatmo-API-PHP
$netatmo_nawsapiclient = '../netatmo/Clients/NAWSApiClient.php'; // Path to NAWSApiClient.php
$netatmo_ws_id = 'xx:xx:xx:xx:xx:xx'; // ID of your weather station main module


// Latitude and longitude, will be overwritten by Netatmo data. In case Netatmo is not reachable ...
$weather_station_latitude = '12.123456';
$weather_station_longitude = '1.123456';


// Include moon phase library from Samir Shah (solarissmoke):
// -- https://github.com/solarissmoke/php-moon-phase
$lib_moonphase = '../lib/MoonPhase.php';

// Include suncalc php library from Greg (gregseth):
// -- https://github.com/gregseth/suncalc-php
$lib_suncalc = '../lib/suncalc.php';


// DWD - Deutscher Wetter Dienst
// -- https://www.dwd.de/DE/leistungen/opendata/opendata.html
$dwd_warncellid = '803461003'; // WarnCell ID
$dwd_region = 'DWHG';
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


// Buffer
// -- https://github.com/leo/buffer
// Used for buffering DWD and Wetter.com data
$buffer_lib = '../buffer/buffer.php';
$buffer_cache_time = 60*45; // 45 min.
$buffer_cache_dir = '../../cache_buffer';


// BSH - Tides
// -- Tides are only allowed to be shown for 7 days for free.
// -- https://www.bsh.de/DE/DATEN/Vorhersagen/Gezeiten/gezeiten_node.html
$bsh_tides = 'no'; // yes/no
$bsh_tides_file = '../data_tides.txt';
//$bsh_tides_file = '../data_tides_'.date('Y').'.txt';

// Your weather station
// -- Description, weathermap data
$weather_station_amazon_tag = ''; // Amazon promotion ID ("xxxxx-21") for netatmo weather station links
// Short description if needed.
$weather_station_text='<p>
Die private Wetterstation befindet sich in 12345 Musterstad und ist seit Juli 2015 in Betrieb.
</p>';
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
