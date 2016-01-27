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
 * No external file access
 */
defined('ABSPATH') or die("No script kiddies please!");


/**
 * Plugin constants
 */
define( 'SMC_FEATURED_VERSION', '2.0.0' );
define( 'SMC_MINIMUM_WP_VERSION', '3.2' );
define( 'SMC_FEATURED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMC_FEATURED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMC_FEATURED_ADMIN_DIR', SMC_FEATURED_PLUGIN_DIR . 'admin/' );
define( 'SMC_FEATURED_DISPL_DIR', SMC_FEATURED_PLUGIN_DIR . 'front/' ); 
define( 'SMC_FEATURED_META_OPTION', 'smc_featured_post' );


/**
 * Register activation / deactivation hooks
 */
register_activation_hook( __FILE__, array('Smcfp', 'smcfp_activate') );
register_deactivation_hook( __FILE__, array('Smcfp', 'smcfp_deactivate') );


/**
 * Require frontend end display classes - initialize
 */
require_once( SMC_FEATURED_DISPL_DIR . 'smc.fp-display.php' );
require_once( SMC_FEATURED_DISPL_DIR . 'smc.fp-widget.php' );
add_action( 'init', array('Smcfp', 'init') );


/**
 * Require admin class - initialize
 */
if ( is_admin() )
{
	require_once( SMC_FEATURED_ADMIN_DIR . 'smc.fp-admin.php' );
	add_action( 'init', array( 'Smcfp_Admin', 'init' ) );
}
