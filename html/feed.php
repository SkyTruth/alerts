<?php 
define ('APP', 'FEED');

require_once('../includes/init.php');

$output_template = 'rss';

if (array_key_exists('f', $_REQUEST))
{
	$f = $_REQUEST['f'];
	switch ($f)
	{
		case 'kml':
			$output_template = 'kml';
			break;
	}
}

$sql = "SELECT DATE_FORMAT(MAX(updated),'%Y-%m-%dT%h:%i:%sZ') updated FROM FeedEntry";
$result = mysql_query ($sql, $db);      
if (!$result) die(mysql_error());
$row = mysql_fetch_assoc($result);
$feed_updated = $row['updated'];
$from_sql  = "FeedEntry fe";
$where_sql = '1';

$lat = array ();
$lng = array ();

if (array_key_exists('BBOX', $_REQUEST)) 
{
	$l = $_REQUEST['BBOX'];
	$l = preg_split('/[:,]/', $l);
	if (sizeof($l) == 4)
	{
		$lat[] = floatval($l[1]);
		$lat[] = floatval($l[3]);
		$lng[] = floatval($l[0]);
		$lng[] = floatval($l[2]);
	}	
}
if (array_key_exists('l', $_REQUEST)) 
{
	$l = $_REQUEST['l'];
	$l = preg_split('/[:,]/', $l);
	if (sizeof($l) == 4)
	{
		$lat[] = floatval($l[0]);
		$lat[] = floatval($l[2]);
		$lng[] = floatval($l[1]);
		$lng[] = floatval($l[3]);
	}
}
if ($lat)
{
	$where_sql .= ' AND fe.lat>= ' . min($lat);  
	$where_sql .= ' AND fe.lat<= ' . max($lat);  
	$where_sql .= ' AND fe.lng>= ' . min($lng);  
	$where_sql .= ' AND fe.lng<= ' . max($lng);  
}

if (array_key_exists('id', $_REQUEST)) 
{
	$id = mysql_escape_string($_REQUEST['id']);
	$where_sql .= " AND id='$id'";
}
if (array_key_exists('tag', $_REQUEST))
{
	$tags = mysql_escape_string ($_REQUEST['tag']);
        $tags = preg_split('/[:,]/', $tags);
	foreach ($tags as $tag)
		if ($tag) $where_term [] = "t.tag = '$tag'";

	if ($where_term)
	{	
		$from_sql .= " JOIN  FeedEntryTag t ON fe.id = t.feed_entry_id ";
		$where_sql .= " AND (" . join(" OR ", $where_term). ")";
	}
}
if (array_key_exists('dates', $_REQUEST)) 
{
	$dates = mysql_escape_string ($_REQUEST['dates']);
        $dates = preg_split('/[:,]/', $dates);

	if (sizeof($dates) >= 1)
		$where_sql .= " AND updated >= '$dates[0]'";
	if (sizeof($dates) >= 2)
		$where_sql .= " AND updated <= ADDDATE('$dates[1]', INTERVAL 1 day)";
}
if (array_key_exists('d', $_REQUEST)) 
{
	$d = intval ($_REQUEST['d']);
	$where_sql .= " AND updated >= DATE_SUB(NOW(), INTERVAL '$d' day)";
}

//var_dump($l);
//var_dump($where_sql);
//die ();


$sql = "select DISTINCT fe.*, DATE_FORMAT(fe.updated,'%Y-%m-%dT%H:%i:%sZ') updated_formatted " .
	" from $from_sql WHERE $where_sql " . 
	" ORDER BY fe.updated DESC LIMIT 100";



$result = mysql_query ($sql, $db);
if (!$result) die(mysql_error());

$data['feed_updated'] = $feed_updated;
$data['items'] = array();
while ($row = mysql_fetch_assoc($result))
	$data['items'][] = $row;

require ("templates/feed.$output_template.template");
