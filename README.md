# php-weather-de
Shows actual weather data from your Netatmo weather station combined with Wetter.com forecast and DWD information as well as BSH tides data.

## Audience
The software uses data from Wetter.com and Deutscher Wetterdienst (DWD) as well as optional tides data from Bundesamt für Seeschifffahrt und Hdydrographie (BSH); therefore it is for Netatmo weather stations located in Germany to add additional information to the current Netatmo data of your station.

## Prerequisites
You need a netatmo weather station and the following accounts:
- Netatmo dev account and app, see https://dev.netatmo.com/
- Deutscher Wetter Dienst GDS account, see http://www.dwd.de/DE/leistungen/gds/gds.html
- Wetter.com API account, see http://www.wetter.com

Optional:
- Tides data that has to be ordered from BSH, please read the terms and conditions, see http://www.bsh.de/de/Meeresdaten/Vorhersagen/Gezeiten/index.jsp

Following additional scripts/fonts are needed:
- Netatmo-API-PHP, see https://github.com/Netatmo/Netatmo-API-PHP
- Buffer, see https://github.com/leo/buffer
- Weather icons font from Eric Flowers, see https://erikflowers.github.io/weather-icons/
- Moon phase php class from Samir Shah, see https://github.com/solarissmoke/php-moon-phase

## Installation
Drop the "src" files to your webspace, wherever you want. Also download the additional resources, see prerequisites.
Please see "examples" how to implement in existing php/html files. Herein you also find a "config" file example to fill all your accounts information, paths and location data.

## Credits: calculations/libraries
The following calculations and libraries are used and marked in the code:
- Moon php class: original maths and code written by Keith Burnett <bodmas.org>, PHP port written by Matt "dxprog" Hackmann <dxprog.com>
- Wind chill calculation taken from: http://www.freemathhelp.com/wind-chill.html
- Heat index calculation taken from: https://www.easycalculation.com/weather/Heat-index.php
- Dew point calculation taken from: http://www.opto22.com/community/showthread.php?t=588
