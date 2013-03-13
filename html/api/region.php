<?php 
define ('APP', 'FEED');

require_once('../includes/init.php');
require_once('../includes/feed_common.php');

$output_template = 'kml';
$data['debug'] = '';


$feed_params = $_REQUEST;
$parsed_params = parse_feed_params($feed_params);
$data['row'] = array();
$data['msg'] = '';

if ($parsed_params['region'])
{
    $sql = "select id, code, name, kml from region where";
    $r_id = intval($parsed_params['region']);
    if ($r_id)
        $sql .= " id = $r_id";
    else
        $sql .= " code = '{$parsed_params['region']}'";

    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    $row = pg_fetch_assoc($result);
    if ($row)
        $data['row'] = $row;
    else
        $data['msg'] = 'Error: Region ' . $parsed_params['region'] . ' does not exist';
}
else
{
    $data['msg'] = 'Error: No region identifer given.';
}

require ("templates/region.$output_template.template");
