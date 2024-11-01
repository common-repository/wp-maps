<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   WpMaps_Display
 * @author    Mimo <mail@mimo.media>
 * @license   GPL-2.0+
 * @link      http://mimo.media
 * @copyright 2015 Mimo
 */



?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<div class="postbox">
			
			<h3 class="hndle"><span><?php _e( 'Wp Maps', 'wp-maps' ); ?></span></h3>
			
			<div class="inside">

				<p> <?php _e( 'If you like this plugin please rate it. find support at ', 'wp-maps' ); ?><a href="http://mimo.studio"><?php _e( 'mimo.studio', 'wp-maps' ); ?></a></p>

			</div>
	</div>
	
	
	<h2 class="nav-tab-wrapper">
			<a href="#tabs-1" class="nav-tab"><?php _e( 'General Settings', 'wp-maps' ); ?></a>
			
		</h2>
	<div id="tabs-1" class="postbox">
		<div class="">
			
			<div class="inside">
		    
		
		    <?php

			$cmb = new_cmb2_box( array(

				'id' => $this->plugin_slug . '_options',
				'hookup' => false,
				'show_on' => array( 'key' => 'options-page', 'value' => array( $this->plugin_slug ) ),
				'show_names' => true,
				    )
		    );

		    $cmb->add_field(array(

				'name' => __( 'Google API key', 'wp-maps' ),
				'desc' => __( 'Get your API key and insert it here', 'wp-maps' ),
				'id' => $this->plugin_slug . '_map_api_key',
				'type' => 'text',
				'default' => '',
				)
			);

			cmb2_metabox_form( $this->plugin_slug . '_options', $this->plugin_slug . '_settings' ); ?>

	   		</div>
	    </div>
	

	
	

	
</div>
