<?php
 

//Load wordpress
define('WP_USE_THEMES', false);
//require("/home/sites/appalachianwaterwatch.org/wordpress/wp-load.php");
require("/Users/paulwoods/Sites/wpalerts/wp-load.php");

echo "<h1>SkyTruth Alerts Wordpress Plugin Test Page</h1>";

if ( is_user_logged_in() ) {
    echo 'Welcome, registered user!';
} else {
    echo 'Welcome, visitor!';
}

echo "<br/>";

echo "<h2>create_user_subscription</h2>";

$s = skytruth_alerts_plugin::create_user_subscription ('TEST3');
echo var_export($s, true);

echo "<h2>delete_user_subscription</h2>";

echo var_export(skytruth_alerts_plugin::delete_user_subscription ($s['id']), true);


echo "<h2>get_user_subscriptions</h2>";

echo var_export(skytruth_alerts_plugin::get_user_subscriptions (), true);


echo "<h2>get_regions(point)</h2>";

echo var_export(skytruth_alerts_plugin::get_regions (array (37.38,-81.6)), true);

echo "<h2>get_regions(bbox)</h2>";

echo var_export(skytruth_alerts_plugin::get_regions (array (37.38,-81.6,37.8,-81.1)), true);

echo "<h2>get_regions</h2>";

echo var_export(skytruth_alerts_plugin::get_regions (), true);


?>
