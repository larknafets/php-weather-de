<?php

$forecast_url = 'http://api.wetter.com/forecast/weather/city/'.$wettercom_citycode.'/project/'.$wettercom_project.'/cs/'.md5($wettercom_project.$wettercom_api_key.$wettercom_citycode);
$forecast_file = get_file_buffer($forecast_url);
$sxe = simplexml_load_string($forecast_file);
$forecast_data = '';
if ($sxe === false) {
  echo 'Wetter.com Daten: Openweather API liefert fehlerhafte oder keine XML-Daten.';
  $wettercom_credit = '';
} else {
	$forecast_data = new SimpleXMLElement($forecast_file);
  $wettercom_credit = '<a rel="nofollow" target="_blank" title="'.$forecast_data->credit[0]->text.'" href="'.$forecast_data->credit[0]->link.'">'.$forecast_data->credit[0]->text.'</a>';
}

?>
