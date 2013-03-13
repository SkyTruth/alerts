<?php 
define ('APP', 'SUBSCRIBE');

require_once('../includes/init.php');
require_once('../includes/feed_common.php');
require_once('../includes/encpoly.php');

$output_template = 'default';
$action = 'default';
$data = array ();
$data['subscription'] = array ();
$data['title'] = "SkyTruth Alerts - Subscribe";
$data['use_maps'] = true;
$data['meta'] = array ();
$data['email'] = '';

$sid = array_key_exists('sid', $_REQUEST) ? mysql_escape_string ($_REQUEST['sid']) : '';
$email = array_key_exists('email', $_REQUEST) ? mysql_escape_string ($_REQUEST['email']) : '';
$rss_url = array_key_exists('rss_url', $_REQUEST) ? mysql_escape_string ($_REQUEST['rss_url']) : '';
$sub = array();
$my_subs = array ();

$parsed_params = parse_feed_params($_REQUEST);    

$unsubscribe_url = '/subscribe/unsubscribe';
$data['confirm_unsubscribe_url'] = make_url('/subscribe/confirm-unsubscribe');
$data['subscribe_url'] = make_url('/subscribe');
$data['subscribe_create_url'] = make_url('/subscribe/create');
$data['rss_url'] = make_url('/rss');

if ($sid)
{
    $sql = "SELECT * FROM RSSEmailSubscription WHERE id = '$sid'";
    $result = mysql_query ($sql, $db);      
    if (!$result) die(mysql_error());
    $sub = mysql_fetch_assoc($result);
    if ($sub)
        $sub['image_url'] = createStaticMapUrl ($sub['rss_url'], 200);
    else
    {
        $data['error'] = "ERROR: Unknonw subscription id: $sid";
        $action = 'error';
    }
}

// Determine what action to perform
if (array_key_exists('action', $_REQUEST))
    $action = $_REQUEST['action'];
else if ($sub)
    if ($sub['active'] && !$sub['confirmed'])
        $action = 'confirm';
     else   
        $action = 'unsubscribe';
else if ($email && $rss_url)
    $action = 'create';


function getDisplayFeedParams ($feed_params)
{
    foreach ($feed_params as $key=>$value)	
        if ($value)
            $display_params[$key] = $value;

	$display_params['channel'] = 'local';
	$display_params['brief'] = '1';

    return $display_params;

}

function createStaticMapUrl ($rss_url, $size)
{
// Model Url:
// http://maps.googleapis.com/maps/api/staticmap?size=512x512&maptype=roadmap&path=color:0xf33f00ff|weight:5|fillcolor:0xFF000033|34.3888,-79.3872|40.0697,-79.3872|40.0697,-71.6968|34.3888,-71.6968|34.3888,-79.3872&sensor=false
    global $geodb;
    
    $parsed = parse_feed_url ($rss_url);
    $bounds = $parsed['bounds'];
    $region = $parsed['region'];

    $url = "http://maps.googleapis.com/maps/api/staticmap?maptype=roadmap&sensor=false";
    $url .= "&size={$size}x{$size}";
    $url .= "&path=color:0xf33f00ff|weight:5|fillcolor:0xFF000033|";    
    if ($bounds)
    {
        $path[] = $bounds[0][0] . ',' . $bounds[0][1];
        $path[] = $bounds[1][0] . ',' . $bounds[0][1];
        $path[] = $bounds[1][0] . ',' . $bounds[1][1];
        $path[] = $bounds[0][0] . ',' . $bounds[1][1];
        $path[] = $bounds[0][0] . ',' . $bounds[0][1];
        $url .= join('|', $path);
    }
    else if ($region)
    {
        
        
        $sql = "select ST_AsText(simple_geom) as the_geom from region where code = '$region'";
        
        $result = pg_query ($geodb, $sql);      
        if (!$result) die(pg_last_error());
        $row = pg_fetch_assoc($result);

        $points = explode(',', substr ($row['the_geom'], 9, -2));
        foreach ($points as &$p)
        {
            $p = explode (' ',$p);
            $tmp = $p[1];
            $p[1] = $p[0];
            $p[0] = $tmp;
        }
        
        $poly = dpEncode($points);
        $url .= "enc:{$poly[0]}";
    }
    
    return $url;
}


