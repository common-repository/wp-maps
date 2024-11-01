<?php

/**
 * Wp Maps.
 *
 * @package   WpMaps_Display
 * @author    Mimo <mail@mimo.media>
 * @license   GPL-2.0+
 * @link      http://mimo.media
 * @copyright 2015 Mimo
 */

/**
 *
 * @package WpMaps_Display
 * @author  Mimo <mail@mimo.media>
 */
class WpMaps_Display {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_slug = 'wpmaps';

	/**
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_name = 'Wp Maps';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Array of cpts of the plugin
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected $cpts = array( 'wpmaps' );

	/**
	 * Array of capabilities by roles
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */


	protected static $plugin_roles = array(
		'editor' => array(
			'edit_demo' => true,
			'edit_others_demo' => true,
		),
		'author' => array(
			'edit_demo' => true,
			'edit_others_demo' => false,
		),
		'subscriber' => array(
			'edit_demo' => false,
			'edit_others_demo' => false,
		),
	);

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		register_via_cpt_core(
			array( __( 'Location', $this->get_plugin_slug() ), __( 'Locations', $this->get_plugin_slug() ), 'wpmaps_location' ), array(
					'taxonomies' => array( 'wpmaps_location_category' ),
					'capabilities' => array(
						'edit_post' => 'edit_location',
						'edit_others_posts' => 'edit_other_location',
					),
					'supports'           => array( 'title','editor','thumbnail','excerpt' ),
					'map_meta_cap' => true,
					'menu_icon'           => 'dashicons-location',
				)
		);

		// add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );
		register_via_taxonomy_core(
			array( __( 'Category', $this->get_plugin_slug() ), __( 'Categories', $this->get_plugin_slug() ), 'wpmaps_location_category' ), array(
					'public' => true,
					'capabilities' => array(
						'assign_terms' => 'edit_posts',
					),
				), array( 'wpmaps_location' )
		);

		add_filter( 'body_class', array( $this, 'add_pn_class' ), 10, 3 );

