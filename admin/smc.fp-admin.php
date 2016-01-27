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

		// Add settings API function
		add_action( 'admin_init', array('Smcfp_Admin', 'adminInit') );

		// Add JS action for Featured
		add_action( 'admin_enqueue_scripts', array('Smcfp_Admin', 'adminJS' ) );

		// Add action for the options menu
		add_action( 'admin_menu', array('Smcfp_Admin', 'adminMenu') );

		// Edit post Meta Box
		add_action( 'add_meta_boxes', array('Smcfp_Admin', 'addMetaBox') );
		add_action( 'save_post', array('Smcfp_Admin', 'saveMetaBox'), 10, 3 );

		// Custom sortable column when viewing posts
		add_filter( 'manage_posts_columns', array('Smcfp_Admin', 'addPostColumn') );
		add_filter( 'manage_edit-post_sortable_columns', array('Smcfp_Admin', 'sortPostColumn') );
		add_action( 'manage_posts_custom_column', array('Smcfp_Admin', 'managePostColumn'), 10, 2 );

		// Ajax page link
		add_action('wp_ajax_add_featured', array('Smcfp_Admin', 'ajaxAddFeatured'));
		add_action('wp_ajax_nopriv_add_featured', array('Smcfp_Admin', 'ajaxAddFeatured'));

		add_action('wp_ajax_del_featured', array('Smcfp_Admin', 'ajaxDelFeatured'));
		add_action('wp_ajax_nopriv_del_featured', array('Smcfp_Admin', 'ajaxDelFeatured'));
	}


	/**
	 * Add sections and fields from WP Settings API for options
	 */
	public static function adminInit()
	{
		add_settings_section("section", "", null, "featured-posts");
	
		add_settings_field(
			"smc_featured_heading", 
			"Shortcode Display Heading", 
			array('Smcfp_Admin', "displayHeading"), 
			"featured-posts", 
			"section"
		);

		add_settings_field(
			"smc_featured_count", 
			"Shortcode Display Count", 
			array('Smcfp_Admin', "displayCount"), 
			"featured-posts", 
			"section"
		);

    	register_setting("section", "smc_featured_heading");
    	register_setting("section", "smc_featured_count");
	}


	/**
	 * Display field for shortcode heading
	 */
	public static function displayHeading()
	{
		echo sprintf(
			'<input type="text" name="smc_featured_heading" class="regular-text" id="smc_featured_heading" value="%s" />',
			get_option('smc_featured_heading')
		);
	}


	/**
	 * Display field for shortcode count
	 */
	public static function displayCount()
	{
		echo sprintf(
			'<input type="text" name="smc_featured_count" class="medium-text" id="smc_featured_count" value="%s" />',
			get_option('smc_featured_count')
		);
	}

	/**
	 * Adds javascript to required admin screen
	 */
	public static function adminJS($hook)
	{
		if( 'edit.php' != $hook ) 
		{
			return;
    	}

    	wp_enqueue_script( 'ajax-script', plugins_url( '/admin.js', __FILE__ ), array('jquery') );
		wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
	}


	/**
	 * Create options menu item
	 */
	public static function adminMenu()
	{
		add_options_page( 
			'SMC Featured Posts',
			'Featured Posts',
			'manage_options',
			'featured-posts',
			array( 'Smcfp_Admin', 'optionPage' ) 
		);
	}


	public static function optionPage()
	{	
		register_setting( 'featured', 'smc_featured_display_count' );

		$file = SMC_FEATURED_ADMIN_DIR . 'settings.php';
		include( $file );
	}


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

		if ( isset($_POST['smcfp_is_featured']) )
		{
			update_post_meta($postId, SMC_FEATURED_META_OPTION, 1);
		}
		else
		{
			delete_post_meta($postId, SMC_FEATURED_META_OPTION);
		}
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
		$columns['featured'] = 'Is Featured';
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
	    		$isFeatured = self::getIsFeatured($id);

	    		$nonce  = wp_create_nonce("smc_fp_link_nonce");
	    		$action = 'del_featured';
	    		$text = 'Yes';
	    		if (!$isFeatured)
	    		{
	    			$action = 'add_featured';
	    			$text = 'No';
	    		}

	    		echo sprintf(
	    			'<a class="featured-item" 
	    			    data-action="%s" data-nonce="%s" data-post-id="%s" href="#">%s</a>',
	    			$action,
	    			$nonce,
	    			$id,
	    			$text
	    		);
	    		//echo '<a class="featured-item" data-action="' . $action . '" data-nonce="' . $nonce . 
	    		//'" data-post-id="' . $id . '" href="#">' . $text . '</a>';
	        break;
	    }
	}


	/**
	 * Ajax add featured post action
	 */
	public static function ajaxAddFeatured()
	{

		$result = array();
		if (!Smcfp_Admin::testPreSave($_POST) )
		{
			$result['status'] = 0;
		}
		else
		{
			update_post_meta($_POST['post_id'], SMC_FEATURED_META_OPTION, 1);	
			$result['status'] = 1;
		}

		echo json_encode($result);
		wp_die();
	}


	/**
	 * Ajax delete featured post action
	 */
	public static function ajaxDelFeatured()
	{
		$result = array();
		if (!Smcfp_Admin::testPreSave($_POST) )
		{
			$result['status'] = 0;
		}
		else
		{
			delete_post_meta($_POST['post_id'], SMC_FEATURED_META_OPTION);	
			$result['status'] = 1;
		}

		echo json_encode($result);
		wp_die();
	}


	/**
	 * DRY implementation to test save data
	 */
	public static function testPreSave($data)
	{
		if (!isset($data['post_id']))
		{
			return false;
		}

		if( !isset($data["smc_fp_link_nonce"]) || 
			!wp_verify_nonce($data['smc_fp_link_nonce'], 'smc_fp_link_nonce') )
		{
			return false;
		}

		if( !current_user_can("edit_post", $data['post_id']) )
		{
			return false;
		}

		return true;
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