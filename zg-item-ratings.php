<?php
/*
 * Plugin Name: Item Ratings by Zeitguys Inc.
 * Plugin URI: http://www.zeitguys.com
 * Description: 
 * Author: Zeitguys Inc
 * Version: 1.0
 * Author URI: http://www.zeitguys.com
 * License: GPL2+
 * Text Domain: zg_item_ratings
 * Domain Path: /languages/
 */

//Define plugin constants
define( 'ZGITEMRATINGS__MINIMUM_WP_VERSION', '3.0' );
define( 'ZGITEMRATINGS__VERSION', '1.0' );
define( 'ZGITEMRATINGS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

//Include plugin classes
require_once( ZGITEMRATINGS__PLUGIN_DIR . 'class.zg-item-ratings.php'               );

//Set Activation/Deactivation hooks
register_activation_hook( __FILE__, array( 'ZgItemRatings', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'ZgItemRatings', 'plugin_deactivation' ) );

//Set config options for plugin
$config_options = array(
	array(
		'meta_key'			=>	'META_KEY_POST_RATING',
		'name'				=>	'',
		'sprite_filename'	=>	'',
		'active_post_types'	=>	array(
			'post',
			'attachment'
		)
	)
);

//Instatiate plugin class and pass config options array
new ZgItemRatings( $config_options );