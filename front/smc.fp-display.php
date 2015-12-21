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
 * SMCFP Frontend Display class
 */
class Smcfp
{


	private static $initiated = false;
	private $posts = false;


	/**
	 * Activate plugin
	 */
	public static function smcfp_activate() 
	{
		if ( version_compare( $GLOBALS['wp_version'], SMC_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'smcfp' );
			echo sprintf('SMC Featured Posts requires WordPress %s or higher', SMC_MINIMUM_WP_VERSION);
		}
		else
		{
			add_option( 'smc_featured_version', SMC_FEATURED_VERSION );
    		add_option( 'smc_featured_installed', date('Y-m-d H:i:s') );	
		}
	}


	/**
	 * Removes all options
	 */
	public static function smcfp_deactivate() 
	{
		delete_option( 'smc_featured_version' );
    	delete_option( 'smc_featured_installed' );
	}


	/**
	 * Entry function for the class
	 */
	public static function init() 
	{
		if (!self::$initiated)
		{
			self::init_hooks();
		}
	}


	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() 
	{
		self::$initiated = true;
		add_shortcode( 'smc_featured_posts', array('Smcfp', 'displayList') );
	}


	/**
	 * Display basic list of featured posts
	 */
	public static function displayList()
	{
		$posts = self::getFeaturedPosts();
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
	}


	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private static function getFeaturedPosts()
	{
		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'date',
      		'order'		     => 'DESC',
			'cache_results'  => true,
			'meta_query'     => array(
				array(
					'key'   => SMC_FEATURED_META_OPTION,
					'value' => 'true'
				)
			)
   		);

		$featured = new WP_Query($args);
		return $featured->get_posts();
	}
}