switch ($action)
{
    case 'create':
        if (!$email || !$rss_url)
        {
            $data['error'] = 'ERROR: Missing subscribe information.  Need an email address and an RSS url.';
            $output_template = 'error'; 
            break;
        }    
    	// Check to see if a subscription already exists
        $sql = "SELECT * FROM RSSEmailSubscription WHERE email = '$email' AND rss_url = '$rss_url' AND active = 1";
        $result = mysql_query ($sql, $db);      
        if (!$result) die(mysql_error());
        $row = mysql_fetch_assoc($result);
        
        if (!$row)
        {
            $row = array();
            $row['email'] = $email;
            $row['rss_url'] = $rss_url;
            $row['id'] = uuid ();
            $row['confirmed'] = 0;
            $row['active'] = 1;
            
            $parsed = parse_feed_url ($rss_url);
            if ($parsed['bounds'])
            {
                $row['lat1'] = $parsed['bounds'][0][0];
                $row['lng1'] = $parsed['bounds'][0][1];
                $row['lat2'] = $parsed['bounds'][1][0];
                $row['lng2'] = $parsed['bounds'][1][1];
            }
                    
            // need to add a new subscription
            $sql = "INSERT INTO RSSEmailSubscription (" . join (',', array_keys($row)) . ") ";
            $sql .= " VALUES ('" . join ("','", array_values($row)) . "')";
            $result = mysql_query ($sql, $db);      
            if (!$result) die(mysql_error());
        }  
        $sub = $row;        
        $sub['image_url'] = createStaticMapUrl ($row['rss_url'], 200);
        $output_template = 'create'; 
        break;
    case 'confirm':
        if (!$sub)
        {
            $data['error'] = 'ERROR: Missing or unknown subscription id.';
            $output_template = 'error'; 
            break;
        }
        $sql = "UPDATE RSSEmailSubscription SET confirmed=1 WHERE id = '$sid'";
        $result = mysql_query ($sql, $db);      
        if (!$result) die(mysql_error());
        $output_template = 'update';
        break;
    case 'confirm-unsubscribe':
        if (!$sub)
        {
            $data['error'] = 'ERROR: Missing or unknown subscription id.';
            $output_template = 'error'; 
            break;
        }
        
        $sql = "UPDATE RSSEmailSubscription SET active =0 WHERE id = '$sid'";
        $result = mysql_query ($sql, $db);      
        if (!$result) die(mysql_error());
        $output_template = 'update';
    
        break;
    case 'unsubscribe':
        if (!$sub)
        {
            $data['error'] = 'ERROR: Missing or unknown subscription id.';
            $output_template = 'error'; 
        }
        else    
            $output_template = 'update';
    
        break;
    case 'update':
        if (!$sub)
        {
            $data['error'] = 'ERROR: Missing or unknown subscription id.';
            $output_template = 'error'; 
        }
        else
            $output_template = 'update';
        break;
    case 'error':
            $output_template = 'error'; 
        break;    
    default:
        $output_template = 'default';
        break;        
}

// Load other subscriptions with the same email address
if ($sub)
{
    $sql = "SELECT * FROM RSSEmailSubscription WHERE email = '{$sub['email']}'";
    $result = mysql_query ($sql, $db);      
    if (!$result) die(mysql_error());
    while ($row = mysql_fetch_assoc($result))
    {
        $row['image_url'] = createStaticMapUrl ($row['rss_url'], 100);
        $row['status'] = $row['confirmed'] ? ($row ['active'] ? 'Active' : 'Inactive') : 'Pending Confirmation';
        switch ($row['status'])
        {
            case 'Active':
                $row['action'] = 'unsubscribe';
                $row['action_url'] = make_url($unsubscribe_url, array('sid'=>$row['id']));
                break;
            default:
                $row['action'] = '';
                $row['action_url'] = '';
        }
        $my_subs[] = $row;
    }    
}

