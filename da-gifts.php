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
/**
 *
 * GNU General Public License, Free Software Foundation
 * <http://creativecommons.org/licenses/GPL/2.0/>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
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

if ( is_admin() ) { 

add_action( 'init', 'DAG_plugin_updater' );

function DAG_plugin_updater() {

	require_once ( dirname( __FILE__ ) . '/includes/da-gifts-updater.php' );

	define( 'WP_DAG_FORCE_UPDATE', true );
		
    $config = array(
        'slug' => plugin_basename(__FILE__),
        'proper_folder_name' => 'da-gifts',
        'api_url' => 'https://api.github.com/repos/CodersPress/da-gifts', 
        'raw_url' => 'https://raw.github.com/CodersPress/da-gifts/master', 
        'github_url' => 'https://github.com/CodersPress/da-gifts', 
        'zip_url' => 'https://github.com/CodersPress/da-gifts/archive/master.zip', 
        'sslverify' => true,
		'requires' => '3.0',
		'tested' => '4.2',
		'readme' => 'README.md',
		'access_token' => 'a04841d0ed87f7a347892f619e54f5025e4a69d1',
		);
    new WP_DAG_Updater( $config );
    }
}
	require( dirname( __FILE__ ) . '/includes/da-gifts-admin.php' );
?>