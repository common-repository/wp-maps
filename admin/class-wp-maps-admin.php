<?php
/**
 * Wp Maps.
 *
 * @package   WpMaps_Display
 * @author    Mimo <mail@mimo.studio>
 * @license   GPL-2.0+
 * @link      http://mimo.studio
 * @copyright 2015 Mimo
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-wp-maps.php`
 *
 * 
 *
 * @package WpMaps_Admin
 * @author  Mimo <mail@mimo.media>
 */
class WpMaps_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		
		$plugin = WpMaps_Display::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->plugin_name = $plugin->get_plugin_name();
		$this->version = $plugin->get_plugin_version();
		$this->cpts = $plugin->get_cpts();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		// Load admin style in dashboard for the At glance widget
		add_action( 'admin_head-index.php', array( $this, 'enqueue_admin_styles' ) );

		// At Glance Dashboard widget for your cpts
		add_filter( 'dashboard_glance_items', array( $this, 'cpt_dashboard_support' ), 10, 1 );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		//Add bubble notification for cpt pending
		add_action( 'admin_menu', array( $this, 'pending_cpt_bubble' ), 999 );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );


       

		
		/*
		 * Add metabox
		 */
		add_action( 'cmb2_init', array( $this, 'wpmaps_metaboxes' ) );

		

		//Add the export settings method
		add_action( 'admin_init', array( $this, 'settings_export' ) );
		//Add the import settings method
		add_action( 'admin_init', array( $this, 'settings_import' ) );


		

		/*
		 * Load Wp_Contextual_Help for the help tabs
		 */
		add_filter( 'wp_contextual_help_docs_dir', array( $this, 'help_docs_dir' ) );
		add_filter( 'wp_contextual_help_docs_url', array( $this, 'help_docs_url' ) );
		if ( !class_exists( 'WP_Contextual_Help' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/WP-Contextual-Help/wp-contextual-help.php' );
		}
		add_action( 'init', array( $this, 'contextual_help' ) );

		/*
		 * Load Wp_Admin_Notice for the notices in the backend
		 * 
		 * First parameter the HTML, the second is the css class
		 */
		if ( !class_exists( 'WP_Admin_Notice' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/WP-Admin-Notice/WP_Admin_Notice.php' );
		}
		//new WP_Admin_Notice( __( 'Updated Messages' ), 'updated' );
		//new WP_Admin_Notice( __( 'Error Messages' ), 'error' );

		/*
		 * CMB 2 for metabox and many other cool things!
		 * https://github.com/WebDevStudios/CMB2
		 */
		require_once( plugin_dir_path( __FILE__ ) . '/includes/CMB2/init.php' );


		/*
		 * Load PointerPlus for the Wp Pointer
		 * 
		 * Unique paramter is the prefix
		 */
		if ( !class_exists( 'PointerPlus' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/PointerPlus/class-pointerplus.php' );
		}
		$pointerplus = new PointerPlus( array( 'prefix' => $this->plugin_slug ) );
		//With this you can reset all the pointer with your prefix
		//$pointerplus->reset_pointer();
		add_filter( 'pointerplus_list', array( $this, 'custom_initial_pointers' ), 10, 2 );

		/*
		 * Load CPT_Columns
		 * 
		 * Check the file for example
		 */
		require_once( plugin_dir_path( __FILE__ ) . 'includes/CPT_Columns.php' );
		
		

		/**
		 * Load Custom addons
		 */
		
		if (! class_exists('PW_CMB2_Field_Google_Maps')) :
			require_once plugin_dir_path(  __FILE__  ) . 'includes/Addons/cmb_field_map-master/cmb-field-map.php';
		endif;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * 
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], get_bloginfo( 'wpurl' ) . '/wp-admin/' ) ) {
			wp_enqueue_style( $this->plugin_slug . '_admin_styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( 'dashicons' ), WpMaps_Display::VERSION );
		}
		
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * 
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '_admin_script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), WpMaps_Display::VERSION );
		}


	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
				__( 'Wp Maps Settings', 'wp-maps' ), $this->plugin_name, 'manage_options', 'wp-maps-settings', array( $this, 'display_plugin_admin_page' )
		);
		/*
		 * Settings page in the menu
		 * 
		 */
		//$this->plugin_screen_hook_suffix = add_menu_page( __( 'Wp Maps Settings', 'wp-maps' ), $this->plugin_name, 'manage_options', 'wp-maps-settings', array( $this, 'display_plugin_admin_page' ), 'dashicons-location', 81);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
		return array_merge(
				array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=' ) . '">' . __( 'Settings' ) . '</a>'
				), $links
		);
	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		//  Define your filter hook callback here
	}

	/**
	 * Add the counter of your CPTs in At Glance widget in the dashboard<br>
	 * NOTE: add in $post_types your cpts, remember to edit the css style (admin/assets/css/admin.css) for change the dashicon<br>
	 *
	 *        Reference:  http://wpsnipp.com/index.php/functions-php/wordpress-post-types-dashboard-at-glance-widget/
	 *
	 * @since    1.0.0
	 */
	public function cpt_dashboard_support( $items = array() ) {
		$post_types = $this->cpts;
		foreach ( $post_types as $type ) {
			if ( !post_type_exists( $type ) ) {
				continue;
			}
			$num_posts = wp_count_posts( $type );
			if ( $num_posts ) {
				$published = intval( $num_posts->publish );
				$post_type = get_post_type_object( $type );
				$text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, $this->plugin_slug );
				$text = sprintf( $text, number_format_i18n( $published ) );
				if ( current_user_can( $post_type->cap->edit_posts ) ) {
					$items[] = '<a class="' . $post_type->name . '-count" href="edit.php?post_type=' . $post_type->name . '">' . sprintf( '%2$s', $type, $text ) . "</a>\n";
				} else {
					$items[] = sprintf( '%2$s', $type, $text ) . "\n";
				}
			}
		}
		return $items;
	}

	/**
	 * Bubble Notification for pending cpt<br>
	 * NOTE: add in $post_types your cpts<br>
	 *
	 *        Reference:  http://wordpress.stackexchange.com/questions/89028/put-update-like-notification-bubble-on-multiple-cpts-menus-for-pending-items/95058
	 *
	 * @since    1.0.0
	 */
	function pending_cpt_bubble() {
		global $menu;

		$post_types = $this->cpts;
		foreach ( $post_types as $type ) {
			if ( !post_type_exists( $type ) ) {
				continue;
			}
			// Count posts
			$cpt_count = wp_count_posts( $type );

			if ( $cpt_count->pending ) {
				// Menu link suffix, Post is different from the rest
				$suffix = ( 'post' == $type ) ? '' : "?post_type=$type";

				// Locate the key of 
				$key = self::recursive_array_search_php( "edit.php$suffix", $menu );

				// Not found, just in case 
				if ( !$key ) {
					return;
				}

				// Modify menu item
				$menu[ $key ][ 0 ] .= sprintf(
						'<span class="update-plugins count-%1$s"><span class="plugin-count">%1$s</span></span>', $cpt_count->pending
				);
			}
		}
	}
	/**
	 * Gets a number of terms and displays them as options
	 * @param  string       $taxonomy Taxonomy terms to retrieve. Default is category.
	 * @param  string|array $args     Optional. get_terms optional arguments
	 * @return array                  An array of options that matches the CMB2 options array
	 */
	public static function get_term_options( $taxonomy = 'category', $args = array() ) {

	    $args['taxonomy'] = $taxonomy;
	    // $defaults = array( 'taxonomy' => 'category' );
	    $args = wp_parse_args( $args, array( 'taxonomy' => 'category' ) );

	    $taxonomy = $args['taxonomy'];

	    $terms = (array) get_terms( $taxonomy, $args );

	    // Initate an empty array
	    $term_options = array();
	    if ( ! empty( $terms ) ) {
	        foreach ( $terms as $term ) {
	            $term_options[ $term->slug] = $term->name;
	        }
	    }

	    return $term_options;
	}
	/**
	 * Required for the bubble notification<br>
	 *
	 *        Reference:  http://wordpress.stackexchange.com/questions/89028/put-update-like-notification-bubble-on-multiple-cpts-menus-for-pending-items/95058
	 *
	 * @since    1.0.0
	 */
	private function recursive_array_search_php( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			$current_key = $key;
			if ( $needle === $value OR ( is_array( $value ) && self::recursive_array_search_php( $needle, $value ) !== false) ) {
				return $current_key;
			}
		}
		return false;
	}

	/**
	 * NOTE:     Your metabox on Map CPT
	 *
	 * @since    1.0.0
	 */
	public function wpmaps_metaboxes() {
		// Start with an underscore to hide fields from custom fields list
		
		$wpmaps_icons_select1 = array(

	        'art-gallery' =>  'map-icon map-icon-boating',
	        'boat-ramp' => 'map-icon map-icon-boat-ramp',
	        'boat-tour' => 'map-icon map-icon-boat-tour',
	        'canoe' => 'map-icon map-icon-canoe',
	        'diving' => 'map-icon map-icon-diving',
	        'fishing' => 'map-icon map-icon-fishing',
	        'fishing-pier' => 'map-icon map-icon-fishing-pier',
	        'fish-cleaning' => 'map-icon map-icon-fish-cleaning',
	        'jet-skiing' => 'map-icon map-icon-jet-skiing',
	        'kayaking' => 'map-icon map-icon-kayaking',
	        'marina' => 'map-icon map-icon-marina',
	        'rafting' => 'map-icon map-icon-rafting',
	        'sailing' => 'map-icon map-icon-sailing',
	        'scuba-diving' => 'map-icon map-icon-scuba-diving',
	        'surfing' => 'map-icon map-icon-surfing',
	        'swimming' => 'map-icon map-icon-swimming',
	        'waterskiing' => 'map-icon map-icon-waterskiing',
	        'whale-watching' => 'map-icon map-icon-whale-watching',
	        'chairlift' => 'map-icon map-icon-chairlift',
	        'cross-country-skiing' => 'map-icon map-icon-cross-country-skiing',
	        'ice-fishing' => 'map-icon map-icon-ice-fishing',
	        'ice-skating' => 'map-icon map-icon-ice-skating',
	        'ski-jumping' => 'map-icon map-icon-ski-jumping',
	        'skiing' => 'map-icon map-icon-skiing',
	        'sledding' => 'map-icon map-icon-sledding',
	        'snow-shoeing' => 'map-icon map-icon-snow-shoeing',
	        'snow' => 'map-icon map-icon-snow',
	        'snowboarding' => 'map-icon map-icon-snowboarding',
	        'snowmobile' => 'map-icon map-icon-snowmobile',
	        'train-station' => 'map-icon map-icon-train-station',
	        'subway-station' => 'map-icon map-icon-subway-station',
	        'bus-station' => 'map-icon map-icon-bus-station',
	        'transit-station' => 'map-icon map-icon-transit-station',
	        'icon-parking' => 'map-icon map-icon-icon-parking',
	        'gas-station' => 'map-icon map-icon-gas-station',
	        'car-rental' => 'map-icon map-icon-car-rental',
	        'car-dealer' => 'map-icon map-icon-car-dealer',
	        'car-repair' => 'map-icon map-icon-car-repair',
	        'car-wash' => 'map-icon map-icon-car-wash',
	        'airport' => 'map-icon map-icon-airport',
	        'taxi-stand' => 'map-icon map-icon-taxi-stand',
			'map-icon map-icon-art-gallery',
	        'campground' => 'map-icon map-icon-campground',
	        'bank' => 'map-icon map-icon-bank',
	        'hair-care' => 'map-icon map-icon-hair-care',
	        'gym' => 'map-icon map-icon-gym',
	        'point-of-interest' => 'map-icon map-icon-bpoint-of-interest',
	        'post-box' => 'map-icon map-icon-post-box',
	        'post-office' => 'map-icon map-icon-post-office',
	        'university' => 'map-icon map-icon-university',
	        'beauty-salon' => 'map-icon map-icon-beauty-salon',
	        'atm' => 'map-icon map-icon-atm',
	        'rv-park' => 'map-icon map-icon-rv-park',
	        'school' => 'map-icon map-icon-school',
	        'library' => 'map-icon map-icon-library',
	        'spa' => 'map-icon map-icon-spa',
	        'route' => 'map-icon map-icon-route',
	        'postal-code' => 'map-icon map-icon-postal-code',
	        'stadium' => 'map-icon map-icon-stadium',
	        'postal-code-prefix' => 'map-icon map-icon-postal-code-prefix',
	        'museum' => 'map-icon map-icon-museum',
	        'finance' => 'map-icon map-icon-finance',
			'natural-feature' => 'map-icon map-icon-natural-feature',
	        'funeral-home' => 'map-icon map-icon-funeral-home',
	        'cemetery' => 'map-icon map-icon-cemetery',
	        'park' => 'map-icon map-icon-park',
	        'lodging' => 'map-icon map-icon-lodging',
	        'female' => 'map-icon map-icon-female',
	        'male' => 'map-icon map-icon-male',
	        'unisex' => 'map-icon map-icon-unisex',
	        'toilet' => 'map-icon map-icon-toilet',
	        'bakery' => 'map-icon map-icon-bakery',
	        'cafe' => 'map-icon map-icon-cafe',
	        'restaurant' => 'map-icon map-icon-restaurant',
	        'food' => 'map-icon map-icon-food',
	        'abseiling' => 'map-icon map-icon-abseiling',
	        'archery' => 'map-icon map-icon-archery',
	        'baseball' => 'map-icon map-icon-baseball',
	        'bicycling' => 'map-icon map-icon-bicycling',
	        'golf' => 'map-icon map-icon-golf',
	        'hang-gliding' => 'map-icon map-icon-hang-gliding',
	        'horse-riding' => 'map-icon map-icon-horse-riding',
	        'inline-skating' => 'map-icon map-icon-inline-skating',
	        'motobike-trail' => 'map-icon map-icon-motobike-trail',
	        'playground' => 'map-icon map-icon-playground',
	        'skateboarding' => 'map-icon map-icon-skateboarding',
	        'tennis' => 'map-icon map-icon-tennis',
	        'walking' => 'map-icon map-icon-walking',
	        'viewing' => 'map-icon map-icon-viewing',
	        'trail-walking' => 'map-icon map-icon-trail-walking',
      	);

	
		 $wpmaps_icons_select = array_flip($wpmaps_icons_select1 ); 

		 $wpmaps_icons_type = array(

	        'MAP_PIN' =>  'MAP_PIN',
	        'SQUARE_PIN' => 'SQUARE_PIN',
	        'SHIELD' => 'SHIELD',
	        'ROUTE' => 'ROUTE',
	        'SQUARE' => 'SQUARE',
	        'SQUARE_ROUNDED' => 'SQUARE_ROUNDED',
	        );


		$wpmaps_location_metabox = new_cmb2_box( array(
		    
		    'id' => 'mimo_location_box',
		    'title' => __( 'Location' , 'wp-maps' ),
		    'object_types'    => array( 'wpmaps_location'),
		    // Where the meta box appear: normal (default), advanced, side. Optional.
		    'context' => 'normal',
		    // Order of meta box: high (default), low. Optional.
		    'priority' => 'high',
		    // Auto save: true, false (default). Optional.
		    'autosave' => true,
		    )
		);
		     
		    
		    $wpmaps_location_metabox->add_field( array(
		    'name' => 'Location',
		    'desc' => 'Please insert your location coordinates, ex.: 37.6329206,-0.6982944, If this element is a route, insert your location coordinates for steps of the route as new rows, last step will be route end and first, route start',
		    'id' => $this->plugin_slug . '_item_location',
		    'type' => 'text',
		    'repeatable' => true,
		
		    
		    ) 
		);

		    $wpmaps_location_metabox->add_field( array(
		    'name' => 'Route Finish',
		    'desc' => 'If this product is a route, drag the marker to set the exact location',
		    'id' => $this->plugin_slug . '_travel_mode',
		    'type'             => 'select',
		    'show_option_none' => true,
		    'default'          => 'DRIVING',
		    'options'          => array(
		        'DRIVING' => __( 'Driving', 'mimo-maps' ),
		        'WALKING'   => __( 'Walking', 'mimo-maps' ),
		        'BICYCLING'     => __( 'Bicycling', 'mimo-maps' ),
		        'TRANSIT'     => __( 'Transit', 'mimo-maps' ),
		    ),
		) );

		     $wpmaps_location_metabox->add_field( array(
		    'name' => 'Link',
		    'desc' => 'Please insert your url',
		    'id' => $this->plugin_slug . '_item_link',
		    'type' => 'text',
		
		    
		    ) 
		);

		     $wpmaps_location_metabox->add_field( array(

				'name' => __( 'Marker Color', 'wp-maps' ),
				'desc' => __( 'Color for marker box', 'wp-maps' ),
				'id' => $this->plugin_slug . '_marker_color',
				'type' => 'colorpicker',
				'default' => '#ffffff',
			    ) 
		    );
	    
		    $wpmaps_location_metabox->add_field( array(

			'name' => __( 'Icon', 'wp-maps' ),
			'desc' => __( 'Choose icon','wp-maps' ),
			'id' => $this->plugin_slug . '_location_icon',
			'type' => 'select',
			'show_option_none' => true,
			'options' => $wpmaps_icons_select,
		    ) 
	    );

		    $wpmaps_location_metabox->add_field( array(

			'name' => __( 'Icon type', 'wp-maps' ),
			'desc' => __( 'Choose icon type','wp-maps' ),
			'id' => $this->plugin_slug . '_icon_type',
			'type' => 'select',
			'show_option_none' => true,
			'options' => $wpmaps_icons_type,
		    ) 
	    );

  

	}

	/**
	 * Process a settings export from config
	 * @since    1.0.0
	 */
	function settings_export() {

		if ( empty( $_POST[ 'pn_action' ] ) || 'export_settings' != $_POST[ 'pn_action' ] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST[ 'pn_export_nonce' ], 'pn_export_nonce' ) ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings[ 0 ] = get_option( $this->plugin_slug . '_settings' );
		$settings[ 1 ] = get_option( $this->plugin_slug . '_settings_colors' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=pn-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			echo json_encode( $settings, JSON_PRETTY_PRINT );
		} else {
			echo json_encode( $settings );
		}
		exit;
	}

	/**
	 * Process a settings import from a json file
	 * @since    1.0.0
	 */
	function settings_import() {

		if ( empty( $_POST[ 'pn_action' ] ) || 'import_settings' != $_POST[ 'pn_action' ] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST[ 'pn_import_nonce' ], 'pn_import_nonce' ) ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$extension = end( explode( '.', $_FILES[ 'pn_import_file' ][ 'name' ] ) );

		if ( $extension != 'json' ) {
			wp_die( __( 'Please upload a valid .json file','wp-maps') );
		}

		$import_file = $_FILES[ 'pn_import_file' ][ 'tmp_name' ];

		if ( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import', 'wp-maps' ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = ( array ) json_decode( file_get_contents( $import_file ) );

		update_option( $this->plugin_slug . '_settings', get_object_vars( $settings[ 0 ] ) );
		update_option( $this->plugin_slug . '_settings_colors', get_object_vars( $settings[ 1 ] ) );

		wp_safe_redirect( admin_url( 'options-general.php?page=wp-maps-settings') );
		exit;
	}

	/**
	 * Filter for change the folder of Contextual Help
	 * 
	 * @since     1.0.0
	 *
	 * @return    string    the path
	 */
	public function help_docs_dir( $paths ) {
		$paths[] = plugin_dir_path( __FILE__ ) . '../help-docs/';
		return $paths;
	}

	/**
	 * Filter for change the folder image of Contextual Help
	 * 
	 * @since     1.0.0
	 *
	 * @return    string    the path
	 */
	public function help_docs_url( $paths ) {
		$paths[] = plugin_dir_path( __FILE__ ) . '../help-docs/img';
		return $paths;
	}

	/**
	 * Contextual Help, docs in /help-docs folter
	 * Documentation https://github.com/voceconnect/wp-contextual-help
	 * 
	 * @since    1.0.0 
	 */
	public function contextual_help() {
		if ( !class_exists( 'WP_Contextual_Help' ) ) {
			return;
		}

		// Only display on the pages - post.php and post-new.php, but only on the `demo` post_type
		WP_Contextual_Help::register_tab( 'wpmaps-help-new-post', __( 'Wp Maps Management', 'wp-maps' ), array(
			'page' => array( 'post.php', 'post-new.php',$this->plugin_slug . '_settings' ),
			'post_type' => array('wpmaps_location'),
			'wpautop' => true
		) );

		// Add to a custom plugin settings page
		WP_Contextual_Help::register_tab( 'wpmaps_settings', __( 'Wp Maps Settings', 'wp-maps' ), array(
			'page' => $this->plugin_slug . '_settings',
			'wpautop' => true
		) );
	}

	/**
	 * Add pointers.
	 * Check on https://github.com/Mte90/pointerplus/blob/master/pointerplus.php for examples
	 *
	 * @param $pointers
	 * @param $prefix for your pointers
	 *
	 * @return mixed
	 */
	function custom_initial_pointers( $pointers, $prefix ) {
		return array_merge( $pointers, array(
			$prefix . '_contextual_tab' => array(
				'selector' => '#contextual-help-link',
				'title' => __( 'Wp Maps Help', 'wp-maps' ),
				'text' => __( 'A pointer for help tab.<br>Go to Posts, Pages or Users for other pointers.', 'wp-maps' ),
				'edge' => 'top',
				'align' => 'right',
				'icon_class' => 'dashicons-welcome-learn-more',
			)
				) );
	}

}
