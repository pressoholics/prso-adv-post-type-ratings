<?php
class ZgItemRatings {
	
	protected $class_config 	= array();
	protected $current_screen	= NULL;
	
	function __construct( $config = array() ) {
		
		//Cache plugin congif options
		$this->class_config = $config;
		
		//Init plugin
		add_action( 'current_screen', array($this, 'init_plugin') );
		
	}
	
	/**
	* init_plugin
	* 
	* Used By Action: 'current_screen'
	* 
	* Detects current view and decides if plugin should be activated
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function init_plugin() {
		
		//Init vars
		$options 		= $this->class_config;
		
		if( !empty($options) && is_admin() ) {
		
			//Confirm we are on an active admin view
			if( $this->is_active_view() ) {
		
				//Set plugin admin actions
				$this->set_admin_actions();
				
				//Enqueue admin scripts
				$this->enqueue_admin_scripts();
				
			}
			
		}
		
	}
	
	/**
	* is_active_view
	* 
	* Detects if current admin view has been set as 'active_post_type' in
	* plugin config options array.
	* 
	* @var		array	$this->class_config
	* @var		array	$active_views
	* @var		obj		$screen
	* @var		string	$current_screen
	* @return	bool	
	* @access 	protected
	* @author	Ben Moody
	*/
	protected function is_active_view() {
		
		//Init vars
		$options 		= $this->class_config;
		$active_views	= array();
		$screen			= get_current_screen();
		$current_screen	= NULL;
		
		//Cache all views plugin will be active on
		$active_views = $this->get_active_views( $options );
		
		//Cache the current view
		if( isset($screen) ) {
		
			//Is this an attachment screen (base:upload or post_type:attachment)
			if( ($screen->id === 'attachment') || ($screen->id === 'upload') ) {
				$current_screen = 'attachment';
			} else {
				
				//Cache post type for all others
				$current_screen = $screen->post_type;
				
			}
			
			//Cache current screen in class protected var
			$this->current_screen = $current_screen;
		}
		
		//Finaly lets check if current view is an active view for plugin
		if( in_array($current_screen, $active_views) ) {
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
	
	/**
	* get_active_views
	* 
	* Interates over plugin config options array merging all
	* 'active_post_type' values into single array
	* 
	* @param	array	$options
	* @var		array	$active_views
	* @return	array	$active_views
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_active_views( $options = array() ) {
		
		//Init vars
		$active_views = array();
		
		//Loop options and cache each active post view
		foreach( $options as $option ) {
			if( isset($option['active_post_types']) ) {
				$active_views = array_merge($active_views, $option['active_post_types']);
			}
		}
		
		return $active_views;
	}
	
	/**
	 * Helper to set all actions for plugin
	 */
	private function set_admin_actions() {
		
		//Loop options and init custom columns for each active view
		$this->init_custom_admin_columns();
		
		
	}
	
	/**
	 * Helper to enqueue all scripts/styles for admin views
	 */
	private function enqueue_admin_scripts() {
		
	}
	
	/**
	* init_custom_admin_columns
	* 
	* Loops all plugin config options and foreach one loops the
	* 'active_post_types' options calling the correct posts columns action
	* and filter based on the post type provided
	* 
	* @var		array	$options
	* @access 	private
	* @author	Ben Moody
	*/
	private function init_custom_admin_columns() {
		
		//Init vars
		$options 		= $this->class_config;
		
		//Loop plugin config options and init custom columns for each
		foreach( $options as $option ) {
			
			//Setup actions and filters for requested post type views
			foreach( $option['active_post_types'] as $view ) {
				switch( $view ) {
					case 'attachment':
						add_filter('manage_media_columns', array($this, 'add_custom_column'), 10);  
						add_action('manage_media_custom_column', array($this, 'add_custom_column_content'), 10, 2);
						break;
					case 'post';
						add_filter('manage_post_posts_columns', array($this, 'add_custom_column'), 10);  
						add_action('manage_post_posts_custom_column', array($this, 'add_custom_column_content'), 10, 2);
						break;
					default:
						add_filter('manage_'. $view .'_posts_columns', array($this, 'add_custom_column'), 10);  
						add_action('manage_'. $view .'_posts_custom_column', array($this, 'add_custom_column_content'), 10, 2);
						break;
				}
			}
			
		}
		
	}
	
	/**
	* add_custom_column
	* 
	* Called By Filters: 'manage_media_columns', 'manage_post_posts_columns', 'manage_'. [post_type] .'_posts_columns'
	* 
	* @param	array	$defaults
	* @return	array	$defaults
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_custom_column( $defaults ) {
		
		$defaults['first_column']  = 'First Column';  
  
	    /* ADD ANOTHER COLUMN (OPTIONAL) */  
	    // $defaults['second_column'] = 'Second Column';  
	  
	    /* REMOVE DEFAULT CATEGORY COLUMN (OPTIONAL) */  
	    // unset($defaults['categories']);  
	  
	    /* TO GET DEFAULTS COLUMN NAMES: */  
	    // print_r($defaults); 
		
	    return $defaults; 
		
	}
	
	/**
	* add_custom_column
	* 
	* Called By Actions: 'manage_media_custom_column', 'manage_post_posts_custom_column', ''manage_'. [post_type] .'_posts_custom_column''
	* 
	* @param	string	$column_name
	* @param	int		$post_ID
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_custom_column_content( $column_name, $post_ID ) {
		
		if ($column_name == 'first_column') {  
	        // DO STUFF FOR first_column COLUMN  
	        echo 'The post ID is: ' . $post_ID;  
	    }  
	  
	    /* IF YOU NEED ANOTHER COLUMN - UNCOMMENT ALSO 
	    $defaults['second_column'] = 'Second Column'; 
	    in ST4_columns_head() 
	    */  
	  
	    /* 
	    if ($column_name == 'second_column') { 
	        // DO STUFF FOR second_column COLUMN 
	    } 
	    */ 
		
	}
	
	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation( $network_wide ) {
		
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
		
	}
	
}



