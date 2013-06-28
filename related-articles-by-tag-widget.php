<?php

class RelatedPostsByTag_Widget extends WP_Widget {

	public function __construct() {
		// widget actual processes
		parent::__construct('related_articles_by_tag_widget', // Base ID
		'Related Articles By Tag Widget', // Name
		array('description' => __('Posts and pages with the same tag(s)', 'text_domain'), ) // Args
		);
	}

	public function widget($args, $instance) {
		global $post;
		$post_count = 0;
		$output = '';
		
		if($post->ID > 0 && !is_home()) {
			// outputs the content of the widget
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);
	
			$output =  $before_widget;
			if (!empty($title))
				$output .= $before_title . $title . $after_title;
			
			$output .= get_related_articles($post->ID, $post_count);;
			
			$output .= $after_widget;
		}
		
		echo $output;
	}

	public function form($instance) {
		// outputs the options form on admin
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Related Articles', 'text_domain' );
		}

		if ( isset( $instance[ 'limit' ] ) ) {
			$limit = $instance[ 'limit' ];
		}
		else {
			$limit = __( '0', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this -> get_field_name('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this -> get_field_id('title'); ?>" name="<?php echo $this -> get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
		<label for="<?php echo $this -> get_field_name('limit'); ?>"><?php _e('Limit:'); ?></label> 
		<input class="widefat" id="<?php echo $this -> get_field_id('limit'); ?>" name="<?php echo $this -> get_field_name('limit'); ?>" type="text" value="3" disabled="disabled" />
		<br/>
		<span style="font-style: italic;">Max. number of related articles to display. Leave empty for unlimited.</span>
		</p>
		<?php
	}

	public function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( !empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '0';

		return $instance;
	}

}
		
?>