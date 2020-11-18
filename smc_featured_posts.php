<?php
/**
 * Plugin name: Featured Posts
 * Description: Display a list of selected posts
 * Author: Kevan Stuart - Sometimes I Code
 * Version: 3.0
 * Licence: GPL
 * 
 * @package SMC_Featured
 * @author Kevan Stuart
 */
defined('ABSPATH') or die('No external script access allowed');

if ( ! function_exists( 'is_admin' ) ) {
  header('Status: 403 Forbidden');
  header('HTTP/1.1 403 Forbidden');
  exit();
}

/**
 * Plugin definitions
 */
define( 'SMC_FEATURED_VERSION', '3.0.0' );
define( 'SMC_MINIMUM_WP_VERSION', '3.2' );
define( 'SMC_FEATURED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMC_FEATURED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMC_FEATURED_ADMIN_DIR', SMC_FEATURED_PLUGIN_DIR . 'admin/' );
define( 'SMC_FEATURED_DISPL_DIR', SMC_FEATURED_PLUGIN_DIR . 'display/' ); 
define( 'SMC_FEATURED_META_OPTION', 'smc_featured_post' );

/**
 * Activation / Deactivation Hooks
 */
register_activation_hook( __FILE__, 'smcActivate' );
register_deactivation_hook( __FILE__, 'smcDeactivate' );

function smcActivate() {
  if ( 
    version_compare( 
      $GLOBALS['wp_version'], 
      SMC_MINIMUM_WP_VERSION, 
      '<'
	  ) 
	) {
		$string = sprintf( 
			'SMC Featured Posts requires WordPress %s or higher', 
			SMC_MINIMUM_WP_VERSION 
		);

		echo __( $string, 'smc');
		wp_die();
	}

	add_option( 'smc_featured_version', SMC_FEATURED_VERSION );
	add_option( 'smc_featured_installed', date( 'Y-m-md H:i:s' ) );
}

function smcDeativate() {
	delete_option( 'smc_featured_version' );
	delete_option( 'smc_featured_installed' );
	delete_option( 'smc_featured_heading' );
	delete_option( 'smc_featured_count' );
}

require_once( SMC_FEATURED_PLUGIN_DIR . 'fp_widget.php' );

add_action( 'widgets_init', function() {
	register_widget( 'FpWidget' );
} );

/** 
 * Run actions for admin and display
 */
if ( is_admin() ) {
	require_once( SMC_FEATURED_ADMIN_DIR . 'fp_admin.php' );

	$admin = new FpAdmin();
	add_action( 'init', array( $admin, 'init' ) );
} else {
	require_once( SMC_FEATURED_DISPL_DIR . 'fp_display.php' );
	
	$display = new FpDisplay();
	add_action( 'init', array( $display, 'init' ) );
}
