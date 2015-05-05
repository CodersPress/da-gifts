<?php
/*
Plugin Name: Dating Theme Gifts Extended
Plugin URI: http://coderspress.com
Description: Extended set of gift images for Premiumpress - Dating Theme
Version: 2015.0503
Revision Date: 3rd May 2015
Author: sMarty
Author URI: http://coderspress.com
License: http://creativecommons.org/licenses/GPL/2.0
*/
add_action( 'init', 'dag_plugin_updater' );
function dag_plugin_updater() {
	include_once( dirname( __FILE__ ) . '/updater.php' );
	define( 'WP_DAG_FORCE_UPDATE', true );
	if ( is_admin() ) { 
		$config = array(
			'slug' => plugin_basename( __FILE__ ),
			'proper_folder_name' => 'da-gifts',
			'api_url' => 'https://api.github.com/repos/CodersPress/da-gifts',
			'raw_url' => 'https://raw.github.com/CodersPress/da-gifts/master',
			'github_url' => 'https://github.com/CodersPress/da-gifts',
			'zip_url' => 'https://github.com/CodersPress/da-gifts/zipball/master',
			'sslverify' => true,
			'requires' => '3.0',
			'tested' => '4.2.1',
			'readme' => 'README.md',
			'access_token' => '2251a69f71a70ab9368c4dcf706554f23e493e2e',
		);
		new WP_DAG_Updater( $config );
	}
}

/* Version - only used for first time install */ 
define ( 'DA_GIFTS_DB_VERSION', '1' );

function da_gifts_add_admin_menu() {
		add_menu_page( 'Dating Gifts', 'DA Gifts Extented', 'manage_options', 'da-gifts-settings', 'da_gifts_admin' );
        add_action('admin_init', 'register_dag_settings');
		}

function register_dag_settings() {
    register_setting("dag-settings-group", "dag_memberShipOnly");
}

add_action( 'admin_menu', 'da_gifts_add_admin_menu' );


function da_gifts_activate() {
	global $wpdb;
	if (get_site_option('da-gifts-db-version') == '') { // if first install load images and create DB table
	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	$sql[] = "CREATE TABLE {$wpdb->base_prefix}da_gifts (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				gift_name varchar(100) NOT NULL,
				gift_image varchar(100) NOT NULL,
                count bigint(20) NOT NULL DEFAULT '0',
			    KEY gift_name (gift_name)
		 	   ) {$charset_collate};";

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );

	dbDelta($sql);
	if ($handle = opendir( dirname( __FILE__ ) . '/includes/images/') ) {
	    while (false !== ($imagefile = readdir($handle))) {
		if (imagefile != 'admin' && imagefile != '' && $imagefile != '.' && imagefile != '..') {
		$imagename = explode(".", $imagefile);
	if ( $imagename[0] != 'admin' && $imagename[0] != '' ){
        $insert = $wpdb->prepare( "INSERT INTO " . $wpdb->base_prefix . "da_gifts ( gift_name, gift_image ) VALUES ( %s, %s )", $imagename[0], $imagefile );
		$results = $wpdb->query( $insert );
	}
		}
        	    }
    	}
    	closedir($handle);
	}
     /* Version Set to 1 so we don't install DB again */ 
	update_site_option( 'da-gifts-db-version', DA_GIFTS_DB_VERSION );

add_action('admin_init', 'dag_defaults');
function dag_defaults() {
    $option = array(
        "dag_memberShipOnly" => "yes", 
      );
    foreach($option as $key => $value) {
        if (get_option($key) == NULL) {
            update_option($key, $value);
        }
    }
    return;
}

}

register_activation_hook( __FILE__, 'da_gifts_activate' );

require( dirname( __FILE__ ) . '/includes/da-gifts-admin.php' );

?>