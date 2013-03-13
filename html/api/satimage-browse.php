<?php 
define ('APP', 'SATIMAGE');

require_once('../includes/init.php');

$output_template = 'default';

$data['msg'] = '';

$data['class'] = strtoupper (pg_escape_string (array_get('class', $_REQUEST, 'OVERVIEW')));
$data['id'] = strtoupper (pg_escape_string (array_get('id', $_REQUEST, '')));

if ($data['class'] == 'AOI')
{
    if ($data['id'])
    {
        $output_template = 'aoi-item';

        $data['title'] = "SkyTruth AOI - {$data['id']}";
        $data['use_maps'] = false;
        $data['meta'] = array ();
        
        $sql = "select * from satimage_aoi where upper(name)='{$data['id']}' limit 1";
        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        
        $row = pg_fetch_assoc($result);
     	$data['item'] = $row;
     	
        $sql = "select * from satimage_published, satimage_aoi where upper(name)='{$data['id']}' limit 1";
        
        $sql =  "select s.acquisition_date, p.* from satimage_published p, satimage_aoi a, satimage s ";
        $sql .= " where ST_Intersects(a.the_geom, ST_Centroid(p.geo_extent)) ";
        $sql .= " and a.name = '{$data['item']['name']}'";
        $sql .= " and p.type = 'GEOTIFF'";
        $sql .= " and p.source_image = s.name";
        $sql .= " order by s.acquisition_date desc limit 50";
        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());

        $data['image_items'] = array();
        while ($row = pg_fetch_assoc($result))
        {    
            $row['url'] = make_url_with_entities("/satimage/ASAR-geotiff-{$row['name']}.kml");
    
        	$data['image_items'][] = $row;
        }
     	
    }
    else
    {
        $output_template = 'aoi-list';

        $data['title'] = "SkyTruth AOI List";
        $data['use_maps'] = false;
        $data['meta'] = array ();
        
        $data['aoilist_kml_url'] = make_url_with_entities("/satimage/AOI-footprint.kml");
        
        $sql = "select * from satimage_aoi order by name asc";
        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        $data['items'] = array();
        
        while ($row = pg_fetch_assoc($result))
        {    
            $row['url'] = make_url_with_entities("/satimage/browse/aoi/{$row['name']}.html");
    
        	$data['items'][] = $row;
        }
    }
}
else
{
    $output_template = 'default';
    $data['title'] = 'SkyTruth Satellite Image Processing System';
    $data['use_maps'] = false;
    $data['meta'] = array ();

    $data['geotiff_kml_url'] = make_url_with_entities("/satimage/ASAR-geotiffs.kml");
    $data['footprint_kml_url'] = make_url_with_entities("/satimage/ASAR-footprint.kml");
    $data['aoi_browse_url'] = make_url_with_entities("/satimage/browse/AOI.html");
    $data['aoi_kml_url'] = make_url_with_entities("/satimage/AOI-footprint.kml");
    
    $sql = "select s.*, ST_AsKML(p.geo_extent) as kml, p.url as geotiff_img_url, p.name as geotiff_img_name, ";
    $sql .= " to_char(acquisition_date, 'YYYY-MM-DD HH24:MI') as acquired, ";
    $sql .= " to_char(acquisition_date, 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as when ";
    $sql .= " from satimage s JOIN satimage_published p ON s.name = p.source_image ";
    $sql .= " where p.geo_extent is not null";
    $sql .= " and (p.type = 'GEOTIFF')";
    $sql .= " order by s.acquisition_date desc";

    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    
    $data['geotiff_items'] = array();
    
    while ($row = pg_fetch_assoc($result))
    {    
        $row['geotiff_kml_url'] = make_url_with_entities("/satimage/ASAR-geotiff-{$row['geotiff_img_name']}.kml");
        $nameparts = explode('-',$row['geotiff_img_name']);
        $row['geotiff_img_name'] = $row['source'] . " " . $nameparts[1];
    	$data['geotiff_items'][] = $row;
    }
    
    
    $sql = "select status, count(*), max(acquisition_date)  as done_through from satimage group by status";
    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    
    $data['status_items'] = array();
    $raw_satus = array ();
    while ($row = pg_fetch_assoc($result))
    {    
        $raw_status[trim($row['status'])] = $row['done_through'];
    }
    if (!array_key_exists('DOWNLOADED', $raw_status))
        $raw_status['DOWNLOADED'] = $raw_status['PROCESSED'];
        
    $data['status_items'][] = array('label'=>'New', 'value'=>$raw_status['NEW']);
    $data['status_items'][] = array('label'=>'Downloaded', 'value'=>$raw_status['DOWNLOADED']);
    $data['status_items'][] = array('label'=>'Processed', 'value'=>$raw_status['PROCESSED']);

    $sql = "select * from satimage_aoi order by name asc";
    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    
    $data['aoi_items'] = array();
    while ($row = pg_fetch_assoc($result))
    {    
        $row['url'] = make_url_with_entities("/satimage/browse/AOI/{$row['name']}.html");
        $data['aoi_items'][] = $row;
    }
    
}

require ("templates/satimage-browse.$output_template.template");


?>