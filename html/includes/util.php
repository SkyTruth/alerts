<?php

function array_get ($key, $arr, $default = Null)
{
    if (array_key_exists($key, $arr))
        return $arr[$key];
    else
        return $default;
}

function get_base_url()
{
	return 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].(($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? '' : ':'.$_SERVER['SERVER_PORT']);
}

function get_current_url($full = true)
{
	if (!$full) return $_SERVER['REQUEST_URI'];
	return get_base_url().$_SERVER['REQUEST_URI'];
}

// usage:  make_url ('/path/file.php', array('param1'=>'A', 'param2'=>'B'))
// If $base_url is ommitted, get_base_url() is used to get the current url hostname and port

function make_url($path, $params = '', $base_url = '', $param_separator='&')
{
    return ($base_url ? $base_url : get_base_url()). $path . 
        ($params ? '?' . http_build_query($params, '', $param_separator) : '');
}

// same as make_url() except that the query parameters are separated with the xml entitiy '&amp;'
// instead of '&'
function make_url_with_entities($path, $params = '', $base_url = '')
{
    return make_url ($path, $params, $base_url, '&amp;');
}


/**

  * Generates an UUID

  * 

  * @author     Anis uddin Ahmad <admin@ajaxray.com>

  * @param      string  an optional prefix

  * @return     string  the formatted uuid

  */

  function uuid($prefix = '')

  {

    $chars = md5(uniqid(mt_rand(), true));

    $uuid  = substr($chars,0,8) . '-';

    $uuid .= substr($chars,8,4) . '-';

    $uuid .= substr($chars,12,4) . '-';

    $uuid .= substr($chars,16,4) . '-';

    $uuid .= substr($chars,20,12);

    return $prefix . $uuid;

  }
  
  
/**
 * Returns the url query as associative array
 *
 * @param    string    query
 * @return    array    params
 */
function convertUrlQuery($query) {
    $queryParts = explode('&', $query);
   
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
   
    return $params;
}

// Set $referrer to '' to use the referrer from the current request
// set to '-' to take the referrer from the page that ultimately displayed the tracking image
function googleAnalyticsGetImageUrl($path='', $referer='') {
    $url = get_base_url();
    $url .= '/ga.php' . "?";
    $url .= "utmac=" . 'UA-25593503-1';
    $url .= "&utmn=" . rand(0, 0x7fffffff);

    $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    if (empty($path))
        $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    if (empty($referer))
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (empty($referer))
      $referer = "-";
      
    $url .= "&utmr=" . urlencode($referer);

    if (!empty($path)) {
      $url .= "&utmp=" . urlencode($path);
    }

    $url .= "&guid=ON";

    return $url;
  }
?>
