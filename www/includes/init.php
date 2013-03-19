<?php defined('APP') OR die('No direct access allowed.');

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'util.php';
require_once 'config.php';

//$db = mysql_connect (
//	$CONFIG['DBHOST'],
//	$CONFIG['DBUSER'],
//	$CONFIG['DBPASS']
//	);

//mysql_select_db($CONFIG['DBNAME'], $db);

$geodb = pg_connect("host={$CONFIG['GEODBHOST']} dbname={$CONFIG['GEODBNAME']} user={$CONFIG['GEODBUSER']} password={$CONFIG['GEODBPASS']}");

// authenticate

if ($CONFIG['AUTH_METHOD'])
    require_once ($CONFIG['AUTH_METHOD']);


?>