/*
if (array_key_exists('sid', $_REQUEST))
{
	$sid = mysql_escape_string ($_REQUEST['sid']);
	
    $sql = "SELECT * FROM RSSEmailSubscription WHERE id = '$sid' AND active = 1";
    $result = mysql_query ($sql, $db);      
    if (!$result) die(mysql_error());
    $row = mysql_fetch_assoc($result);
    if ($row)
    {
        $data['subscription'] = $row;
        $data['subscription']['status'] = 'active';
    
        $data['query'] = parse_feed_url ($row['rss_url']);
        
        $data['feed_params'] = getDisplayFeedParams($data['query']);

        if (array_key_exists ('unsubscribe', $_REQUEST))
        {
            $sql = "UPDATE RSSEmailSubscription SET active =0 WHERE id = '$sid'";
            $result = mysql_query ($sql, $db);      
            if (!$result) die(mysql_error());
    
            $data['subscription']['status'] = 'unsubscribed';
        }
        else if ($row['confirmed'] == 0)
        {
            // mark the subscription as confirmed
            $sql = "UPDATE RSSEmailSubscription SET confirmed=1 WHERE id = '$sid'";
            $result = mysql_query ($sql, $db);      
            if (!$result) die(mysql_error());
            
            $data['subscription']['confirmed'] = 1;
            $data['subscription']['status'] = 'confirmed';
        }
        $data['subscription']['image_url'] = createStaticMapUrl ($data['subscription']['rss_url'], 200);
        $output_template = 'view';
    }
    else
    {
        $data['error'] = 'ERROR: Unknown subscription id';
        $output_template = 'default';
    }
}
else if (array_key_exists('email', $_REQUEST) && array_key_exists('rss_url', $_REQUEST)) 
{
	$email = mysql_escape_string ($_REQUEST['email']);
	$rss_url = mysql_escape_string (urldecode($_REQUEST['rss_url']));
	
	// Check to see if a subscription already exists
    $sql = "SELECT * FROM RSSEmailSubscription WHERE email = '$email' AND rss_url = '$rss_url' AND active = 1";
    $result = mysql_query ($sql, $db);      
    if (!$result) die(mysql_error());
    $row = mysql_fetch_assoc($result);
    
    if (!$row)
    {
        $row = array();
        $row['email'] = $email;
        $row['rss_url'] = $rss_url;
        $row['id'] = uuid ();
        $row['confirmed'] = 0;
        $row['active'] = 1;
        
        $parsed = parse_feed_url ($rss_url);
        if ($parsed['bounds'])
        {
            $row['lat1'] = $parsed['bounds'][0][0];
            $row['lng1'] = $parsed['bounds'][0][1];
            $row['lat2'] = $parsed['bounds'][1][0];
            $row['lng2'] = $parsed['bounds'][1][1];
        }
                
        // need to add a new subscription
        $sql = "INSERT INTO RSSEmailSubscription (" . join (',', array_keys($row)) . ") ";
        $sql .= " VALUES ('" . join ("','", array_values($row)) . "')";
        $result = mysql_query ($sql, $db);      
        if (!$result) die(mysql_error());
    }

    $data['query'] = parse_feed_url ($row['rss_url']);
    $data['feed_params'] = getDisplayFeedParams($data['query']);

    $data['subscription'] = $row;
    $data['subscription']['status'] = $row['confirmed'] ? 'active' : 'created';
    $data['subscription']['image_url'] = createStaticMapUrl ($row['rss_url'], 200);
    $output_template = 'create';
}
else if (array_key_exists('l', $_REQUEST)) 
{
//	$l = $_REQUEST['l'];
//	$parsed = parseBounds ($l);

    $parsed_params = parse_feed_params($_REQUEST);

    $sub_params = $parsed_params;
    	
    if ($sub_params['channel'] == $sub_params['output_template'])	
    	$sub_params['channel'] = 'stae';
    $sub_params['output_template'] = 'rss'; 
    
    $data['feed_params'] = getDisplayFeedParams($parsed_params);
    
	$data['subscription']['rss_url'] = build_feed_url($sub_params);
//	$data['kml_sample_url'] = make_url('/kml', array('l'=>$parsed['bounds_url'], 'n'=>3, 'channel'=>'local'));

	$data['query'] = parse_feed_url (urldecode($data['subscription']['rss_url']));
	
    $output_template = 'create';
    $data['subscription']['status'] = 'new';
    $data['subscription']['image_url'] = createStaticMapUrl ($data['subscription']['rss_url'], 200);
	
}
*/

$data['action'] = $action;
$data['subscription'] = $sub;
$data['my_subs'] = $my_subs;
if ($my_subs)
    $data['email'] = $my_subs[0]['email'];
    
$data['feed_params'] = getDisplayFeedParams($parsed_params);
if (array_key_exists('bounds',$data['feed_params']))
    $data['map_bounds'] = $data['feed_params']['bounds'];

require ("templates/subscribe.$output_template.template");
?>