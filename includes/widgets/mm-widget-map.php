<?php 

// register WpMaps_Display_Widget widget
function register_mm_wpmaps() {
    register_widget( 'WpMaps_Display_Widget' );
}
add_action( 'widgets_init', 'register_mm_wpmaps' );

/**
 * Adds WpMaps_Display_Widget widget.
 */
class WpMaps_Display_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'mm_wpmaps', // Base ID
			__('Wp Maps', 'wp-maps'), // Name
			array( 'description' => __( 'A News Shop_Map Widget in which you can choose the map to display', 'wp-maps' ),
			 'panels_groups' => array('mimo'), 
			 'panels_icon' => 'dashicons dashicons-yes',) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_id = apply_filters( 'widget_post_id', $instance['post_id'] );
		$category_id = apply_filters( 'widget_category_id', $instance['category_id'] );
		$opened = apply_filters( 'widget_opened', $instance['opened'] );
		$posts_per_page = apply_filters( 'widget_posts_per_page', $instance['posts_per_page'] );
		$posttype = apply_filters( 'widget_posttype', $instance['posttype'] );
		$map_id = apply_filters( 'widget_map_id', $instance['map_id'] );
		
		
		$id = $args['widget_id'];

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title']; 
			
			//Output
			
		
		 
			
		WpMaps_Display::display_map($map_id, $post_id, $category_id, $opened, $posts_per_page, $posttype);
		WpMaps_Display::wpmaps_map_html();

	 ?>
	

	

<?php echo $args['after_widget'];
	}
	     
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$title = isset($instance['title']) ? esc_attr($instance['title']) : 'Wp Map';
		$posts_per_page = isset($instance['posts_per_page']) ? esc_attr($instance['posts_per_page']) : '-1';
		$posttype = isset($instance['posttype']) ? esc_attr($instance['posttype']) : 'post';
		$post_id = isset($instance['post_id']) ? esc_attr($instance['post_id']) : '';
		$category_id = isset($instance['category_id']) ? esc_attr($instance['category_id']) : '';
		$opened = isset($instance['opened']) ? esc_attr($instance['opened']) : 'yes';		
		$map_id = isset($instance['map_id']) ? esc_attr($instance['map_id']) : '';
		
		
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','wp-maps' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'posttype' ); ?> "><?php _e('Post Type to use:', 'wp-maps'); ?></label>
		<select id="<?php echo $this->get_field_id( 'posttype' ); ?>" name="<?php echo $this->get_field_name( 'posttype' ); ?>" value="<?php echo esc_attr( $posttype ); ?>" type="select">
		      <?php $mmargs = array(
				   'public'   => true,
				   '_builtin' => false
				);

				$output = 'names'; // names or objects, note names is the default
				$operator = 'and'; // 'and' or 'or'

				$posttypes = get_post_types( $mmargs, $output, $operator );
				array_unshift($posttypes, 'post'); 
				$imageoptions = $posttypes;
				  foreach ($imageoptions as $option) {
					  
					  echo '<option value="' . $option . '" id="' . $option . '"', $posttype == $option ? ' selected="selected"' : '', '>', $option, '</option>'; } ?>

		</select>
	</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>"><?php _e( 'Number of posts to show:','wp-maps' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>"  type="number" value="<?php echo esc_attr( $posts_per_page ); ?>" />
	</p>


		<p>
		<label for="<?php echo $this->get_field_id( 'map_id' ); ?>"><?php _e( 'Map id','wp-maps' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'map_id' ); ?>" name="<?php echo $this->get_field_name( 'map_id' ); ?>" type="text" value="<?php echo esc_attr( $map_id ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php _e( 'Post id','wp-maps' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>" type="text" value="<?php echo esc_attr( $post_id ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php _e( 'Category id','wp-maps' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'category_id' ); ?>" name="<?php echo $this->get_field_name( 'category_id' ); ?>" type="text" value="<?php echo esc_attr( $category_id ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'opened' ); ?> "><?php _e('Map Opened:', 'wp-maps'); ?></label>
		<select id="<?php echo $this->get_field_id( 'opened' ); ?>" name="<?php echo $this->get_field_name( 'opened' ); ?>" value="<?php echo esc_attr( $opened ); ?>" type="select">
		      <?php $mapoptions = array(
				   true   => 'Yes',
				   false => 'No'
				);

				  foreach ($mapoptions as $option) {
					  
					  echo '<option value="' . $option . '" id="' . $option . '"', $opened == $option ? ' selected="selected"' : '', '>', $option, '</option>'; } ?>

		</select>
	</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['posts_per_page'] = ( ! empty( $new_instance['posts_per_page'] ) ) ? strip_tags( $new_instance['posts_per_page'] ) : '';
		$instance['post_id'] = ( ! empty( $new_instance['post_id'] ) ) ? strip_tags( $new_instance['post_id'] ) : '';
		$instance['category_id'] = ( ! empty( $new_instance['category_id'] ) ) ? strip_tags( $new_instance['category_id'] ) : '';
		$instance['opened'] = ( ! empty( $new_instance['opened'] ) ) ? strip_tags( $new_instance['opened'] ) : '';
		$instance['posttype'] = ( ! empty( $new_instance['posttype'] ) ) ? strip_tags( $new_instance['posttype'] ) : '';
		$instance['map_id'] = ( ! empty( $new_instance['map_id'] ) ) ? strip_tags( $new_instance['map_id'] ) : '';
		
		
		
		return $instance;
	}

} // class WpMaps_Display_Widget
?>