<?php 

define ('APP', 'EMBED');

require_once('../includes/init.php');
require_once('../includes/feed_common.php');

$output_template = 'default';
$feed_params = $_REQUEST;
$parsed_params = parse_feed_params($feed_params);

$data['height'] = '100%';
$data['width'] = '100%';
$data['map']['width'] = '75%';
$data['map']['height'] = '100%';
$data['side_bar']['width'] = '25%';
$data['side_bar']['height'] = '100%';
$data['side_bar']['visible'] = true;


if ($parsed_params['nosidebar'])
{
    $data['side_bar']['width'] = '0%';
    $data['map']['width'] = '100%';
    $data['side_bar']['visible'] = false;
}

$data['map_bounds'] = $parsed_params['bounds'];


if (array_key_exists('width', $_REQUEST)) 
{
	$w = intval ($_REQUEST['width']);
    $data['width'] = $w . 'px';
    if ($parsed_params['nosidebar'])
        $data['map']['width'] = $w  . 'px';
    else
    {
        $data['side_bar']['width'] = $w * 0.25 . 'px';
        $data['map']['width'] = $w * 0.75 . 'px';
    }
}
if (array_key_exists('height', $_REQUEST)) 
{
	$h = intval ($_REQUEST['height']);
	$data['height'] = $h . 'px';
    $data['map']['height'] = $data['height'];
    $data['side_bar']['height'] = $data['height'];
}


$data['notify_bar'] =  false;
if (array_key_exists('notify', $_REQUEST) && $_REQUEST['notify'] && $parsed_params['region']) 
{
    $data['notify_bar'] = true;
}

$data['region_name'] = $parsed_params['region_name'];


//  block lat/lng from passing through to the feed
unset ($feed_params['l']);
unset ($feed_params['BBOX']);
unset ($feed_params['width']);
unset ($feed_params['height']);
unset ($feed_params['notify']);

$data['feed_params'] = $feed_params;
$data['feed_url'] = 'http://alerts.skytruth.org/ge/alerts.kml?' . http_build_query($feed_params, '', '&');
$data['rss_url'] = 'http://alerts.skytruth.org/rss?' . http_build_query($feed_params, '', '&');

require ("templates/embed.$output_template.template");

?>


