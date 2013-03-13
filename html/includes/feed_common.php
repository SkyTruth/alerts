<?php

function default_feed_params ()
{
    $params = array();
    $params['after'] = '';
    $params['bounds'] = array ();   // limit results to this bounding rectangle - this is the same as combining 'lat' and 'lng'
    $params['brief'] = 0;           // brief descriptions
    $params['d'] = '';              // integer number of days ago to include
    $params['dates'] = array();     // pair of dates
    $params['debug'] = 0;           // debug flag - must be set to 'fred' in order to trigger debug output
    $params['id'] = '';             // limit results to just the one item with the given id
    $params['lat'] = array ();      // pair of latitudes extracted from the 'l' or BBOX parameter
    $params['lng'] = array ();      // pair of longitudes extracted from the 'l' BBOX parameter
    $params['max_results'] = 50;    // maximum number of results to return
    $params['nosidebar'] = 0;       // if non-zero, suppress the display of the sidebar in an embedded map
    $params['output_template'] = 'rss';           // output format
    $params['sort'] = 'DESC';       // sort_direction
    $params['tags'] = array();      // list of tags
    $params['tag'] = array();       // alias for tags
    $params['region'] = '';         // region id or code
    $params['region_name'] = '';    // region display name
    
    return $params;
}

function parse_feed_params($request)
{
    global $geodb;
    
    $params = default_feed_params ();    

    if (array_key_exists('after', $request)) 
    {
    	$params['after'] = mysql_escape_string($request['after']);
    }

    if (array_key_exists('f', $request))
    {
    	switch (strtolower($request['f']))
    	{
    		case 'kml':
    			$params['output_template'] = 'kml';
    			break;
    		case 'kml2':
    			$params['output_template'] = 'kml2';
    			break;
    		case 'json':
    			$params['output_template'] = 'json';
    			break;
    		case 'rss':
    			$params['output_template'] = 'rss';
    			break;
    	}
    }
    if (array_key_exists('channel', $request)) 
        $params['channel'] = $request['channel'];
    else
        $params['channel'] = $params['output_template'];
 
    
    if (array_key_exists('BBOX', $request)) 
    {
    	$l = $request['BBOX'];
    	$l = preg_split('/[:,]/', $l);
    	if (sizeof($l) == 4)
    	{
    		$params['lat'][] = floatval($l[1]);
    		$params['lat'][] = floatval($l[3]);
    		$params['lng'][] = floatval($l[0]);
    		$params['lng'][] = floatval($l[2]);
    	}
    }
    if (array_key_exists('l', $request)) 
    {
    	$l = $request['l'];
    	$l = preg_split('/[:,]/', $l);
    	if (sizeof($l) == 4)
    	{
    		$params['lat'][] = floatval($l[0]);
    		$params['lat'][] = floatval($l[2]);
    		$params['lng'][] = floatval($l[1]);
    		$params['lng'][] = floatval($l[3]);
    	}
    }
    
    if (!empty($params['lat']))
    {
        $params['bounds'] = array(array(min($params['lat']), min($params['lng'])), 
                                    array(max($params['lat']), max($params['lng'])));
    }
        
    if (array_key_exists('id', $request)) 
    {
    	$params['id'] = mysql_escape_string($request['id']);
    }

    $tags = '';
    if (array_key_exists('tag', $request))
    	$tags = mysql_escape_string ($request['tag']); // alias for backward compatibility - use 'tags'
    if (array_key_exists('tags', $request))
    	$tags = mysql_escape_string ($request['tags']);
    
    if ($tags)
    {
//        $params['tags'] = preg_split('/[,:]/', $tags);
        $tags = preg_split('/[,]/', $tags);
        foreach ($tags as $tag)
            $params['tags'][] = preg_split('/[:]/', $tag);
        $params['tag'] = $params['tags'];       // alias for backward comaptibility - use 'tags'
    }

    if (array_key_exists('region', $request)) 
    {
    	$params['region'] = mysql_escape_string ($request['region']);

        $sql = "select name from region where code = '{$params['region']}'";
        
        $result = pg_query ($geodb, $sql);      
        if (!$result) die(pg_last_error());
        $row = pg_fetch_assoc($result);
    	if ($row)
    	   $params['region_name'] = $row['name'];
    }
    
    if (array_key_exists('dates', $request)) 
    {
    	$dates = mysql_escape_string ($request['dates']);
        $params['dates'] = preg_split('/[:,]/', $dates);
    }

    if (array_key_exists('d', $request)) 
    {
    	$params['d'] = intval ($request['d']);
    }

    if (array_key_exists('n', $request)) 
    {
    	$params['max_results'] = max( min (intval ($request['n']), 200), 1);
    	$params['n'] = $params['max_results'];
    }

    if (array_key_exists('brief', $request)) 
    {
        $params['brief'] = intval($request['brief']);
    }

    if (array_key_exists('nosidebar', $request)) 
    {
        $params['nosidebar'] = intval($request['nosidebar']);
    }
    
    if (array_key_exists('sort', $request)) 
    {
    	$s = strtolower($request['sort']);
    	switch ($s)
    	{
    		case 'a':
    		case 'asc':
    			$params['sort'] = 'ASC';
    			break;
    	}
    }

    if (array_key_exists('debug', $request)) 
    {
        if (strtolower($request['debug']) == 'fred')
        $params['debug'] = 1;
    }
    
    return $params;
}

