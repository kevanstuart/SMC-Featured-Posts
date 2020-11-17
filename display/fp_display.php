<?php
 /**
 * Author: Kevan Stuart - Sometimes I Code
 * Version: 3.0
 * Licence: GPL
 * 
 * @package SMC_Featured
 * @author Kevan Stuart
 */
class FpDisplay {
	/**
	 * Initializes WordPress hooks
	 */
	public function init() {
		add_shortcode( 'smc_featured_posts', array( $this, 'displayList' ) );
	}

	/**
	 * Display basic list of featured posts
	 */
	public function displayList() {
		$head  = get_option( 'smc_featured_heading', null );
		$posts = $this->getFeaturedPosts();

		if ( ! is_null( $posts ) || $posts ) {
			$list = array();

			foreach( $posts AS $post ) {
				$list[] = sprintf(
					'<li class="fp-item"><a href="%1$s" class="fp-link">%2$s</a></li>',
					get_permalink( $post->ID );,
					$post->post_title;
				);
			}

			$output = '';
			if ( ! is_null( $head ) ) {
				$output .= sprintf('<h5>%s</h5>', $head);
			}

			$output .= sprintf(
				'<ul class="fp-list">%s</ul>', 
				implode( '', $list )
			);

			return $output;
		}

		return '<p>No featured posts found</p>';
	}


	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private function getFeaturedPosts() {
		$count = get_option( 'smc_featured_count', 5 );
		$args = array(
			'post_status' => 'publish',
			'posts_per_page' => $count,
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