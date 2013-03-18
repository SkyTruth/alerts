<?php

// TODO: Add Source ID option
// TODO: Add option Category ID to sync to alerts
// TODO: Add user level visibility filters....


class skytruth_alerts_options_page {
    public $options = array(
        'geodb_host'=>'localhost',
        'geodb_database'=>'wpalerts',
        'geodb_user'=>'postgres',
        'geodb_password'=>'',
        'sync_cat_id'=>'',
        'source_id'=>'999'
        );
    public $messages = array();
    
	function __construct() 
	{
		add_action('admin_menu', array(&$this, 'admin_menu'));
		$opt = get_option(SKYTRUTH_ALERTS_OPTIONS);
		if ($opt)
        	$this->options = array_merge($this->options, $opt);
	}
	function admin_menu () 
	{
		add_options_page('SkyTruth Alerts Options','SkyTruth Alerts','manage_options',__FILE__,array($this, 'settings_page'));
	}
	function  settings_page () 
	{
	   
        $this->messages = array ();
        
    	if(isset($_POST['skytruth_alerts_update'])){
     	    $this->update_options ();
            $this->test_geodb_connection ();
    	}
        $this->write_page ();
	}
	
	function update_options ()
	{
		$this->options["geodb_host"] = trim($_POST['skytruth_alerts_geodb_host']);
		$this->options["geodb_database"] = trim($_POST['skytruth_alerts_geodb_database']);
		$this->options["geodb_user"] = trim($_POST['skytruth_alerts_geodb_user']);
		$this->options["geodb_password"] = trim($_POST['skytruth_alerts_geodb_password']);
		if(empty($this->options["geodb_host"])){
            $messages[] = __('GeoDB Host','SKYTRUTH_ALERTS') . ' ' .  __('cannot be left blank.', 'SKYTRUTH_ALERTS');
		}
		elseif(empty($this->options["geodb_database"])){
            $messages[] = __('GeoDB Database','SKYTRUTH_ALERTS') . ' ' .  __('cannot be left blank.', 'SKYTRUTH_ALERTS');
		}
		elseif(empty($this->options["geodb_user"])){
            $messages[] = __('GeoDB User','SKYTRUTH_ALERTS') . ' ' .  __('cannot be left blank.', 'SKYTRUTH_ALERTS');
		}
		else{
            $messages[] = __('Options Saved','SKYTRUTH_ALERTS');
		}
		
		update_option(SKYTRUTH_ALERTS_OPTIONS,$this->options);
	}
	
	function test_geodb_connection () {
	    $opts = $this->options;
	    
    	if (!function_exists('pg_connect'))
            $this->messages[] = __('WARNING: PGSQL module must be installed in order to connect to the GeoDatabase','SKYTRUTH_ALERTS');
        elseif (
            !empty($opts["geodb_host"]) && 
            !empty($opts["geodb_database"]) && 
            !empty($opts["geodb_user"])) {
            
            $geodb = pg_connect("host={$opts['geodb_host']} dbname={$opts['geodb_database']} user={$opts['geodb_user']} password={$opts['geodb_password']}");
            
            if ($geodb) 
                $this->messages[] = __('GeoDatabase connection successful','SKYTRUTH_ALERTS');
            else
                $this->messages[] = __('ERROR: Unable to connect to GeoDB','SKYTRUTH_ALERTS');
        }
            
	}
	
