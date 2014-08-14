<?php
/*
 * Plugin Name: Item Ratings Image Zoom by Benjamin Moody & Zeitguys Inc.
 * Plugin URI: http://www.BenjaminMoody.com & http://www.zeitguys.com
 * Description: 
 * Author: Benjamin Moody
 * Version: 1.0
 * Author URI: http://www.BenjaminMoody.com
 * License: GPL2+
 * Text Domain: zg_item_ratings_zoom
 * Domain Path: /languages/
 */

//Define plugin constants
define( 'ZGITEMRATINGSZOOM__MINIMUM_WP_VERSION', '3.0' );
define( 'ZGITEMRATINGSZOOM__VERSION', '1.0' );
define( 'ZGITEMRATINGSZOOM__DOMAIN', 'zg-item-ratings-zoom-plugin' );
define( 'ZGITEMRATINGSZOOM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ZGITEMRATINGSZOOM__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//Set Activation/Deactivation hooks
register_activation_hook( __FILE__, 'zg_item_ratings_zoom_activation' );
register_deactivation_hook( __FILE__, 'zg_item_ratings_zoom_deactivation' );

function zg_item_ratings_zoom_activation( $network_wide = NULL ) {
	
}

function zg_item_ratings_zoom_deactivation() {
	
}


//Init plugin during 'admin_init' action to ensure parent is instatiated
add_action( 'admin_init', 'zg_item_ratings_zoom_init' );
function zg_item_ratings_zoom_init() {
		
	//Include plugin classes
	require_once( ZGITEMRATINGSZOOM__PLUGIN_DIR . 'class.zg-item-ratings-zoom.php' );
	
	//Instatiate plugin class and pass config options array
	new ZgItemRatingsZoom();
	
}

