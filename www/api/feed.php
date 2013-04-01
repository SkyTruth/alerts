<?php 
define ('APP', 'FEED');

require_once('../includes/init.php');
require_once('../includes/feed_common.php');

$output_template = 'rss';
$data['debug'] = '';
$feed_params = $_REQUEST;
$parsed_params = parse_feed_params($feed_params);

$output_template = $parsed_params['output_template'];

// get the most recent published date to use as the updated time for the feed

$sql = "select to_char(max(published), 'YYYY-MM-DD\"T\"HH24:MI:SS.MSZ') as published from feedentry";

//OLD: $sql = "SELECT DATE_FORMAT(MAX(published),'%Y-%m-%dT%h:%i:%sZ') published FROM FeedEntry";

$result = pg_query ($geodb, $sql);      
if (!$result) die(pg_last_error());
$row = pg_fetch_assoc($result);
$data['feed_updated'] = $row['published'];

if ($parsed_params['region'])
{
    $r_id = intval($parsed_params['region']);
    if (!$r_id)
    {
        // look up region code
        $sql = "select id from region where code = '{$parsed_params['region']}'";
        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        $row = pg_fetch_assoc($result);
        if ($row)
            $parsed_params['region'] = $row['id'];
        else    
            $parsed_params['region'] = '';
    }
}

    
$sql = build_feed_query_sql ($parsed_params, $AUTH['FILTER_SQL']);

if ($parsed_params['debug'])
    $data['debug'] = $sql;

// echo $sql;
    
$result = pg_query ($geodb, $sql);
if (!$result) die(pg_last_error());


$data['items'] = array();
// $feed_entry_ids = array();
$data['tags'] = array();


while ($row = pg_fetch_assoc($result))
{    
    $row['report_url'] = make_url_with_entities ("/report/${row['id']}");
    if ($parsed_params['channel'] && $parsed_params['channel'] != 'local')
        $row['report_url'] .= "#c={$parsed_params['channel']}";
  
    $report_url_html = "<a href=\"${row['report_url']}\" target=\"_top\">View Full Report</a>";

    if ($parsed_params['brief'])
        $row['content'] = $report_url_html;
    else
        $row['content'] .= "<p>$report_url_html</p>";
        
    if ($parsed_params['channel'] != 'local')
    {    
        $analytics_path = '/syndication/' . $parsed_params['channel'] . '/' . $row['id'];
        $row['content'] .= '<img src="' . googleAnalyticsGetImageUrl ($analytics_path, '-') . '"/>';
    }

    $tags = substr($row['tags'], 1, -1);    // Strip off brackets '{}'
    if ($tags)
    {
        $tags = explode(',', $tags);
        $row['tags'] = $tags;
        foreach ($tags as $tag)
            $data['tags'][$row['id']][] = $tag;
        // TODO: Get rid of $data['tags'] and just use $data['items']['tags'] instead
    }
    else
        $row['tags'] = array();

//TODO: Load source names from mysql and do a lookup 
//  $row['source_name'] = $source_names[$row['source_id']];

	$data['items'][] = $row;
//	$feed_entry_ids[] = $row['id'];

}


$data['self_url'] = htmlentities(get_current_url());

/*
// Now get the tags that go with these feed entries
$feed_entry_ids_sql = "'" . join ("','", $feed_entry_ids) . "'";

$sql = "SELECT feed_entry_id, tag FROM FeedEntryTag WHERE feed_entry_id IN ($feed_entry_ids_sql)";
$result = mysql_query ($sql, $db);
if (!$result) die(mysql_error());
while ($row = mysql_fetch_assoc($result))
    $data['tags'][$row['feed_entry_id']][] = $row['tag'];
*/

require ("templates/feed.$output_template.template");
