<?php defined('APP') OR die('No direct access allowed.');

$CONFIG['DBHOST'] = 'localhost';
$CONFIG['DBNAME'] = 'NRC_Data';
$CONFIG['DBUSER'] = 'scraper';
$CONFIG['DBPASS'] = 'CHANGE ME';

$CONFIG['GEODBHOST'] = 'localhost';
$CONFIG['GEODBNAME'] = 'alerts';
$CONFIG['GEODBUSER'] = 'scraper';
$CONFIG['GEODBPASS'] = 'CHANGE ME';

$CONFIG['BASE_URL'] = 'http://alerts.skytruth.org/';
$CONFIG['FEED_BASE_URL'] = $CONFIG['BASE_URL'];
$CONFIG['SUBSCRIBE_URL'] = $CONFIG['BASE_URL'] . 'subscribe';
$CONFIG['REGION_KML_BASE_URL'] = $CONFIG['BASE_URL'] . 'region/';

$CONFIG['MAP_COOKIE_NAME'] = 'alerts_map'

$CONFIG['GA_ACCOUNT'] = 'UA-25593503-1';
$CONFIG['GA_DOMAIN'] = '.skytruth.org';


?>