	function write_page ()
	{
        foreach ($this->messages as $msg){
        	echo '<div id="message" class="updated fade"><p><strong>' . $msg . '.</strong></p></div>';    
        }
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php _e('SkyTruth Alerts Options','SKYTRUTH_ALERTS');?></h2>
<form action="" method="post" enctype="multipart/form-data" name="skytruth_alerts_form">

<h3>GeoDatabase connection parameters</h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e('GeoDB Host','SKYTRUTH_ALERTS'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="skytruth_alerts_geodb_host" value="<?php echo $this->options["geodb_host"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('GeoDB Database','SKYTRUTH_ALERTS'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="skytruth_alerts_geodb_database" value="<?php echo $this->options["geodb_database"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('GeoDB User','SKYTRUTH_ALERTS'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="skytruth_alerts_geodb_user" value="<?php echo $this->options["geodb_user"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('GeoDB Password','SKYTRUTH_ALERTS'); ?>
		</th>
		<td>
			<label>
				<input type="password" name="skytruth_alerts_geodb_pasword" value="<?php echo $this->options["geodb_pasword"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	
</table>

<h3>Content Settings</h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e('Synchronize Category ID','SKYTRUTH_ALERTS'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="skytruth_alerts_sync_cat_id" value="<?php echo $this->options["sync_cat_id"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e('Alerts Source ID','SKYTRUTH_ALERTS'); ?>
		</th>
		<td>
			<label>
				<input type="text" name="skytruth_alerts_source_id" value="<?php echo $this->options["source_id"]; ?>" size="43" style="width:272px;height:24px;" />
			</label>
		</td>
	</tr>
</table>

<p class="submit">
<input type="hidden" name="skytruth_alerts_update" value="update" />
<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes'); ?>" />
</p>
</form>

</div>
<?php	
	   
	}
}

new skytruth_alerts_options_page;

/*
//function skytruth_alerts_admin(){
//	add_options_page('SkyTruth Alerts Options', 'SkyTruth Alerts','manage_options', __FILE__, 'skytruth_alerts_page');
//}

//function skytruth_alerts_page(){
//	$staOptions = get_option("skytruth_alerts_options");
//
//	$messages = array();
//	
//	if (!function_exists('pg_connect'))
//        $messages[] = __('WARNING: PGSQL module must be installed in order to connect to the GeoDatabase','SKYTRUTH_ALERTS');
//	
//	if(isset($_POST['skytruth_alerts_update'])){
//		$staOptions = array();
//		if(empty($staOptions["geodb_host"])){
//            $messages[] = __('GeoDB Host','SKYTRUTH_ALERTS') . ' ' .  __('cannot be left blank.', 'SKYTRUTH_ALERTS');
//		}
//		elseif(empty($staOptions["geodb_database"])){
//            $messages[] = __('GeoDB Database','SKYTRUTH_ALERTS') . ' ' .  __('cannot be left blank.', 'SKYTRUTH_ALERTS');
//		}
//		elseif(empty($staOptions["geodb_user"])){
//            $messages[] = __('GeoDB User','SKYTRUTH_ALERTS') . ' ' .  __('cannot be left blank.', 'SKYTRUTH_ALERTS');
//		}
//		else{
//            $messages[] = __('Options Saved','SKYTRUTH_ALERTS');
//		}
//		
//        // test geodb connection
//        
//        if (function_exists('pg_connect') && !empty($staOptions["geodb_host"]) && !empty($staOptions["geodb_database"]) && !empty($staOptions["geodb_user"])) {
//            
//            $geodb = pg_connect("host={$staOptions['geodb_host']} dbname={$staOptions['geodb_database']} user={$staOptions['geodb_user']} password={$staOptions['geodb_password']}");
//            
//            if ($geodb) 
//                $messages[] = __('GeoDatabase connection successful','SKYTRUTH_ALERTS');
//            else
//                $messages[] = __('ERROR: Unable to connect to GeoDB','SKYTRUTH_ALERTS');
//        }
//
//	}
//
//    foreach ($messages as $msg){
//    	echo '<div id="message" class="updated fade"><p><strong>' . $msg . '.</strong></p></div>';    
//    }
//	
//?>
//<div class="wrap">
//<?php screen_icon(); ?>
//<h2><?php _e('SkyTruth Alerts Options','SKYTRUTH_ALERTS');?></h2>
//<form action="" method="post" enctype="multipart/form-data" name="skytruth_alerts_form">
//
//<table class="form-table">
//	<tr valign="top">
//		<th scope="row">
//			<?php _e('GeoDB Host','SKYTRUTH_ALERTS'); ?>
//		</th>
//		<td>
//			<label>
//				<input type="text" name="skytruth_alerts_geodb_host" value="<?php echo $staOptions["geodb_host"]; ?>" size="43" style="width:272px;height:24px;" />
//			</label>
//		</td>
//	</tr>
//	<tr valign="top">
//		<th scope="row">
//			<?php _e('GeoDB Database','SKYTRUTH_ALERTS'); ?>
//		</th>
//		<td>
//			<label>
//				<input type="text" name="skytruth_alerts_geodb_database" value="<?php echo $staOptions["geodb_database"]; ?>" size="43" style="width:272px;height:24px;" />
//			</label>
//		</td>
//	</tr>
//	<tr valign="top">
//		<th scope="row">
//			<?php _e('GeoDB User','SKYTRUTH_ALERTS'); ?>
//		</th>
//		<td>
//			<label>
//				<input type="text" name="skytruth_alerts_geodb_user" value="<?php echo $staOptions["geodb_user"]; ?>" size="43" style="width:272px;height:24px;" />
//			</label>
//		</td>
//	</tr>
//	<tr valign="top">
//		<th scope="row">
//			<?php _e('GeoDB Password','SKYTRUTH_ALERTS'); ?>
//		</th>
//		<td>
//			<label>
//				<input type="password" name="skytruth_alerts_geodb_pasword" value="<?php echo $staOptions["geodb_pasword"]; ?>" size="43" style="width:272px;height:24px;" />
//			</label>
//		</td>
//	</tr>
//	
//</table>
//<p class="submit">
//<input type="hidden" name="skytruth_alerts_update" value="update" />
//<input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes'); ?>" />
//</p>
//</form>
//
//</div>
//<?php	
//}
*/
//add_action('admin_menu', 'skytruth_alerts_admin');
?>