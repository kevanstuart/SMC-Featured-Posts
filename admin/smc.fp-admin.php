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
//require_once SMC_FEATURED_ADMIN_DIR . '/smc_list_table.php';


/**
 * SMCFP Admin class
 */
class Smcfp_Admin
{


	private static $initiated = false;


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

		// Admin page menu
		//add_action( 'admin_menu', array( 'Smcfp_Admin', 'adminMenu' ), 5 );

		// Edit post Meta Box
		add_action( 'add_meta_boxes', array('Smcfp_Admin', 'addMetaBox') );
		add_action( 'save_post', array('Smcfp_Admin', 'saveMetaBox'), 10, 3 );

		// Custom sortable column when viewing posts
		add_filter( 'manage_posts_columns', array('Smcfp_Admin', 'addPostColumn') );
		add_filter( 'manage_edit-post_sortable_columns', array('Smcfp_Admin', 'sortPostColumn') );
		add_action( 'manage_posts_custom_column', array('Smcfp_Admin', 'managePostColumn'), 10, 2 );
	}


	/**
	 * Add admin options menu
	 */
	/*public static function adminMenu() {
		$hook = add_options_page( 
			__('SMC Featured Posts', 'smcfp'), 
			__('SMC Featured Posts', 'smcfp'), 
			'manage_options', 
			'smc-featured-posts', 
			array( 'Smcfp_Admin', 'displayPage' ) 
		);
	}*/


	/*public static function displayPage()
	{}*/


	/**
	 * Add custom Meta Box
	 */
	public static function addMetaBox()
	{
		add_meta_box(
			  'smcfpMetaBox'
			, __( 'Show as Featured Post', 'smcfp_admin' )
			, array('Smcfp_Admin', 'metaBoxCallback')
			, 'post'
			, 'side'
		);
	}


	/**
	 * Save custom Meta Box
	 */
	public static function saveMetaBox($postId, $post, $update)
	{
		// Nonce verified?
		if( !isset($_POST["smc_fp_box_nonce"]) || 
			!wp_verify_nonce($_POST["smc_fp_box_nonce"], basename(__FILE__)) )
		{
			return $postId;
		}

		// User can edit post?
		if( !current_user_can("edit_post", $postId) )
		{
			return $postId;
		}

		// Not autosaving?
		if( defined("DOING_AUTOSAVE") && DOING_AUTOSAVE )
		{
			return $postId;
		}

		// This is a post?
		if( 'post' != $post->post_type )
		{
			return $postId;
		}

		// Get value & update
		$checkBox = "";
		if(isset($_POST["smcfp_is_featured"]))
    	{
        	$checkBox = $_POST["smcfp_is_featured"];
    	}
    	update_post_meta($postId, SMC_FEATURED_META_OPTION, $checkBox);
	}


	/**
	 * Render custom Meta Box
	 */
	public static function metaBoxCallback($post)
	{
		wp_nonce_field( basename(__FILE__), 'smc_fp_box_nonce' );
		$value = self::getIsFeatured($post->ID);

		echo '<label for="smcfp_is_featured">';
		echo '<input type="checkbox" id="smcfp_is_featured" name="smcfp_is_featured" value="true"';

		if ($value)
		{
			echo ' checked="checked" ';
		}

		echo '/>';
		_e( 'Set this post as a Featured Post', 'smcfp' );
		echo '</label> ';
	}


	/**
	 * Add empty custom column to list
	 */
	public static function addPostColumn($columns)
	{
		$columns['featured'] = 'Featured';
		return $columns;
	}


	/**
	 * Add sort function to empty custom column
	 */
	public static function sortPostColumn($columns)
	{
		$columns['featured'] = SMC_FEATURED_META_OPTION;
		return $columns;
	}


	/**
	 * Add content to custom column
	 */
	public static function managePostColumn($columnName, $id)
	{
		switch ($columnName) {
	    	case 'featured':
	    		$is = self::getIsFeatured($id);
	    		echo ($is) ? "Yes" : "No";
	        break;
	    }
	}


	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private static function getIsFeatured($id)
	{
		$isFeatured = get_post_meta( $id, SMC_FEATURED_META_OPTION, TRUE);
		return $isFeatured;
	}
}