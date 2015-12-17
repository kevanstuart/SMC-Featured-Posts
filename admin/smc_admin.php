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
class SMCFP_admin
{

	/**
	 * Constructor - add hooks here
	 */
	function __construct()
	{
		$this->addSmcfpPostColumn();
		$this->addSmcfpMetaBox();
	}


	/**
	 * Add custom meta box to edit posts page & save
	 */
	function addSmcfpMetaBox()
	{
		add_action( 'add_meta_boxes', array($this, 'addMetaBox') );
		add_action( 'save_post', array($this, 'saveMetaBox'), 10, 3 );
	}


	/**
	 * Add sortable, custom post column in posts list
	 */
	function addSmcfpPostColumn()
	{
		add_filter( 'manage_posts_columns', array($this, 'addPostColumn') );
		add_filter( 'manage_edit-post_sortable_columns', array($this, 'sortPostColumn') );
		add_action( 'manage_posts_custom_column', array($this, 'managePostColumn'), 10, 2 );
	}


	/**
	 * Add custom Meta Box
	 */
	function addMetaBox()
	{
		add_meta_box(
			'smcfpMetaBox',
			__( 'Featured Post', 'smcfp' ),
			array($this, 'metaBoxCallback'),
			'post',
			'side'
		);
	}

	/**
	 * Save custom Meta Box
	 */
	function saveMetaBox($postId, $post, $update)
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
	function metaBoxCallback($post)
	{
		wp_nonce_field( basename(__FILE__), 'smc_fp_box_nonce' );
		$value = $this->getIsFeatured($post->ID);

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
	function addPostColumn($columns)
	{
		$columns['is_featured'] = 'Is Featured';
		return $columns;
	}


	/**
	 * Add sort function to empty custom column
	 */
	function sortPostColumn($columns)
	{
		$columns['is_featured'] = SMC_FEATURED_META_OPTION;
		return $columns;
	}


	/**
	 * Add content to custom column
	 */
	function managePostColumn($columnName, $id)
	{
		switch ($columnName) {
	    	case 'is_featured':
	    		$is = $this->getIsFeatured($id);
	    		echo ($is) ? "Yes" : "No";
	        break;
	    }
	}


	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private function getIsFeatured($id)
	{
		$isFeatured = get_post_meta( $id, SMC_FEATURED_META_OPTION, TRUE);
		return $isFeatured;
	}
}