		// Override the template hierarchy for load /templates/content-demo.php
		add_filter( 'template_include', array( $this, 'load_content_demo' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_vars' ) );

		add_filter( 'get_posts', array( $this, 'wpmaps_location' ) );
		add_shortcode( 'wpmaps', array( $this, 'wpmaps_map_shortcode' ) );
		add_filter( 'single_template', array( $this, 'wpmaps_custom_template' ) );
		add_image_size( 'wpmaps-thumb', 200, 150, true );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return self::$plugin_slug;
	}

	/**
	 * Return the plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin name variable.
	 */
	public function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Return the version
	 *
	 * @since    1.0.0
	 *
	 * @return    Version const.
	 */
	public function get_plugin_version() {
		return self::VERSION;
	}

	/**
	 * Return the cpts
	 *
	 * @since    1.0.0
	 *
	 * @return    Cpts array
	 */
	public function get_cpts() {
		return $this->cpts;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses
	 *                                    "Network Activate" action, false if
	 *                                    WPMU is disabled or plugin is
	 *                                    activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses
	 *                                    "Network Deactivate" action, false if
	 *                                    WPMU is disabled or plugin is
	 *                                    deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();
				}
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Add support for custom CPT on the search box
	 *
	 * @since    1.0.0
	 *
	 * @param    object $query
	 */
	public function filter_search( $query ) {
		// if ( $query->is_search ) {
			// Mantain support for post
			// $this->cpts[] = 'post';
			// $query->set( 'post_type', $this->cpts );
		// }
		// return $query;
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// Requirements Detection System - read the doc/example in the library file
		require_once( plugin_dir_path( __FILE__ ) . 'includes/requirements.php' );
		new Wpmaps_Requirements( self::$plugin_name, self::$plugin_slug, array(
			'WP' => new WordPress_Requirement( '4.1.0' ),
		) );

		// Define activation functionality here
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles;
		}

		foreach ( $wp_roles->role_names as $role => $label ) {
			// if the role is a standard role, map the default caps, otherwise, map as a subscriber
			$caps = ( array_key_exists( $role, self::$plugin_roles ) ) ? self::$plugin_roles[ $role ] : self::$plugin_roles['subscriber'];

			// loop and assign
			foreach ( $caps as $cap => $grant ) {
				// check to see if the user already has this capability, if so, don't re-add as that would override grant
				if ( ! isset( $wp_roles->roles[ $role ]['capabilities'][ $cap ] ) ) {
					$wp_roles->add_cap( $role, $cap, $grant );
				}
			}
		}

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = 'wp-maps';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_style( $this->get_plugin_slug() . '_bootstrap', plugins_url( 'assets/css/bootstrap.min.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles-sidebar', plugins_url( 'assets/css/mm-sidebars.css', __FILE__ ), array(), self::VERSION );

		wp_enqueue_style( $this->get_plugin_slug() . 'map-icons-font', plugins_url( 'assets/css/map-icons.css', __FILE__ ), array(), self::VERSION );

	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
	    wp_enqueue_script( 'jquery-effects-core' );
	    wp_enqueue_script( 'jquery-ui-accordion' );
	    wp_enqueue_script( $this->get_plugin_slug() . '_bootstrap', plugins_url( 'assets/js/bootstrap.min.js', __FILE__ ), array( 'jquery', self::$plugin_slug . 'plugin_script' ), self::VERSION );

	}

	/**
	 * Single View for CPT Maps
	 *
	 * @since    1.0.0
	 */
	/* Filter the single_template with our custom function*/
	public static function search( $id, $array ) {
		$i = 0;
		foreach ( $array as $key => $val ) {
			if ( $key === $id ) {
				return $val;
			}
		}
		   return null;
		   $i++;
	}


	public function wpmaps_custom_template( $single ) {
	    global $wp_query, $post;

		/* Checks for single template by post type */
		if ( $post->post_type == 'wpmaps_map' ) {
			if ( file_exists( plugins_url( '../templates/single-map.php', __FILE__ ) ) ) {
				return plugins_url( '../templates/single-map.php', __FILE__ );
			}
		}
	    return $single;
	}

	/* Include Html markup */
	public static function wpmaps_map_html( $echo = true ) {
		if ( ! get_post_type( 'wpmaps_map' ) ) :
			echo '<div class="wpmaps-container"><div id="wpmaps"></div></div>';
		endif;
	}

	// Include General Map for full website
	public static function wpmaps_full_map() {

					$args = array();

					self::display_map( $args );
					self::wpmaps_map_html();

	}

	public static function wpmaps_location() {
			global $wp_query;
		 	$query = $wp_query;

		if ( is_single() ) {
			global $post;
			$id = get_post_meta( get_the_id(), 'wpmaps_location',true );
			if ( $id !== '' ) {
				return true;

			} else {
					return false;
			}
		} else if ( $query->is_main_query() ) {

			global $posts;
			$id = wp_list_pluck( $posts, 'wpmaps_location', $index_key = null );
			if ( count( array_filter( $id ) ) > 0 ) {
				return true;

			} else {
				return false;
			}
		}

	}

	/**
	 * Add class in the body on the frontend
	 *
	 * @since    1.0.0
	 */
	public function add_pn_class( $classes ) {
		$classes[] = $this->get_plugin_slug();
		return $classes;
	}

	// TODO Set default values in function to be overwritten by developers
	public static function display_map( $args ) {

		$defaults = wp_parse_args(array(

			'post_id' => '',
		    'category_slug' => '',
		    'posts_per_page' => '-1',
		    'height' => '500px',
		    'zoom' => '12',

		));

		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );

		// Prepare
			$i = 0;
			$wpmaps_css = '';
			$i2 = 0;

			$mapcolors = get_option( self::$plugin_slug . '_settings' );

		
		$wpmaps_map_api_key = $mapcolors[ self::$plugin_slug . '_map_api_key' ];

		if ( $wpmaps_map_api_key == '' ) {
			echo esc_html( __( 'Sorry no Google Maps API Key found Error 4, please go to Wp Maps Settings and insert your Google Maps API Key' , 'wp-maps' ) );
	 		return;
		}

		
		
		$post_id = ( isset( $args['post_id'] ) ? $args['post_id'] : null);
		$category_slug = ( isset( $args['category_slug'] ) ? $args['category_slug'] : null);
		$posts_per_page = ( isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : '-1');
		$height = ( isset( $args['height'] ) ? $args['height'] : '500px');
		$zoom = ( isset( $args['zoom'] ) ? $args['zoom'] : '12');

		
		if ( null === $post_id || $post_id === '' ) {

			$post_id = null;
			
		} else {

			$post_id = $args['post_id'];

		} ;

			// Set $category_slug
		if ( null === $category_slug || $category_slug === '' ) {

			$category_slug = null;
			
		} else {

			$category_slug = $args['category_slug'];

		} ;

			// Set $category_slug
		if ( null === $posts_per_page || $posts_per_page === '' ) {

			$posts_per_page = '-1';
			
		} else {

			$posts_per_page = $args['posts_per_page'];

		} ;

		if ( null === $height || $height === '' ) {

			$height = '500px';
			
		} else {

			$height = $args['height'];

		} ;

		if ( null === $zoom || $zoom === '' ) {

			$zoom = '12';
			
		} else {

			$height = $args['height'];

		} ;


	   	// Prepare Query Args
	    $map_data = array();

	    $lats  = array();

	    $longs = array();

	    $wpmaps_args = wp_parse_args( array() );

	    $wpmaps_args['ignore_sticky_posts'] = true;

	    if ( $post_id ) { $wpmaps_args['post__in'] = array( $post_id );
		}

		if ( $category_slug  ) : $wpmaps_args['tax_query'] = array(
				array(
					'taxonomy' => 'wpmaps_location_category',
					'field'    => 'slug',
					'terms'    => $category_slug,
				),
			);
		endif;

	    $wpmaps_args['posts_per_page'] = $posts_per_page;
	    $wpmaps_args['post_type'] = 'wpmaps_location';
	    

	    // Query with args
		$map_query = new WP_Query( $wpmaps_args );

		// Set taxonomy depending on Post Type to use
		$wpmaps_taxonomy_post_type = 'wpmaps_location_category';

	    $n = 1;
	    
	    // Loop
	    if ( $map_query->have_posts() ) :
	        while ( $map_query->have_posts() ) : $map_query->the_post();
			    // Get the category slug
	    		$n = 0;
	    		$key = '';

	    		$categories = wp_get_post_terms( get_the_id(), $wpmaps_taxonomy_post_type );
			    foreach ( $categories as $category ) {
					$key = $category->slug;
					$n++;
					if ( $n = 1 ) { break;
					}
				}

			  	
			  	$meta_coords = get_post_meta( get_the_id(), self::$plugin_slug . '_item_location', true );
			  	$mimo_color = get_post_meta( get_the_id(), self::$plugin_slug . '_marker_color', true );
			  	$mimo_icon = get_post_meta( get_the_id(), self::$plugin_slug . '_location_icon', true );
			  	$mimo_icon_type = get_post_meta( get_the_id(), self::$plugin_slug . '_icon_type', true );
			  	$item_link = get_post_meta( get_the_id(), self::$plugin_slug . '_item_link', true );
	           	$travel_mode = get_post_meta( get_the_id(), self::$plugin_slug . '_travel_mode', true );
	            $product_cats = get_the_terms( get_the_id(), $wpmaps_taxonomy_post_type );
	            if ( $product_cats ) { $single_cat = array_shift( $product_cats );
				}
	            if ( isset( $single_cat ) && isset( $product_cats ) ) { $wpmaps_term_id = $single_cat->term_id;
				}
		$single_cat_slug = ( isset( $single_cat  ) ? $single_cat->slug : null);
		$single_cat_name = ( isset( $single_cat  ) ? $single_cat->name : null);

	           

				if ( isset( $meta_coords ) && '' !== $meta_coords  ) {

	            		$startvalue = array_values( $meta_coords )[0];
	            		$coords = explode( ',',$startvalue );
						$startlat = $coords[0]; // latitude
						$startlon = $coords[1]; // longitud

	 			};

				if ( count( $meta_coords ) > 1  ) {

					$allwaypointsandend = array_shift( $meta_coords );
					$lastmarker = end( $meta_coords );
					$endcoords = explode( ',',$lastmarker );
					$endlat = $endcoords[0];
					$endlon = $endcoords[1];

					if ( count( $meta_coords ) > 1 ) { $waypoints = array_slice( $meta_coords, 0, -1 );
					}
				} else {
					$endlat = $endlon = $waypoints = false;
				};

				if ( $meta_coords  ) :
					if ( $startlat !== '0'  ) :

						$map_data[] = array(
							get_the_title(),  // 0
							$startlat, // 1
							$startlon, // 2
							get_the_permalink(), // 3
							$mimo_color, // 4
							$mimo_icon, // 5
							$single_cat_slug, // 6
							$endlat,// 7
							$endlon,// 8
							$mimo_icon_type,// 9
							wp_trim_words( get_the_excerpt(),8 ),// 10
							get_the_post_thumbnail( get_the_id(),'thumbnail' ),// 11
							null,// 12,
							$waypoints,// 13
							plugins_url( 'wp-maps' ),// 14
							__( 'Read more','wp-maps' ),// 15
							$travel_mode,// 16
							$single_cat_name,// 17
							esc_url( $item_link ),// 18

						);

				   endif;
	           	 endif;

				$n++;
	    endwhile;  // End Loop

			wp_reset_postdata();

			if ( ! empty( $map_data ) ) {
				$map_data1 = json_encode( $map_data );
			} else {

				return;
				echo '<p class="mimo-maps-error-message">' . esc_html( 'Sorry no locations found. Error 3' , 'mimo-maps' ) . '</p>';
			}

			$map_data1 = json_encode( $map_data );

			$wp_maps_settings_style = json_encode( $mapcolors );

			wp_enqueue_script( self::$plugin_slug . '_sidebars', plugins_url( 'assets/js/mm-sidebars.js', __FILE__ ), array( 'jquery', self::$plugin_slug . 'plugin_script' ), self::VERSION );

			wp_enqueue_script( self::$plugin_slug . '_plugin_lines', plugins_url( 'assets/js/jquery.line.js', __FILE__ ), array( 'jquery', self::$plugin_slug . 'plugin_script' ), self::VERSION );

			wp_enqueue_script( self::$plugin_slug . '_googlemaps', 'https://maps.googleapis.com/maps/api/js?key=' . $wpmaps_map_api_key,  'jquery' ,'', true );
			wp_enqueue_script( self::$plugin_slug . '_googlemaps_markericons', plugins_url( 'assets/js/map-icon.min.js', __FILE__ ),  'jquery' ,self::$plugin_slug . '_googlemaps', true );
			wp_enqueue_script( self::$plugin_slug . '_googlemaps_markerlibrary', plugins_url( 'assets/js/markerwithlabel.js', __FILE__ ),  'jquery' ,self::$plugin_slug . '_googlemaps', true );
			wp_enqueue_script( self::$plugin_slug . '_plugin_infobox', plugins_url( 'assets/js/infobox.js', __FILE__ ), array( 'jquery', self::$plugin_slug . '_googlemaps' ), self::VERSION );
			wp_enqueue_script( self::$plugin_slug . '_plugin_markerkluster', plugins_url( 'assets/js/markerkluster.js', __FILE__ ), array( 'jquery', self::$plugin_slug . '_googlemaps' ), self::VERSION );
			wp_enqueue_script( self::$plugin_slug . '_plugin_script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery', self::$plugin_slug . '_googlemaps', self::$plugin_slug . '_plugin_infobox', self::$plugin_slug . '_plugin_markerkluster' ), self::VERSION );
			wp_localize_script( self::$plugin_slug . '_plugin_script', 'map_data', $map_data1 );

