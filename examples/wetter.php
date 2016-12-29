<?php

// <html>
// <head>
// write your html head
// ...
// include path to weather icons, see description in config
// </head>
// <body>

echo '<h1>Das Wetter in Musterstadt</h1>';

setlocale(LC_ALL,langLocale('de'));
include(dirname(__FILE__).'/config.inc.php');
include(dirname(__FILE__).'/src/weather.php');

// write your html footer
// </body>
// </html>

?>
