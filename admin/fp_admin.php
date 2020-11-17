<?php
/**
 * Author: Kevan Stuart - Sometimes I Code
 * Version: 3.0
 * Licence: GPL
 * 
 * @package SMC_Featured
 * @author Kevan Stuart
 */
class FpAdmin {

	/**
	 * Initializes WordPress hooks
	 */
	public function init() {

		/**
		 * Add Settings API for admin page
		 * */
		add_action( 'admin_init', array( $this, 'settingsInit' ) );

		/**
		 * Add the FP settings page to the settings menu
		 */
		add_action( 'admin_menu', array( $this, 'settingsMenu') );

		/**
		 * Add JS action for Featured Posts
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'settingsJS' ) );	
		
		/**
		 * Add the Post meta box option for Featured Posts
		 */
		add_action( 'add_meta_boxes', array( $this, 'addMetaBox') );
		add_action( 'save_post', array( $this, 'saveMetaBox'), 10, 3 );
		
		/**
		 * Add the custom sortable column in Posts list
		 */
		add_filter( 'manage_posts_columns', array( $this, 'addPostColumn' ) );
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'sortPostColumn' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'managePostColumn' ), 10, 2 );

		/**
		 * Ajax actions
		 */
		add_action( 'wp_ajax_add_featured', array( $this, 'ajaxAddFeatured' ) );
		add_action( 'wp_ajax_nopriv_add_featured', array( $this, 'ajaxAddFeatured' ) );
		add_action( 'wp_ajax_del_featured', array( $this, 'ajaxDelFeatured' ) );
		add_action( 'wp_ajax_nopriv_del_featured', array( $this, 'ajaxDelFeatured' ) );
	}

	/**
	 * Create options menu item
	 */
	public function settingsMenu() {
		add_options_page( 
			'SMC Featured Posts',
			'Featured Posts',
			'manage_options',
			'featured-posts',
			array( $this, 'settingsPage' ) 
		);
	}

	/**
	 * Add sections and fields from Settings API for 
	 * the Featured Posts Settings page
	 */
	public function settingsInit() {
		add_settings_section(
			'section', 
			null, 
			null, 
			'featured-posts'
		);

		add_settings_field(
			'smc_featured_heading',
			'Shortcode Display Heading',
			array( $this, 'displayHeading' ),
			'featured-posts',
			'section'
		);

		add_settings_field(
			'smc_featured_count', 
			'Shortcode Display Limit', 
			array( $this, 'displayCount' ), 
			'featured-posts', 
			'section'
		);

		register_setting( 'section', 'smc_featured_heading' );
		register_setting( 'section', 'smc_featured_count' );
	}

	/**
	 * Display field for shortcode heading
	 */
	public function displayHeading() {
		echo sprintf(
			'<input 
				type="text" 
				name="smc_featured_heading" 
				class="regular-text" 
				id="smc_featured_heading" 
				value="%s" 
			/>',
			get_option('smc_featured_heading')
		);
	}

	/**
	 * Display field for shortcode count
	 */
	public function displayCount() {
		echo sprintf(
			'<input 
				type="text" 
				name="smc_featured_count" 
				class="medium-text" 
				id="smc_featured_count" 
				value="%s" 
			/>',
			get_option('smc_featured_count')
		);
	}

	/**
	 * Adds javascript to required admin screen
	 */
	public function settingsJS( $hook ) {
		if ( 'edit.php' != $hook ) {
			return;
    }

    wp_enqueue_script(
			'ajax-script', 
			plugins_url( '/fp_admin.js', __FILE__ ), 
			array( 'jquery' )
		);

		wp_localize_script(
			'ajax-script', 
			'ajax_object', 
			array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) 
		);
	}

	/** 
	 * Display setgins page content
	 */
	public function settingsPage() {	
		register_setting( 'featured', 'smc_featured_display_count' );

		$file = SMC_FEATURED_ADMIN_DIR . 'settings.php';
		include( $file );
	}

	/**
	 * Add custom Meta Box
	 */
	public function addMetaBox() {
		add_meta_box(
			'smcfpMetaBox', 
			__( 'Set featured post', 'smcfp_admin' ),
			array( $this, 'metaBoxCallback' ), 
			'post', 
			'side',
			'default',
			array(
				'__block_editor_compatible_meta_box' => true,
				'__back_compat_meta_box' => false,
			)
		);
	}

	/**
	 * Save custom Meta Box
	 */
	public function saveMetaBox( $postId, $post, $update ) {
		if ( 
			! isset( $_POST['smc_fp_box_nonce'] ) || 
			! wp_verify_nonce( $_POST['smc_fp_box_nonce'], basename( __FILE__ ) ) 
		) {
			return $postId;
		}

		// User can edit post?
		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return $postId;
		}

		// Not autosaving?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $postId;
		}

		// This is a post?
		if ( 'post' != $post->post_type ) {
			return $postId;
		}

		if ( isset( $_POST['smcfp_is_featured'] ) ) {
			update_post_meta( $postId, SMC_FEATURED_META_OPTION, 1 );
		} else {
			delete_post_meta( $postId, SMC_FEATURED_META_OPTION );
		}
	}

	/**
	 * Render custom Meta Box
	 */
	public function metaBoxCallback( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'smc_fp_box_nonce' );
		$isFeatured = $this->getIsFeatured( $post->ID );

		ob_start();

		echo '<label for="smcfp_is_featured">';

		echo sprintf(
			'<input type="checkbox" 
				id="smcfp_is_featured" 
				name="smcfp_is_featured" 
				value="true"
				%s 
			/>',
			$isFeatured ? 'checked=checked' : ''
		);

		_e( 'Set this post as a Featured Post', 'smcfp' );
		echo '</label> ';

		ob_end_flush();
	}

	/**
	 * Add empty custom column to list
	 */
	public function addPostColumn( $columns ) {
		$columns['featured'] = 'Featured';
		return $columns;
	}

	/**
	 * Add sort function to empty custom column
	 */
	public function sortPostColumn( $columns ) {
		$columns['featured'] = SMC_FEATURED_META_OPTION;
		return $columns;
	}

	/**
	 * Add content to custom column
	 */
	public function managePostColumn( $columnName, $id ) {
		if ( $columnName === 'featured') {
			$isFeatured = $this->getIsFeatured( $id );
			$nonce = wp_create_nonce( 'fp_link_nonce' );

			$action = !$isFeatured 
				? 'add_featured'
				: 'del_featured';

			$text = $isFeatured
				? 'Yes'
				: 'No';

			echo sprintf(
				'<a class="featured-item"
					data-action="%s"
					data-nonce="%s"
					data-post-id="%s"
					href="#">%s</a>',
				$action,
				$nonce,
				$id,
				$text
			);
		}
	}

	/**
	 * Ajax add featured post action
	 */
	public function ajaxAddFeatured() {
		$result = array();
		if ( ! $this->testPreSave( $_POST ) ) {
			$result['status'] = false;
		} else {
			update_post_meta( $_POST['post_id'], SMC_FEATURED_META_OPTION, true );
			$result['status'] = true;
		}

		echo json_encode( $result );
		wp_die();
	}

	/**
	 * Ajax delete featured post action
	 */
	public function ajaxDelFeatured(){
		$result = array();
		if ( ! $this::testPreSave( $_POST ) ) {
			$result['status'] = false;
		} else {
			delete_post_meta( $_POST['post_id'], SMC_FEATURED_META_OPTION );	
			$result['status'] = true;
		}

		echo json_encode( $result );
		wp_die();
	}

	/**
	 * DRY implementation to test save data
	 */
	public function testPreSave( $data ) {
		if ( ! isset( $data['post_id'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $data['post_id'] ) ) {
			return false;
		}

		if ( 
			! isset( $data['fp_link_nonce'] ) || 
			! wp_verify_nonce( $data['fp_link_nonce'], 'fp_link_nonce' ) 
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get the post meta flag for "smc_featured_post"
	 */
	private function getIsFeatured( $id ) {
		$isFeatured = get_post_meta( $id, SMC_FEATURED_META_OPTION, true );
		return $isFeatured;
	}
}