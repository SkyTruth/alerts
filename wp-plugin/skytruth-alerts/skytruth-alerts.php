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
	    self::$options['sync_cat_ids'] = explode (',',self::$options['sync_cat_ids']);
        self::init_db();
    }
    
    public static function init_db ()
    {
        if (!function_exists('pg_connect')) return;
        
        $options = self::$options;
        $connect_str = 
           "host={$options['geodb_host']} " . 
            "dbname={$options['geodb_database']} " .
            "user={$options['geodb_user']} " .
            "password={$options['geodb_password']}";
                    
        self::$geodb =  pg_connect($connect_str);
        
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
        
    }    
    
    public static function get_feed_entry ($post_id)
    {
        $post = get_post($post_id);
        $cats = get_the_category ($post_id);
        $tags = get_the_tags ($post_id);
        $link = get_permalink ($post_id);  
        $meta = get_metadata ('post', $post_id);
        if (!$post) return;
        
//        skytruth_alerts_log_event (var_export(self::$options, true));
	$cat_ids = array();
	foreach ($cats as $cat)
		$cat_ids[] = $cat->cat_ID;

//        skytruth_alerts_log_event (var_export($cat_ids, true));

        // Must be a post and in the right category
        if ($post->post_type == 'post' and 
		array_intersect($cat_ids, self::$options['sync_cat_ids']))
        {
            $feed_entry = array ();
            
            // TODO: put sourceid in wp_options
            $feed_entry['source_id'] = self::$options['source_id'];
            $feed_entry['source_item_id'] = $post_id;
            $url = 'http://alerts.skytruth.org/source/' .  $feed_entry['source_id'] . '/' . $feed_entry['source_item_id'];
            $feed_entry['id'] = skytruth_alerts_UUID::v3(SKYTRUTH_ALERTS_NAMESPACE_URL, $url);
            $feed_entry['title'] = $post->post_title;    
            $feed_entry['link'] = $link;
            $feed_entry['summary'] = $meta['event_desc'][0];
            $feed_entry['content'] = $post->post_content;
            $feed_entry['lat'] = $meta['lat'][0];
            $feed_entry['lng'] = $meta['lon'][0];
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
                    $values[] = "{" . implode (",", $value) . "}";
                else
                    $values[] = $value;
                $placeholders [] = "$".$count;
                $count += 1;    
            }            
        }   
        
        $sql = "INSERT INTO feedentry (". implode(",", $fields) .") VALUES (". implode(",",$placeholders) .")";
        
        skytruth_alerts_log_event ($sql);
        skytruth_alerts_log_event (implode(", ", $values));
        
        $result = pg_query_params (self::$geodb, $sql, $values);
        
        skytruth_alerts_log_event (pg_result_error($result));
    }

    public static function test ()
    {
	return 'success';
    }

    // get a list of regions that can be used to create subscriptions
    // Supply a point to get only the region(s) that contain that point
    // suppply a bounding box to get all regions that intersect
    //
    // e.g. get_regions()   will get you all regions
    // get_regions (array(39.2,-85.4))  will get you regions that contian the point lat=39.2, lng=-85.4
    // get_regions(array(39.2,-85.4,39.5,-84.6)) will get you all regions that intersect
    //      the box defined by the two give points - lat1,lng1,lat2,lng2

    public static function get_regions ($location=array())
    {
        $sql = 'SELECT id, name, code from region';
        if (count($location) == 2)
            $sql .= " where st_contains(the_geom, st_setsrid(st_point({$location[1]}, {$location[0]}), 4326))";
        else if (count($location) == 4)
            $sql .= " where st_intersects(the_geom, st_setsrid(st_envelope('LINESTRING("
                . "{$location[1]} {$location[0]}, {$location[3]} {$location[2]}"
                . ")'::geometry), 4326))";
        
        $result = pg_query (self::$geodb, $sql);
        if ($result)
            return pg_fetch_all($result);
        else
            skytruth_alerts_log_event (pg_result_error($result));
    }

    // get a list of existing subscriptions for the current user
    public static function get_user_subscriptions ()
    {
        global $user_email;
        
        if (is_user_logged_in())
        {
            $sql = 'SELECT * from "RSSEmailSubscription" where email=$1';
            $result = pg_query_params (self::$geodb, $sql, array($user_email));
            if ($result)
                return pg_fetch_all($result);
            else
                skytruth_alerts_log_event (pg_result_error($result));
        }
        else
            return array ();
    }	


    // cteate a new subscription for the current user
    public static function create_user_subscription ($region_code)
    {
        global $user_email;
        
        if (is_user_logged_in())
        {
            
            // TODO construct rss url
            $rss_url = "http://appalachianwaterwatch.org/alerts/rss?region=$region_code";
            
        	// Check to see if a subscription already exists
            $sql = 'SELECT * FROM "RSSEmailSubscription" WHERE email = $1 AND rss_url = $2 AND active = 1';
            $result = pg_query_params (self::$geodb, $sql, array($user_email, $rss_url));
            if (!$result) skytruth_alerts_log_event (pg_result_error($result));
            if (pg_num_rows($result))
                return pg_fetch_assoc($result);
                        
            $row = array ();
            $row['email'] = $user_email;
            $row['rss_url'] = $rss_url;
            $row['id'] = skytruth_alerts_UUID::v3(SKYTRUTH_ALERTS_NAMESPACE_URL, "$rss_url&email=$user_email");
            $row['confirmed'] = 0;
            $row['active'] = 1;
            
            $param = 1;
            
            $keys_sql = array();
            $values_sql = array();
            foreach ($row as $name=>$value)
            {
                $keys_sql[] = $name;
                $values_sql[] = '$'.$param;    
                $param += 1;
            }

            $sql = 'INSERT INTO "RSSEmailSubscription" (' . join(',', $keys_sql) . ') VALUES (' .  join(',', $values_sql) . ')';
            $result = pg_query_params (self::$geodb, $sql, $row);
            if (!$result) skytruth_alerts_log_event (pg_result_error($result));
            
            $sql = 'SELECT * from "RSSEmailSubscription" where id=$1';

            $result = pg_query_params (self::$geodb, $sql, array($row['id']));
            if ($result)
                return pg_fetch_assoc($result);
            else
                skytruth_alerts_log_event (pg_result_error($result));
        }
        else
            return false;
    }

    // delete an existing subscription for the current user
    public static function delete_user_subscription ($id)
    {
        $sql = 'DELETE FROM "RSSEmailSubscription" WHERE id = $1';
        $result = pg_query_params (self::$geodb, $sql, array($id));
        if (!$result) skytruth_alerts_log_event (pg_result_error($result));
        return pg_affected_rows($result);
    }	
}


add_action('init', array('skytruth_alerts_plugin', 'init'));
add_action('publish_post', array('skytruth_alerts_plugin', 'publish_post'));
add_action('delete_post', array('skytruth_alerts_plugin', 'delete_post'));
//register_activation_hook( __FILE__, array( 'skytruth_alerts_plugin', 'on_activate' ) );
//register_deactivation_hook( __FILE__, array( 'skytruth_alerts_plugin', 'on_dectivate' ) );
//register_uninstall_hook( __FILE__, array( 'skytruth_alerts_plugin', 'on_uninstasll' ) );

if(is_admin()){require_once('skytruth-alerts-admin.php');}

?>
