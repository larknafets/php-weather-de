<?php

$bsh_credit = '';

if (file_exists(dirname(__FILE__).'/'.$bsh_tides_file) || filesize(dirname(__FILE__).'/'.$bsh_tides_file)>0) {
  $bsh_data_location = '';
  $bsh_data_year = '';
  $bsh_data_mesz = 'no';
  $c_tide_date = array();
  $c_tide_time = array();
  $c_tide_type = array();
  $c_tide_text = array();
  $c_now = mktime(0,0,0,strftime('%m',time()),strftime('%d',time()),strftime('%Y',time()));
  $c_end = $c_now + (60*60*24*6); // Calculation of next 7 days (incl. today)

  $t_file_open = fopen(dirname(__FILE__).'/'.$bsh_tides_file,'r');

  while(!feof($t_file_open)) {
    $t_line = trim(fgets($t_file_open));
    if (strlen($t_line)>0) {
      $t_data = explode('#',$t_line);
      // Location
      if ($t_data[0]=='A04') {
        $bsh_data_location = trim($t_data[2]);
      }
      // Year
      if ($t_data[0]=='A06') {
        $bsh_data_year = trim($t_data[2]);
      }
      // Data in MESZ --- unused
      if ($t_data[0]=='B01' && strlen($trim($t_data[2]))>0) {
        $bsh_data_mesz = 'yes';
      }
      // Tides data
      if ($t_data[0]=='VB1') {
        $t_tmp_date = explode('.',$t_data[5]);
        $t_tmp_time = explode(':',$t_data[6]);
        $t_tmp_unixtime = mktime(trim($t_tmp_time[0]),trim($t_tmp_time[1]),0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2]));
        if ($bsh_data_mesz=='no') {
            $t_tmp_unixtime = $t_tmp_unixtime - 3600;
            $t_tmp_date = strftime('%Y', $t_tmp_unixtime).'-'.strftime('%m', $t_tmp_unixtime).'-'.strftime('%d', $t_tmp_unixtime);
            $t_tmp_offset_date = new DateTime($t_tmp_date);
            $t_tmp_offset = date_offset_get($t_tmp_offset_date);
            $t_tmp_unixtime = $t_tmp_unixtime + $t_tmp_offset;
        }
        $c_tide_time_unix[] = $t_tmp_unixtime;
        $c_tide_date[] = mktime(0,0,0,strftime('%m', $t_tmp_unixtime),strftime('%d', $t_tmp_unixtime),strftime('%Y', $t_tmp_unixtime));
        $c_tide_time[] = strftime('%H:%M', $t_tmp_unixtime);
    	  $c_tide_text[] = trim($t_data[3]).'W: '.strftime('%H:%M', $t_tmp_unixtime);

//        $c_tide_date[] = mktime(0,0,0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2]));
//        $c_tide_time[] = strftime('%H:%M', mktime(trim($t_tmp_time[0]),trim($t_tmp_time[1]),0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2])));
//    	  $c_tide_text[] = trim($t_data[3]).'W: '.strftime('%H:%M', mktime(trim($t_tmp_time[0]),trim($t_tmp_time[1]),0,trim($t_tmp_date[1]),trim($t_tmp_date[0]),trim($t_tmp_date[2])));

        $c_tide_type[] = trim($t_data[3]);
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
  		$tide_text[$c_tide_date[$i]][] = array($c_tide_text[$i],$c_tide_time[$i],$c_tide_type[$i],$c_tide_time_unix[$i]);
  	}
  }
  $bsh_credit = 'Gezeiten: <a rel="nofollow" target="_blank" title="Bundesamt für Seeschifffahrt und Hydrographie" href="http://www.bsh.de/">Bundesamt für Seeschifffahrt und Hydrographie</a>, '.$bsh_data_location.', '.$bsh_data_year;

} else {
  echo 'BSH Daten: Datei konnte nicht geladen werden.';
  $bsh_tides='no';
}

?>
