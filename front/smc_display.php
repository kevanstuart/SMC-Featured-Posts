<?php
/**
 * Plugin name: SMC Selected Featured Posts
 * Description: Display a list of selected posts on the sidebar
 * Author: Kevan Stuart - Sometimes I Code
 * Adapted from: SAN – w3cgallery.com & Windowshostingpoint.com & Syed Balkhi
 * Version: 2.0
 * Licence: GPL
 * 
 * @package SMC_Featured
 * @author Kevan Stuart
 */


/**
 * Required files
 */
//require_once SMC_FEATURED_DISPL_DIR . '/smc_functions.php';


/**
 * SMCFP Frontend Display class
 */
class SMCFP_display
{

	/**
	 * Constructor - add hooks here
	 */
	function __construct()
	{
		$this->getFeaturedPosts();
	}




	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	function getFeaturedPosts()
	{
		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'date',
      		'order'		     => 'DESC',
			'cache_results'  => false,
			'meta_query'     => array(
				array(
					'key'   => SMC_FEATURED_META_OPTION,
					'value' => 'true'
				)
			)
   		);

		$featured = new WP_Query($args);
		return $featured;
	}
}