<?php

$tides_file = dirname(__FILE__).'/'.$bsh_tides_file;

if (file_exists($tides_file) || filesize($tides_file)>0) {

  $c_tide_date = array();
  $c_tide_time = array();
  $c_tide_type = array();
  $c_tide_text = array();

  $c_now = mktime(0,0,0,strftime('%m',time()),strftime('%d',time()),strftime('%Y',time()));
  $c_end = $c_now + (60*60*24*6);

  $t_file_open = fopen($tides_file,'r');
  while(!feof($t_file_open)) {
    $t_line = trim(fgets($t_file_open));
    if (strlen($t_line)>0) {
      $t_data = explode('#',$t_line);
      $t_tmp_date = explode('.',$t_data[5]);
      $t_tmp_time = explode(':',$t_data[6]);
      $c_tide_date[] = mktime(0,0,0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2]));
      $c_tide_time_unix[] = mktime(trim($t_tmp_time[0]),trim($t_tmp_time[1]),0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2]));
      $c_tide_type[] = trim($t_data[3]);
    	$c_tide_text[] = trim($t_data[3]).'W: '.strftime('%H:%M', mktime(trim($t_tmp_time[0]),trim($t_tmp_time[1]),0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2])));
      $c_tide_time[] = strftime('%H:%M', mktime(trim($t_tmp_time[0]),trim($t_tmp_time[1]),0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2])));
      if (trim($t_data[3])=='H') {
        $c_tide_icon[] = bsh_replace('H');
      } else if (trim($t_data[3])=='N') {
        $c_tide_icon[] = bsh_replace('N');
      } else {
        $c_tide_icon[] = bsh_replace('NA');
      }
    }
  }
  fclose($t_file_open);

  $tide_date = array();
  $tide_text = array();

  $tmp_date = 0;
  for($i=0; $i<=count($c_tide_date); $i++) {
  	if ($c_tide_date[$i]>=$c_now && $c_tide_date[$i]<=$c_end) {
  		if ($c_tide_date[$i]!=$tmp_date) {
  			$tide_date[] = $c_tide_date[$i];
  			$tmp_date = $c_tide_date[$i];
  		}
  		$tide_text[$c_tide_date[$i]][] = array($c_tide_text[$i],$c_tide_time[$i],$c_tide_type[$i],$c_tide_icon[$i]);
  	}
  }

}

// --- FUNCTIONS ---

function bsh_replace($the_field) {
	$replace_from = array('H'); // high
	$replace_to = array('wi-direction-up-right');
	$replace_from[] = 'N'; // low
	$replace_to[] = 'wi-direction-down-right';
	$replace_from[] = 'NA'; // unknown
	$replace_to[] = 'wi-na';
  $total = count($replace_from);
  for ($i=0; $i<$total; $i++) {
    $the_field = preg_replace('/'.$replace_from[$i].'/',$replace_to[$i],$the_field);
  }
  return $the_field;
}

?>
