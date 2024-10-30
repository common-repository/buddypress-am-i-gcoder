<?php
define ( 'BP_GSOCR_IS_INSTALLED', 1 );
define ( 'BP_GSOCR_VERSION', 1);
define ( 'BP_GSOCR_DB_VERSION', 1 );

/* This is our app slug */
if ( !defined( 'BP_GSOCR_SLUG' ) )
    define ( 'BP_GSOCR_SLUG', 'api' );

/* l18n stuff */
if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) )
    load_textdomain( 'bp-gsocr', dirname( __FILE__ ) . '/buddypress-bp-am-i-gsocr/languages/' . get_locale() . '.mo' );

/**
 * bp_gsocr_setup_globals()
 *
 * Sets up global variables.
 */
function bp_gsocr_setup_globals() {
    global $bp, $wpdb;
    
    /* For internal identification */
    $bp->gsocr->id = 'api';
    $bp->gsocr->keys_table_name = $wpdb->base_prefix . 'bp_api_keys';
    $bp->gsocr->logs_table_name = $wpdb->base_prefix . 'bp_api_logs';
    $bp->gsocr->slug = BP_GSOCR_SLUG;
    
    /* Register this in the active components array */
    $bp->active_components[$bp->gsocr->slug] = $bp->gsocr->id;
}
add_action( 'wp', 'bp_gsocr_setup_globals', 2 );
add_action( 'admin_menu', 'bp_gsocr_setup_globals', 2 );

/**
 * bp_gsocr_add_admin_menu()
 *
 * Add a WordPress wp-admin admin menu for under the "BuddyPress" menu.
 */
function bp_gsocr_add_admin_menu() {
    global $bp;

    if ( !$bp->loggedin_user->is_site_admin )
        return false;

    require ( dirname( __FILE__ ) . '/bp-gsocr-admin.php' );

    add_submenu_page( 'bp-general-settings', __( 'Applications', 'bp-gsocr' ), __( 'Applications', 'bp-gsocr' ), 'manage_options', 'bp-gsocr-newapp', 'bp_gsocr_admin' );
}
add_action( 'admin_menu', 'bp_gsocr_add_admin_menu' );

/* Load the BP user related part */
require ( dirname( __FILE__ ) . '/bp-gsocr-user-screen.php' );
require ( dirname( __FILE__ ) . '/bp-gsocr-api.php' );

/**
 * TODO
 * Add a screen for bp users and show latest entries from api_log
 */

/**
 * TODO
 * Add notification support for bp users
 */