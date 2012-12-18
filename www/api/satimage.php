<?php 
define ('APP', 'SATIMAGE');

require_once('../includes/init.php');

$output_template = 'default';

$data['msg'] = '';

$data['source'] = strtoupper (pg_escape_string (array_get('p1', $_REQUEST, 'ASAR')));
$data['type'] = strtoupper (pg_escape_string (array_get('p2', $_REQUEST, 'FOOTPRINT')));

if ($data['source'] == 'AOI')
{
    $output_template = 'aoikml';
    $data['doc_name'] = "SkyTruth: Monitored Areas of Interest";

    $sql = "select *, ST_AsKML(the_geom) as kml from satimage_aoi";
    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    
    $data['items'] = array();
    
    while ($row = pg_fetch_assoc($result))
    {    
        $row['url'] = make_url_with_entities ("/satimage/browse/AOI/{$row['name']}.html");
    	$data['items'][] = $row;
    }
    
}
elseif ($data['source'] == 'LIST')
{
    $output_template = 'list';
    $data['title'] = 'ASAR GeoTiffs';
    $data['use_maps'] = false;
    $data['meta'] = array ();
    
    $sql =  "select s.*, p.url as geotiff_img_url, p.name as geotiff_img_name, ";
    $sql .= " ST_AsKML(get_shifted_geom(p.geo_extent)) as kml, ";
    $sql .= " to_char(acquisition_date, 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as when from satimage s";
    $sql .= " JOIN satimage_published p ON s.name = p.source_image ";
    $sql .= " where p.geo_extent is not null";
    $sql .= " and (p.type = 'GEOTIFF')";
    $sql .= " order by s.acquisition_date desc";

    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    
    $data['geotiff_items'] = array();
    
    while ($row = pg_fetch_assoc($result))
    {    
        $row['geotiff_kml_url'] = make_url_with_entities("/satimage/ASAR-geotiff-{$row['geotiff_img_name']}.kml");

    	$data['geotiff_items'][] = $row;
    }
    
    $data['geotiff_kml_url'] = make_url_with_entities("/satimage/ASAR-geotiffs.kml");
    $data['footprint_kml_url'] = make_url_with_entities("/satimage/ASAR-footprint.kml");
    
    $sql = "select status, count(*), max(acquisition_date)  as done_through from satimage group by status";
    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());
    
    $data['status_items'] = array();
    $raw_satus = array ();
    while ($row = pg_fetch_assoc($result))
    {    
        $raw_status[trim($row['status'])] = $row['done_through'];
    }
    $data['status_items'][] = array('label'=>'New', 'value'=>$raw_status['NEW']);
    $data['status_items'][] = array('label'=>'Downloaded', 'value'=>$raw_status['DOWNLOADED']);
    $data['status_items'][] = array('label'=>'Processed', 'value'=>$raw_status['PROCESSED']);
}
elseif ($data['type'] == 'GEOTIFFS')
{
        $output_template = 'items';
        $data['preview_items'] = array();
        $data['doc_name'] = "SkyTruth: Processed ASAR GeoTiffs";

        $sql = "select s.*, p.url as geotiff_img_url, p.name as geotiff_img_name, ";
        $sql .= " ST_AsKML(get_shifted_geom(p.geo_extent)) as kml, ";
        $sql .= " to_char(acquisition_date, 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as when from satimage s";
        $sql .= " JOIN satimage_published p ON s.name = p.source_image ";
        $sql .= " where p.geo_extent is not null";
        $sql .= " and (p.type = 'GEOTIFF')";
        $sql .= " order by s.acquisition_date desc limit 100";

        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        
        $data['geotiff_items'] = array();
        
        while ($row = pg_fetch_assoc($result))
        {    
            $row['geotiff_kml_url'] = make_url_with_entities("/satimage/{$data['source']}-geotiff-{$row['geotiff_img_name']}.kml");
    
        	$data['geotiff_items'][] = $row;
        }
        
}
elseif ($data['type'] == 'FOOTPRINT')
{

    $data['year'] = intval (array_get('p3', $_REQUEST, 0));
    $data['month'] = intval (array_get('p4', $_REQUEST, 0));
    $data['day'] = intval (array_get('p5', $_REQUEST, 0));
    
    $data['link_template'] = "/satimage/{SOURCE}-{TYPE}{YEAR}{MONTH}{DAY}.kml";
    $data['link_params'] = array ('{SOURCE}'=>$data['source'], '{TYPE}'=>$data['type'], '{YEAR}'=>'', '{MONTH}'=>'', '{DAY}'=>'');
    
    if ($data['year'] && $data['month'] && $data['day'])
    {
        $output_template = 'items';

        // get a list of all footprints in the specified day
        $sql = "select s.*, p.url as preview_img_url, p.name as preview_img_name, ";
        $sql .= " ST_AsKML(get_shifted_geom(s.geo_extent)) as kml, ";
        $sql .= " to_char(acquisition_date, 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as when from satimage s";
        $sql .= " LEFT JOIN satimage_published p ON s.name = p.source_image ";
        $sql .= " where s.geo_extent is not null";
        $sql .= " and (p.type is null or p.type = 'PREVIEW')";
        $sql .= " and date_trunc('day', s.acquisition_date) = '{$data['year']}-{$data['month']}-{$data['day']}'";
        $sql .= " order by s.acquisition_date asc";

        $data['doc_name'] = "Day: {$data['day']}";
    
        
        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        
        $data['preview_items'] = array();
        
        while ($row = pg_fetch_assoc($result))
        {    
            $row['preview_kml_url'] = make_url_with_entities("/satimage/{$data['source']}-preview-{$row['preview_img_name']}.kml");
    
        	$data['preview_items'][] = $row;
        }
        
        $sql = "select s.*, p.url as geotiff_img_url, p.name as geotiff_img_name, ";
        $sql .= " ST_AsKML(get_shifted_geom(p.geo_extent)) as kml, ";
        $sql .= " to_char(acquisition_date, 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as when from satimage s";
        $sql .= " JOIN satimage_published p ON s.name = p.source_image ";
        $sql .= " where p.geo_extent is not null";
        $sql .= " and (p.type = 'GEOTIFF')";
        $sql .= " and date_trunc('day', s.acquisition_date) = '{$data['year']}-{$data['month']}-{$data['day']}'";
        $sql .= " order by s.acquisition_date asc";

        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        
        $data['geotiff_items'] = array();
        
        while ($row = pg_fetch_assoc($result))
        {    
            $row['geotiff_kml_url'] = make_url_with_entities("/satimage/{$data['source']}-geotiff-{$row['geotiff_img_name']}.kml");
    
        	$data['geotiff_items'][] = $row;
        }
    }
    else
    {
        if (! $data['year'])
        {
            // create network links to all years in the archive
            $sql = "select distinct extract(year from acquisition_date) as name ";
            $sql .= ", to_char(date_trunc('year', acquisition_date), 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as begin_span ";
            $sql .= ", to_char(date_trunc('year', acquisition_date) + INTERVAL '1 year - 1 second', 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as end_span ";
            $sql .= " from satimage";
            $sql .= " where geo_extent is not null";
            $sql .= " order by name asc";
            $data['link_param_key'] = '{YEAR}';
            $data['doc_name'] = "SkyTruth: {$data['source']} {$data['type']}";
        }
        else if (! $data['month'])
        {
            // create network links to all months in the specified year
            $sql = "select distinct lpad(extract(month from acquisition_date)::text, 2, '0') as name ";
            $sql .= ", to_char(date_trunc('month', acquisition_date), 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as begin_span ";
            $sql .= ", to_char(date_trunc('month', acquisition_date) + INTERVAL '1 month - 1 second', 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as end_span ";
            $sql .= " from satimage";
            $sql .= " where geo_extent is not null";
            $sql .= " and date_trunc('year', acquisition_date) = '{$data['year']}-01-01' ";
            $sql .= " order by name asc";
            $data['link_param_key'] = '{MONTH}';
            $data['link_params']['{YEAR}'] = "-{$data['year']}";
            $data['doc_name'] = "Year: {$data['year']}";
            
            
        }    
        else if (! $data['day'])
        {
            // create network links to all days in the specified month
            $sql = "select distinct lpad(extract(day from acquisition_date)::text, 2, '0') as name ";
            $sql .= ", to_char(date_trunc('day', acquisition_date), 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as begin_span ";
            $sql .= ", to_char(date_trunc('day', acquisition_date) + INTERVAL '1 day - 1 second', 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as end_span ";
            $sql .= " from satimage";
            $sql .= " where geo_extent is not null";
            $sql .= " and date_trunc('month', acquisition_date) = '{$data['year']}-{$data['month']}-01' ";
            $sql .= " order by name asc";
            $data['link_param_key'] = '{DAY}';
            $data['link_params']['{YEAR}'] = "-{$data['year']}";
            $data['link_params']['{MONTH}'] = "-". str_pad($data['month'], 2, '0', STR_PAD_LEFT);
            $data['doc_name'] = "Month: {$data['month']}";
        }    
        
        $result = pg_query ($geodb, $sql);
        if (!$result) die(pg_last_error());
        
        $data['items'] = array();
        
        while ($row = pg_fetch_assoc($result))
        {    
            $data['link_params'] [$data['link_param_key']] = "-{$row['name']}";
                
            $row['url'] = make_url_with_entities(str_replace (array_keys($data['link_params']), array_values($data['link_params']), $data['link_template']));
    
        	$data['items'][] = $row;
        }

    }
    
}
else // type=="GEOTIFF"
{
    $data['name'] = pg_escape_string (array_get('p3', $_REQUEST, ''));
    if (array_get('p4', $_REQUEST, ''))
        $data['name'] = $data['name'] . '-' . pg_escape_string (array_get('p4', $_REQUEST, ''));
    if (array_get('p5', $_REQUEST, ''))
        $data['name'] = $data['name'] . '-' . pg_escape_string (array_get('p5', $_REQUEST, ''));
    
    $sql =  "select p.*,   ";
    $sql .= " to_char(s.acquisition_date, 'YYYY-MM-DD\"T\"HH24:MI:SSZ') as when, ";
    if ($data['type'] == 'PREVIEW')
    {
        $sql .= " get_kml_latlonquad(p.geo_extent) as kml";
//        $sql .= " ST_X(ST_PointN(ST_ExteriorRing(p.geo_extent), 1)) || ',' || ST_Y(ST_PointN(ST_ExteriorRing(p.geo_extent), 1)) || ',0 ' || ";
//        $sql .= " ST_X(ST_PointN(ST_ExteriorRing(p.geo_extent), 2)) || ',' || ST_Y(ST_PointN(ST_ExteriorRing(p.geo_extent), 2)) || ',0 ' || ";
//        $sql .= " ST_X(ST_PointN(ST_ExteriorRing(p.geo_extent), 3)) || ',' || ST_Y(ST_PointN(ST_ExteriorRing(p.geo_extent), 3)) || ',0 ' || ";
//        $sql .= " ST_X(ST_PointN(ST_ExteriorRing(p.geo_extent), 4)) || ',' || ST_Y(ST_PointN(ST_ExteriorRing(p.geo_extent), 4)) || ',0' as coords ";
    }
    else
    {
        $sql .= "get_kml_latlonbox(p.geo_extent) as kml";
//        $sql .= " ST_X(ST_PointN(ST_ExteriorRing(p.geo_extent), 1)) as west,  ";
//        $sql .= " ST_Y(ST_PointN(ST_ExteriorRing(p.geo_extent), 1)) as south, ";
//        $sql .= " ST_X(ST_PointN(ST_ExteriorRing(p.geo_extent), 3)) as east,  ";
//        $sql .= " ST_Y(ST_PointN(ST_ExteriorRing(p.geo_extent), 3)) as north ";
    }
    
    $sql .= " from satimage_published p, satimage s";
    $sql .= " where p.geo_extent is not null";
    $sql .= " and p.source_image = s.name";
    $sql .= " and (p.type = '{$data['type']}')";
    $sql .= " and (p.name = '{$data['name']}')";

    $result = pg_query ($geodb, $sql);
    if (!$result) die(pg_last_error());

    $row = pg_fetch_assoc($result);
    $data['item'] = $row;
    $output_template = 'image';
}

require ("templates/satimage.$output_template.template");


?>