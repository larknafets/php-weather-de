<?php

// <html>
// <head>
// write your html head
// ...
// include path to weather icons, see description in config
// </head>
// <body>

echo '<h1>Sonne / Mond</h1>';

setlocale(LC_ALL,langLocale('de'));
date_default_timezone_set('Europe/Berlin');
include(dirname(__FILE__).'/config.inc.php');
include(dirname(__FILE__).'/src/moonsun.php');

// write your html footer
// </body>
// </html>

?>
