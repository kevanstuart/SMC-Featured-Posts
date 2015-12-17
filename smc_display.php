<?php
/**
 * The main admin page for sc_featured_posts plugin.
 * 
 * @package SC-Featured-Posts
 * @version 1.1
 */

require_once('smc_admin.php');

/**
 * Initialize SC Featured Posts list
 */
function sc_featured_posts_init()
{
    /**
     * Disabled in admin view
     */
	if (is_admin()) {
		return;
	}
}

/**
 * Call featured list for list insert
 */
function insert_sc_featured_list ()
{

    $list = featured_posts();

    $output  = '<ul class="featured-posts-list">';
	
    foreach ($list AS $id)
    {
        $output .= '<li>';
        $output .= '<a href="' . get_permalink($postId) . '">' . get_the_title($postId) . '</a>';
        $output .= '</li>';
    }

    $output .= '</ul>';
    echo $output;
}

/**
 * Call featured list for menu insert
 */
function insert_sc_featured_menu()
{

    $list = featured_posts();

    foreach ($list AS $id)
    {
        $output .= '<li>';
        $output .= '<a href="' . get_permalink($postId) . '">' . get_the_title($postId) . '</a>';
        $output .= '</li>';
    }

    echo $output;
}

/**
 * Return the featured post IDs
 * @return array
 */
function featured_posts()
{
    /**
     * Get Featured list from options table
     */
    return sc_featured_posts_get_list();
}
