<?php
/*
Plugin Name: SkyTruth Alerts
Plugin URI: http://alerts.skytruth.org
Description: Integrates Skytruth Alerts into wordpress
Version: 0.1
Author: Paul Woods
Author URI: https://github.com/pwoods25443
License: GPL2
*/
/*  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


define('SKYTRUTH_ALERTS_VERSION', '0.1');
define('SKYTRUTH_ALERTS_OPTIONS', 'skytruth_alerts_options');
define('SKYTRUTH_ALERTS_DEBUG_LOGFILE', '/tmp/wpalerts.log');
define('SKYTRUTH_ALERTS_NAMESPACE_URL', '6ba7b811-9dad-11d1-80b4-00c04fd430c8');

require_once dirname( __FILE__ ) . '/uuid.php';



function skytruth_alerts_log_event($msg) {
    $fp = fopen(SKYTRUTH_ALERTS_DEBUG_LOGFILE, 'a');
    fwrite($fp, date('Y-m-d H:i:s ', time()));
    fwrite($fp, $msg);
    fwrite($fp, "\n");
    fclose($fp);    
    
}


class skytruth_alerts_plugin 
{
    public static $geodb;
    public static $options = array();
    
    
    public static function init ()
    {
        self::$options =  get_option(SKYTRUTH_ALERTS_OPTIONS);
        self::init_db();
    }
    
    public static function init_db ()
    {
        if (!function_exists('pg_connect')) return;
        
        $options = self::$options;
        self::$geodb =  pg_connect(
            "host={$options['geodb_host']} " . 
            "dbname={$options['geodb_database']} " .
            "user={$options['geodb_user']} " .
            "password={$options['geodb_password']}");        
        
    }

    public static function delete_post ($post_id)
    {
        // delete the feed entry where soruce_item_id == post_id
        skytruth_alerts_log_event ('delete_post ' . $post_id);
    }    
    
    public static function publish_post ($post_id)
    {
        // extract a feed entry from the given post and insert or update it in the geo database

        $feed_entry = self::get_feed_entry ($post_id);
        
        skytruth_alerts_log_event (var_export($feed_entry, true));
        
        self::store_feed_entry ($feed_entry);
        
        
//        $post = get_post($post_id);
//        $cats = get_the_category ($post_id);
//        $cat_slugs = array ();
//        foreach ($cats as $cat)
//            $cat_slugs[] = $cat->slug;
//        skytruth_alerts_log_event ('publish_post cats: ' . implode(' ', $cat_slugs));
//    
//        $tags = get_the_tags ($post_id);
//        $tag_slugs = array ();
//        foreach ($tags as $tag)
//            $tag_slugs[] = $tag->slug;
//        skytruth_alerts_log_event ('publish_post tags: ' . implode(' ', $tag_slugs));
//        
//        $url = 'http://alerts.skytruth.org/source/91/' . $post_id;
//        $uuid = skytruth_alerts_UUID::v3(SKYTRUTH_ALERTS_NAMESPACE_URL, $url);
//        skytruth_alerts_log_event ('publish_post uuid: ' . $uuid);
    }    
    
    public static function get_feed_entry ($post_id)
    {
        $post = get_post($post_id);
        $cats = get_the_category ($post_id);
        $tags = get_the_tags ($post_id);
        $link = get_permalink ($post_id);  
        if (!$post) return;
        
        // Must be a post
        if ($post->post_type == 'post')
        {
            $feed_entry = array ();
            
            // TODO: put sourceid in wp_options
            $feed_entry['source_id'] = 999;
            $feed_entry['source_item_id'] = $post_id;
            $url = 'http://alerts.skytruth.org/source/' .  $feed_entry['source_id'] . '/' . $feed_entry['source_item_id'];
            $feed_entry['id'] = skytruth_alerts_UUID::v3(SKYTRUTH_ALERTS_NAMESPACE_URL, $url);
            $feed_entry['title'] = $post->post_title;    
            $feed_entry['link'] = $link;
            $feed_entry['summary'] = $post->post_excerpt;
            $feed_entry['content'] = $post->post_content;
            $feed_entry['lat'] = 39.464285 + (rand(-5,5) / 100.0);
            $feed_entry['lng'] = -77.797353 + (rand(-1,1) / 10.0);
            $feed_entry['incident_datetime'] = $post->post_date;   
            $feed_entry['tags'] = array ();
            foreach ($tags as $tag)
                $feed_entry['tags'][] = $tag->slug;
            
            return $feed_entry;     
        }
    }

    public static function store_feed_entry ($feed_entry)
    {
        $fields = array();
        $values = array ();
        $placeholders = array();
        $count = 1;
        
        foreach ($feed_entry as $name => $value)
        {
            if ($value)
            {
                $fields[] = $name;
                if ($name == 'tags')
                    $values[] = "{'" . implode ("','", $value) . "'}";
                else
                    $values[] = $value;
                $placeholders [] = "$".$count;
                $count += 1;    
            }            
        }   
        
        $sql = "INSERT INTO feedentry (". implode(",", $fields) .") VALUES (". implode(",",$placeholders) .")";
        
        skytruth_alerts_log_event ($sql);
        
        $result = pg_query_params (self::$geodb, $sql, $values);
        
        skytruth_alerts_log_event (pg_result_error($result));
    }
}



//$skytruth_alerts_config = array();
//
//
//
//
//function skytruth_alerts_init() {
//    global $skytruth_alerts_config;
//        
//	$staOptions = get_option("skytruth_alerts_options");
//    if (function_exists('pg_connect') && !empty($staOptions["geodb_host"]) && !empty($staOptions["geodb_database"]) && !empty($staOptions["geodb_user"])) {
//        
//        $skytruth_alerts_config['geodb'] = pg_connect("host={$staOptions['geodb_host']} dbname={$staOptions['geodb_database']} user={$staOptions['geodb_user']} password={$staOptions['geodb_password']}");
//    }
//    skytruth_alerts_log_event ('init');
//   
//}

//function skytruth_alerts_save_post ($post_id){
//    skytruth_alerts_log_event ('save_post ' . $post_id);
//}
//
//function skytruth_alerts_edit_post ($post){
//    skytruth_alerts_log_event ('edit_post ' . $post);
//}
//
//function skytruth_alerts_delete_post ($post_id){
//    skytruth_alerts_log_event ('delete_post ' . $post_id);
//}

//function skytruth_alerts_publish_post ($post_id){
//    $post = get_post($post_id);
//    $cats = get_the_category ($post_id);
//    $cat_slugs = array ();
//    foreach ($cats as $cat)
//        $cat_slugs[] = $cat->slug;
//    skytruth_alerts_log_event ('publish_post cats: ' . implode(' ', $cat_slugs));
//
//    $tags = get_the_tags ($post_id);
//    $tag_slugs = array ();
//    foreach ($tags as $tag)
//        $tag_slugs[] = $tag->slug;
//    skytruth_alerts_log_event ('publish_post tags: ' . implode(' ', $tag_slugs));
//    
//    $url = 'http://alerts.skytruth.org/source/91/' . $post_id;
//    $uuid = skytruth_alerts_UUID::v3(SKYTRUTH_ALERTS_NAMESPACE_URL, $url);
//    skytruth_alerts_log_event ('publish_post uuid: ' . $uuid);
//}

//function get_feed_entry ($post_id) {
//    $post = get_post($post_id);
//    $cats = get_the_category ($post_id);
//    $tags = get_the_tags ($post_id);
//
//    if (!$post) return;
    
//    feed_entry = array ();
//    
//    feed_entry['title'] = $post->title;    
//    $source_id = 
//    $url = 'http://alerts.skytruth.org/source/91/' . $post_id;
//    feed_entry['id'] = skytruth_alerts_UUID::v3(SKYTRUTH_ALERTS_NAMESPACE_URL, 'http://alerts.skytruth.org/source/' . $source_id. '/' . $post_id;);
//}

//function publish_feed_entry ($post_id) {
//    
//}


add_action('init', array('skytruth_alerts_plugin', 'init'));
add_action('publish_post', array('skytruth_alerts_plugin', 'publish_post'));
add_action('delete_post', array('skytruth_alerts_plugin', 'delete_post'));
//register_activation_hook( __FILE__, array( 'skytruth_alerts_plugin', 'on_activate' ) );
//register_deactivation_hook( __FILE__, array( 'skytruth_alerts_plugin', 'on_dectivate' ) );
//register_uninstall_hook( __FILE__, array( 'skytruth_alerts_plugin', 'on_uninstasll' ) );

if(is_admin()){require_once('skytruth-alerts-admin.php');}

?>