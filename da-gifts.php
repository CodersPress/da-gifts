<?php
/*
Plugin Name: Dating Theme Gifts Extended
Plugin URI: http://coderspress.com
Description: Extended set of gift images for Premiumpress - Dating Theme
Version: 1.0
Revision Date: 17th April 2015
Author: sMarty
Author URI: http://coderspress.com
License: GPLv2
*/

/*************************************************************************************************************/

/* Version - only used for first time install */ 
define ( 'DA_GIFTS_DB_VERSION', '1' );

function da_gifts_activate() {
	global $wpdb;
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

	if (get_site_option('da-gifts-db-version') == '') { // if first install load image to gifts table
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
}

register_activation_hook( __FILE__, 'da_gifts_activate' );

function da_gifts_init() {
	require( dirname( __FILE__ ) . '/includes/da-gifts-admin.php' );
}
da_gifts_init();
?>