function build_feed_url ($params)
{
    $default_params = default_feed_params ();
    
    
    $base_url = '/rss';
    $url_params = array ();
    
    foreach ($params as $key=>$value)
    {
        
        switch ($key)
        {
            case 'output_template':
                $base_url = '/'.$value;
                break;
            case 'bounds':
                if (!empty($value))
                    $url_params['l']  = join(':',$params['bounds'][0]) . ':' . join(':',$params['bounds'][1]);
                break;
            case 'lat':
            case 'lng':
            case 'tags':
            case 'max_results':
                // do nothing - these parameters are not passed through
                break;
            case 'channel':
                if ($value != $params['output_template'])
                    $url_params[$key] = $value;
                break;
            default:
                if (!empty($value) && $value != $default_params[$key])
                    $url_params[$key] = $value;
                break;
        }
    }
    
    return make_url ($base_url, $url_params);
}

function parse_feed_url ($url)
{
    $parsed = parse_url ($url);
    $request = convertUrlQuery($parsed['query']);
    return parse_feed_params($request);
}

function build_feed_query_sql($params)
{
    switch ($params['output_template'])
    {
        case 'rss':
        case 'json':
            $date_sort_field = 'published';
            break;
    
        default:
            $date_sort_field = 'incident_datetime';
            break;
    }   

    $from_sql  = "feedentry fe JOIN feedsource fs on fe.source_id = fs.id ";
    $where_sql = 'true';

    if ($params['after'])
        $where_sql .= " AND $date_sort_field > '${params['after']}'";
    	
    if ($params['bounds'])
    {
    	$where_sql .= ' AND fe.lat>= ' . $params['bounds'][0][0];  
    	$where_sql .= ' AND fe.lat<= ' . $params['bounds'][1][0];  
    	$where_sql .= ' AND fe.lng>= ' . $params['bounds'][0][1];  
    	$where_sql .= ' AND fe.lng<= ' . $params['bounds'][1][1];  
    }
    
    if ($params['id'])
    	$where_sql .= " AND fe.id='${params['id']}'";

    if ($params['tags'])
    {
        $or_terms = array();
//    	foreach ($params['tags'] as $tag)
//    	{
//	       if ($tag) 
//	           $or_terms [] = "'$tag' = ANY (fe.tags)";
//    	}
    	
    	foreach ($params['tags'] as $or_tags)
    	{
    	   $and_terms = array ();
    	   foreach ($or_tags as $tag)
    	       if ($tag) 
    	           $and_terms [] = "'$tag'";
    	   if (!empty($and_terms))
        	   $or_terms[] = " tags @> array[" . join(",", $and_terms) ." ]::character varying[]";
    	}
    	
    	if (!empty($or_terms))
    	{	
    		$where_sql .= " AND ( " . join(" OR ", $or_terms). " )";
    	}
   }

    if ($params['region'])
    {
    	$where_sql .= " AND fe.regions @> array[{$params['region']}]";
    }
   
    if ($params['d'])
    {
    	$where_sql .= " AND $date_sort_field >= NOW() - INTERVAL '{$params['d']} days'";
    }
   
    if ($params['dates'])
    {
    	if (sizeof($params['dates']) >= 1)
    		$where_sql .= " AND $date_sort_field >= TIMESTAMP '{$params['dates'][0]}'";
    	if (sizeof($params['dates']) >= 2)
    		$where_sql .= " AND $date_sort_field <= TIMESTAMP '{$params['dates'][1]}' + INTERVAL '1 day'";
    }

    $order_sql = "fe.$date_sort_field {$params['sort']}";
    
    $sql = "select fe.*, fs.name as source_name, to_char(fe.published, 'YYYY-MM-DD\"T\"HH24:MI:SS.MS+00:00') published_formatted,published as published_sequential" .
    	" from $from_sql WHERE $where_sql " . 
    	" ORDER BY $order_sql " .
    	" LIMIT ${params['max_results']}";
    	
    return $sql;
}

?>
