<?php 
define ('APP', 'GE');

require_once('../includes/init.php');

$output_template = 'default';

$feed_params = $_REQUEST;
$data['show_logo'] = true;

if (array_key_exists('f', $_REQUEST))
{
    unset ($feed_params['f']);
    
	$f = $_REQUEST['f'];
	switch ($f)
	{
		case 'alerts':
		    // no filter 
			break;
		case 'recent':
			$feed_params['d'] = 30;
			break;
        default:
            $feed_params['tag'] = $f;
            break;
	}
}

if (array_key_exists('nologo', $_REQUEST))
{
    unset ($feed_params['nologo']);
    
	$data['show_logo'] = ! $_REQUEST['nologo'];
}

if (array_key_exists('id', $_REQUEST))
{
    $output_template = 'single';

}

$data['param_str'] = http_build_query($feed_params, '', ' ');

$data['doc_name'] = "SkyTruth Alerts " . $data['param_str'];
$data['feed_url'] = make_url_with_entities ("/kml", $feed_params);

require ("templates/ge.$output_template.template");


?>
