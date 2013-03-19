<?php

//Load wordpress
define('WP_USE_THEMES', false);
require($CONFIG['WORDPRESS_PATH'] . "wp-load.php");


$AUTH['TAGS'] = $CONFIG['WP_AUTH_FILTER']['anonymous']['tags'];
if ( is_user_logged_in() ) {
    foreach ($CONFIG['WP_AUTH_FILTER']['current_user_can'] as $capability=>$filter)    
        if (current_user_can ($capability))
            $AUTH['TAGS'] = array_merge($AUTH['TAGS'], $filter['tags']);
}

if (! empty ($AUTH['TAGS']))
    $AUTH['FILTER_SQL'] = "tags && ARRAY['" . implode("','",$AUTH['TAGS'])   . "']::character varying[]";

?>