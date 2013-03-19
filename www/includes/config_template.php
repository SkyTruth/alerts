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

$CONFIG['MAP_COOKIE_NAME'] = 'alerts_map';

$CONFIG['GA_ACCOUNT'] = 'UA-25593503-1';
$CONFIG['GA_DOMAIN'] = '.skytruth.org';

$CONFIG['AUTH_METHOD'] = '';    // php file to be loaded to perform user authentication
$CONFIG['WORDPRESS_PATH'] = '';    // Full path to local wordpress install
$CONFIG['WP_AUTH_FILTER'] = array (
    'anonymous' => array ('tags' => array('anonymous')),
    'current_user_can' => array (
        'access_s2member_level1' => array('tags' => array('level1'))
    )
);
$AUTH['filter_sql'] = '';   // sql filter to be applied to feed output based on user authentication

?>
