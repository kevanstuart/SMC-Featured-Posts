<?php
 /**
 * Author: Kevan Stuart - Sometimes I Code
 * Version: 3.0
 * Licence: GPL
 * 
 * @package SMC_Featured
 * @author Kevan Stuart
 */
class FpWidget extends WP_Widget {
	public function __construct() {
		load_plugin_textdomain( 'smc-fp' );

		$options = array(
			'class_name' => 'FpWidget',
			'description' => __( 'Display Featured Posts', 'smc-fp' )
		);

		parent::__construct(
			'fp_widget',
			__( 'Featured Posts' , 'smc-fp'),
			$options
		);
	}
	
	public function widget( $args, $instance ) {
		if ( array_key_exists( 'before_widget', $args ) ) {
			echo $args['before_widget'];
		}

		$perPage = ( ! empty( $instance['number'] ) )
			? $instance['number']
			: 5;

		$posts = $this->getFeaturedPosts($perPage);

		if ( ! is_null( $posts) || $posts ) {
			$list = array();

			foreach( $posts AS $post ) {	
				$list[] = sprintf(
					'<li class="fp-item"><a href="%1$s" class="fp-link">%2$s</a></li>',
					get_permalink( $post->ID ),
					$post->post_title
				);
			}
			
			echo sprintf('<ul class="fp-list-widget">%s</ul>', implode( '', $list ) );
		}
		else
		{
			echo '<p>No featured posts found</p>';
		}

		if ( array_key_exists( 'after_widget', $args ) ) { 
			echo $args['after_widget']; 
		}
	}

	public function form( $instance ) {
		$number = isset( $instance['number'] )
			? $instance['number']
			: 5;

		echo sprintf(
			'<p>
				<label for="%1$s">
					%2$s
					<input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s"/>
				</label>
			</p>',
			$this->get_field_id( 'number' ),
			_e( 'Number of posts' ),
			$this->get_field_name( 'number' ),
			esc_attr( $number )
		);
	}

	public function update( $newInstance, $oldInstance )
	{
		$instance['number'] = strip_tags( $newInstance['number'] );
		return $instance;
	}

	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private function getFeaturedPosts( $number )
	{
		$args = array(
			'post_status'=> 'publish',
			'posts_per_page' => $number,
			'orderby' => 'date',
      'order' => 'DESC',
			'cache_results' => true,
			'meta_query' => array(
				array(
					'key' => SMC_FEATURED_META_OPTION,
					'value' => true
				)
			)
   	);

		$featured = new WP_Query( $args );
		return $featured->get_posts();
	}
}
