<?php
/**
 * Author: beautifultemplates
 * Author URI: http://www.beautiful-templates.com/
 * Classname: ST_Widget
 */
class ST_Widget extends WP_Widget{
	
 	function __construct() {
		parent::__construct(
			'st_gallery_widget', 
			__('ST Gallery WP', 'st-gallery'), 
			array( 'description' => __( 'ST Gallery WP Widget', 'st-gallery' ), )
		);
		add_action( 'widgets_init', array($this, 'register_gallery_widget') );
	}
	
	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ){
			echo $args['before_title'] . $title . $args['after_title'];
		}
		// Main Widget
		
		$widgetHTML 	= $instance['widgetHTML'];
		
		

		echo do_shortcode($widgetHTML);
		
		//End: Main Widget
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title 		= $instance[ 'title' ];
			$galleryID 	= $instance[ 'galleryID' ];
			$widgetHTML = $instance[ 'widgetHTML' ];
		}
		$option = get_option('st_gallery_wp');
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<div class="row">
			<div class="col-md-12">
					<p>
					<label for="<?php echo $this->get_field_id( 'galleryID' ); ?>"><?php _e( 'Select gallery:' ); ?></label>
					<select class="widefat st_gallery_widget" id="<?php echo $this->get_field_id( 'galleryID' ); ?>" name="<?php echo $this->get_field_name( 'galleryID' ); ?>">
					<?php
						foreach ($option as $key => $gallery) { ?>
							<option value="<?php echo $key ?>" <?php if (esc_attr($galleryID)==$key) echo 'selected';?>><?php echo $gallery['name']; ?></option>
						<?php
						}
					?>
					</p>
					</select>
					<p>
						<span id="<?php echo $this->get_field_id( 'galleryID' ); ?>" class="st_gallery_widget_insert button" onclick="st_gallery_insert(this);"><?php _e( 'Insert' , st-gallery ); ?></span>
						<span id="<?php echo $this->get_field_id( 'galleryID' ); ?>" class="st_gallery_widget_edit button" onclick="st_gallery_edit(this);"><?php _e( 'Edit' , st-gallery ); ?></span>
					</p>
					<p><textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'widgetHTML' ); ?>" name="<?php echo $this->get_field_name( 'widgetHTML' ); ?>"><?php echo esc_attr( $widgetHTML ); ?></textarea></p>
			</div>
		</div>
		
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
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['galleryID'] = ( ! empty( $new_instance['galleryID'] ) ) ? strip_tags( $new_instance['galleryID'] ) : '';
		$instance['widgetHTML'] = ( ! empty( $new_instance['widgetHTML'] ) ) ? strip_tags( $new_instance['widgetHTML'] ) : '';

		return $instance;
	}
	

	function register_gallery_widget() {
	    register_widget('ST_Widget');
	}
}
new ST_Widget();
?>