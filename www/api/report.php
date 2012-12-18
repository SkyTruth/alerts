<?php
define ('APP', 'FEED');

require_once('../includes/init.php');

$output_template = 'default';
$data = array ();
$data['title'] = "SkyTruth Alerts - Subscribe";
$data['use_maps'] = true;
$data['meta'] = array ();

$alert_id = mysql_escape_string($_REQUEST['id']);

$data['ga_actions']['_setAllowAnchor'] = 'true';
$data['ga_actions']['_setCampSourceKey'] = 'c';


$sql = "select * from feedentry where id='$alert_id'";
$result = pg_query ($geodb, $sql);      
if (!$result) die(pg_last_error());
$entry = pg_fetch_assoc($result);
$tags = array();
if ($entry)
{
    $t = substr($entry['tags'], 1, -1);    // Strip off brackets '{}'
    if ($t)
        $tags = explode(',', $t);

    $data['ga_actions']['_trackEvent'] = array ('AlertView', 'ViewPage', $alert_id, 10);
} 
else 
{
    $data['error'] = 'ERROR: Unknown entry id';
}

$incident_date = substr($entry['incident_datetime'], 0, 10);
$data['entry'] = $entry;
$data['tags'] = $tags;
$data['title'] = "Skytruth Alert: ${entry['title']} $incident_date";

$data['meta']['og:type'] = 'article';
$data['meta']['og:url'] = 'http://alerts.skytruth.org/report/' . $entry['id'];
$data['meta']['og:image'] = 'http://skytruth.org/images/logo.jpg';
$data['meta']['og:site_name'] = 'SkyTruth Alerts';
$data['meta']['og:email'] = 'alerts@skytruth.org';
$data['meta']['og:description'] = 'SkyTruth Alerts delivers real-time updates about environmental incidents in your back yard - or whatever part of the world you know and love. As soon as we know - you know';
$data['meta']['fb:admins'] = '1446720793';

$data['feed_params'] = array('brief'=>'1', 'channel'=>'local');
$data['self_url'] = get_current_url();

$bounds = array ($entry['lat'] + 0.05, $entry['lng'] + 0.06, $entry['lat'] - 0.05, $entry['lng'] - 0.06);
$data['subscribe_url'] = make_url('/subscribe', array('l'=>join(',', $bounds)));
$data['view_nearby_url'] = make_url('/', array('l'=>join(',', $bounds)));

require ("templates/report.$output_template.template");

?>