			$wpmaps_colors = array();

			wp_localize_script( self::$plugin_slug . '_plugin_script', 'wp_maps_settings_style' , $wp_maps_settings_style );
			wp_localize_script( self::$plugin_slug . '_plugin_script', 'wp_maps_height' , $height );
			wp_localize_script( self::$plugin_slug . '_plugin_script', 'wp_maps_zoom' , $zoom );

			wp_enqueue_style( self::$plugin_slug . '_plugin_wpmaps_styles', plugins_url( 'assets/css/mm-custom-styles.css', __FILE__ ), array(), self::VERSION );
			$wpmaps_css .= '

		';
			wp_add_inline_style( self::$plugin_slug . '_plugin_wpmaps_styles', $wpmaps_css );

			add_action( 'wp_enqueue_scripts',  'display_map' );
		else :

			return;
		endif;

	}

	public function enqueue_js_vars() {

	}
	/**
	 * Example for override the template system on the frontend
	 *
	 * @since    1.0.0
	 */
	public function load_content_demo( $original_template ) {
		if ( 'wpmaps_map' == get_post_type( get_the_id() ) ) {
			return wpmaps_get_template_part( 'single', 'map', false );
		} else {
			return $original_template;
		}
	}
	// check the current post for the existence of a short code
	public function has_shortcode( $shortcode = '' ) {

	    $post_to_check = get_post( get_the_id() );

	    // false because we have to search through the post content first
	    $found = false;

	    // if no short code was provided, return false
	    if ( ! $shortcode ) {
	        return $found;
	    }
	    // check the post content for the short code
	    if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
	        // we have found the short code
	        $found = true;
	    }

	    // return our final results
	    return $found;
	}


	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */


	public function action_method_name() {
		// Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */


	public function filter_method_name() {
		// Define your filter hook callback here
	}


	public static function wpmaps_single_cat_icon( $category = '' ) {

		$options = get_option( self::$plugin_slug . '_settings_colors' );
		$entries = $options[ self::$plugin_slug . '_cat_colors' ];
		$i = 0;

		$wpmaps_cat_color_array = array();

		$wpmaps_echo = '';
		foreach ( (array) $entries as $key => $entry ) {

			if ( isset( $entry[ self::$plugin_slug . '_cat_name' ] ) ) {	$wpmaps_cat_name = esc_html( $entry[ self::$plugin_slug . '_cat_name' ] );
			} else {  	$wpmaps_cat_name = '' ;
			};
			if ( isset( $entry[ self::$plugin_slug . '_cat_color' ] ) ) {	$wpmaps_cat_color = esc_html( $entry[ self::$plugin_slug . '_cat_color' ] );
			} else {  	$wpmaps_cat_color = '' ;
			};
			if ( isset( $entry[ self::$plugin_slug . '_text_color' ] ) ) {	$wpmaps_text_color = esc_html( $entry[ self::$plugin_slug . '_text_color' ] );
			} else {  	$wpmaps_text_color = '' ;
			};
			if ( isset( $entry[ self::$plugin_slug . '_tax_icon' ] ) ) {	$wpmaps_cat_icon = esc_html( $entry[ self::$plugin_slug . '_tax_icon' ] );
			} else {  	$wpmaps_cat_icon = '' ;
			};

			if ( $wpmaps_cat_name !== '' ) { $wpmaps_cat_array[] = $wpmaps_cat_name ;
			}
			if ( $wpmaps_cat_color !== '' ) { $wpmaps_color_array[] = $wpmaps_cat_color;
			}
			if ( $wpmaps_cat_icon !== '' ) { $wpmaps_icon_array[] = $wpmaps_cat_icon;
			}

			$i++;
		}
		if ( isset( $wpmaps_cat_array ) && isset( $wpmaps_color_array ) ) :
			$wpmaps_cat_color_array = array_combine( $wpmaps_color_array, $wpmaps_cat_array );
			$wpmaps_cat_icon_array = array_combine( $wpmaps_icon_array, $wpmaps_cat_array );

			$wpmaps_term_id = $category;
			$wpmaps_tax_icon = array_search( $wpmaps_term_id, $wpmaps_cat_icon_array );
			if ( $wpmaps_tax_icon !== false ) :

				 $wpmaps_echo .= '<i class="wpmaps-' . esc_html( $wpmaps_term_id ) . ' ' . esc_html( $wpmaps_tax_icon ) . '"></i>';

			endif;

		endif;
		return $wpmaps_echo;
	}

	/**
	 *
	 *        Reference:  http://codex.wordpress.org/Shortcode_API
	 *
	 * @since    1.0.0
	 */


	public function wpmaps_map_shortcode( $atts ) {
		// Shortcode that shows the map
		 extract( shortcode_atts( array(

			 'post_id' => '',
			 'category_slug' => '',
			 'posts_per_page' => '-1',
			 'height' => '500px',
	    	'zoom' => '12',

		 ) , $atts ) );

		 $args = array();

		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args( $atts );

		 	self::display_map( $args );

		  	self::wpmaps_map_html();

	}

}
