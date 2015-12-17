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
 * No external file access
 */
defined('ABSPATH') or die("No script kiddies please!");


/**
 * Plugin constants
 */
define( 'SMC_FEATURED_VERSION', '2.0.0' );
define( 'SMC_FEATURED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMC_FEATURED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMC_FEATURED_ADMIN_DIR', SMC_FEATURED_PLUGIN_DIR . '/admin' );
define( 'SMC_FEATURED_DISPL_DIR', SMC_FEATURED_PLUGIN_DIR . '/front' ); 
define( 'SMC_FEATURED_META_OPTION', 'smc_featured_post' );


/**
 * Activate plugin
 */
function smc_featured_activate()
{
    smc_setup();
}
register_activation_hook(__FILE__, 'smc_featured_activate');


/**
 * Deactivate plugin and remove data
 */
function smc_featured_deactivate()
{
    smc_takedown();
}
register_deactivation_hook(__FILE__, 'smc_featured_deactivate');


/**
 * Setup options
 */
function sometimes_db_setup()
{
    add_option( 'smc_featured_version', SMC_FEATURED_VERSION );
    add_option( 'smc_featured_installed', date('Y-m-d H:i:s') );
}


/**
 * Takedown database tables
 */
function sometimes_db_takedown()
{
    delete_option( 'smc_featured_version' );
    delete_option( 'smc_featured_installed' );
}


/**
 * Initialize admin class
 */ 
/*if ( is_admin() )
{
    require_once SMC_FEATURED_ADMIN_DIR . '/smc_admin.php';
    $admin = new SMCFP_admin();
}*/

/**
 * Initialize frontend class
 */
require_once SMC_FEATURED_DISPL_DIR . '/smc_display.php';
$display = new SMCFP_display();
