<?php
/**
 * Plugin name: SMC Selected Featured Posts
 * Description: Display a list of selected posts on the sidebar
 * Author: Kevan Stuart - Sometimes I Code
 * Version: 2.0
 * Licence: GPL
 * 
 * @package SMC_Featured
 * @author Kevan Stuart
 */


/**
 * SMCFP Widget Display class
 */
class Smcfp_Widget extends WP_Widget
{
	public function __construct()
	{
		load_plugin_textdomain('smcfp');
		parent::__construct(
			'smcfp_widget',
			__( 'Featured Posts' , 'smcfp'),
			array( 'description' => __( 'Display a list of Featured Posts' , 'smcfp') )
		);
	}


	public function widget( $args, $instance )
	{
		if ( array_key_exists('before_widget', $args) )
		{ 
			echo $args['before_widget']; 
		}

		$per_page = (!empty($instance['number'])) ? $instance['number'] : 5;
		$posts    = $this->getFeaturedPosts($per_page);

		if (!is_null($posts) || $posts)
		{
			$listHtml = '';
			foreach($posts AS $post)
			{
				$tempDate  = new DateTime($post->post_date);
				$postDate  = $tempDate->format('d-m-Y');
				$postLink  = get_permalink($post->ID);
				$postTitle = $post->post_title;

				$text      = '<li class="smcfp-item"><a href="%1$s" class="smcfp-link">%2$s</a></li>';
				$listHtml .= sprintf($text, $postLink, $postTitle . ' - ' . $postDate);
			}
			
			echo sprintf('<ul class="smcfp-list">%s</ul>', $listHtml);
		}
		else
		{
			echo '<p>No featured posts found</p>';
		}

		if ( array_key_exists('after_widget', $args) )
		{ 
			echo $args['after_widget']; 
		}
	}


	public function form( $instance )
	{
		if ( isset( $instance[ 'number' ] ) ) {
			$number = $instance[ 'number' ];
		}
		else {
			$number = __( '5', 'smcfp' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">
				<?php _e( 'Number of Posts:' ); ?>
			</label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
		</p>
		<?php 
	}


	public function update( $newInstance, $oldInstance )
	{
		$instance['number'] = strip_tags( $newInstance['number'] );
		return $instance;
	}


	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private function getFeaturedPosts($number)
	{
		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => $number,
			'orderby'        => 'date',
      		'order'		     => 'DESC',
			'cache_results'  => true,
			'meta_query'     => array(
				array(
					'key'   => SMC_FEATURED_META_OPTION,
					'value' => 1
				)
			)
   		);

		$featured = new WP_Query($args);
		return $featured->get_posts();
	}
}


function smcfp_register_widgets() {
	register_widget( 'Smcfp_Widget' );
}
add_action( 'widgets_init', 'smcfp_register_widgets' );