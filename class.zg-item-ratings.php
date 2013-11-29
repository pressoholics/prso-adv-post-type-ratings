<?php
class ZgItemRatings {
	
	protected $class_config 					= array();
	protected $current_screen					= NULL;
	
	function __construct( $config = array() ) {
		
		//Cache plugin congif options
		$this->class_config = $config;
		
		//Init plugin
		add_action( 'current_screen', array($this, 'init_plugin') );
		
	}
	
	/**
	* Overloading
	* 
	* Make use of __call magic method to allow the same plugin method
	* to be used as teh callback for multiple instances of wordpress custom column filter/action
	* 
	* This tricks wordpress filter/action into thinking each callback is a unique method when in fact
	* one method is handling the logic and returning the value.
	*
	* WHY?
	* This was important to allow us to keep the config option array and thus easily manage multiple
	* RAting systems from one place. We loop over the config options array and init each instance as we go
	* using the same methods to do it thus we don't have to duplicate code via class extensions :)
	*
	* @access 	public
	* @author	Ben Moody
	*/
	function __call( $method, $args ) {
		
		//Init vars
		$method_explode = NULL;
		
		//Parse requested method name
		$method_explode = explode('-', $method);
		
		//Detect calls to add_custom_column method
		if( isset($method_explode[0]) && ($method_explode[0] === 'add_custom_column') ) {
			//Call add_custom_column method and pass args along
			return $this->add_custom_column( $method_explode[1], $args[0] );
		}
		
		//Detect calls to add_custom_column_content method
		if( isset($method_explode[0]) && ($method_explode[0] === 'add_custom_column_content') ) {
			//Call add_custom_column_content method and pass args along
			return $this->add_custom_column_content( $method_explode[1], $args[0], $args[1] );
		}
		
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
				add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts') );
				//$this->enqueue_admin_scripts();
				
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
	public function enqueue_admin_scripts() {
		
		//Init vars
		$js_inc_path 	= ZGITEMRATINGS__PLUGIN_URL . 'inc/js/';
		$css_inc_path 	= ZGITEMRATINGS__PLUGIN_URL . 'inc/css/';
		
		wp_enqueue_script( 'jquery' );
		
		//Enqueue scripts for Rate It js plugin
		wp_register_script( 'rate-it',
			$js_inc_path . 'rate-it/jquery.rateit.min.js',
			array('jquery'),
			'1.0.16'
		);
		wp_enqueue_script( 'rate-it' );
		
		//Enqueue this plugin's script
		wp_register_script( 'zg-item-ratings',
			$js_inc_path . 'zg-item-ratings-script.js',
			array('rate-it'),
			'1.0'
		);
		wp_enqueue_script( 'zg-item-ratings' );
		
		//Enqueue stylesheet for rate it plugin - can be de-enqueued and replaced by themes :)
		wp_enqueue_style( 'zg-item-ratings-stars', 
			$css_inc_path . 'zg-item-ratings-stars.css', 
			array(), 
			'1.0' 
		);
		
		//Enqueue stylesheet for plugin
		wp_enqueue_style( 'zg-item-ratings', 
			$css_inc_path . 'zg-item-ratings-style.css', 
			array(), 
			'1.0' 
		);
		
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
						//Note we append option mete key value to create a unique method name for overloading - see $this->__call
						add_filter('manage_media_columns', array($this, 'add_custom_column-' .$option['meta_key'] ), 10);  
						add_action('manage_media_custom_column', array($this, 'add_custom_column_content-' .$option['meta_key']), 10, 2);
						break;
					case 'post';
						//Note we append option mete key value to create a unique method name for overloading - see $this->__call
						add_filter('manage_post_posts_columns', array($this, 'add_custom_column-' .$option['meta_key'] ), 10);  
						add_action('manage_post_posts_custom_column', array($this, 'add_custom_column_content-' .$option['meta_key']), 10, 2);
						break;
					default:
						//Note we append option mete key value to create a unique method name for overloading - see $this->__call
						add_filter('manage_'. $view .'_posts_columns', array($this, 'add_custom_column-' .$option['meta_key'] ), 10);  
						add_action('manage_'. $view .'_posts_custom_column', array($this, 'add_custom_column_content-' .$option['meta_key']), 10, 2);
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
	public function add_custom_column( $config_option_key, $defaults ) {
		
		//Init vars
		$column_slug	= NULL;
		$column_name	= NULL;
		
		//Set column params
		foreach( $this->class_config as $option ) {
			if( $option['meta_key'] === $config_option_key ) {
			
				$column_slug = strtolower($option['meta_key']);
				$column_name = $option['name'];
				
				//Add custom column
				$defaults[$column_slug]  = $column_name;
				break;
			}
		}
		  
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
	public function add_custom_column_content( $config_option_key, $column_name, $post_ID ) {

		//Init vars
		$column_slug	= NULL;
		$output 		= NULL;
		
		//Set column params
		foreach( $this->class_config as $option ) {
			if( $option['meta_key'] === $config_option_key ) {
			
				$column_slug = strtolower($option['meta_key']);

				break;
			}
		}

		if ($column_name == $column_slug) {  
	        
	        ob_start();
	        ?>
	        <div data-productid="<?php esc_attr_e($post_ID); ?>" class="zg-item-ratings-rateit"></div>
	        <?php
	        $output = ob_get_contents();
	        ob_end_clean();
	        
	        //Echo out content
	        echo apply_filters( 'zg_item_ratings_column_stars', $output, $column_name, $post_ID );
	    }
		
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



