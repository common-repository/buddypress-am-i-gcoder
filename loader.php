<?php
/*
Plugin Name: Am I a GSoCer?
Plugin URI: http://profiles.wordpress.org/c00l2sv
Description: Hacks to show off some skills when applying for WordPress GSoC 2010
Version: 1.0
Revision Date: April 29, 2010
Requires at least: WordPress 2.9.2, BuddyPress 1.2.3
Tested up to: WordPress 2.9.2, BuddyPress 1.2.3
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: Stas Sușcov
Author URI: http://profiles.wordpress.org/c00l2sv
Site Wide Only: true
*/

/* Only load the component if BuddyPress is loaded and initialized. */
function bp_gsocr_init() {
	require( dirname( __FILE__ ) . '/includes/bp-gsocr-core.php' );
}
add_action( 'bp_init', 'bp_gsocr_init' );

/* Put setup procedures to be run when the plugin is activated in the following function */
function bp_gsocr_activate() {
    global $wpdb;
    
    if ( !empty($wpdb->charset) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

    $sql[] = "CREATE TABLE `{$wpdb->base_prefix}bp_api_keys` (
                            `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `app_key` varchar(40) NOT NULL,
                            `app_key_name` varchar(100) NOT NULL,
                            `app_key_uri` varchar(100) NOT NULL,
                            `app_key_creation_date` TIMESTAMP
                    ) {$charset_collate};";
    
    $sql[] = "CREATE TABLE `{$wpdb->base_prefix}bp_api_logs` (
                            `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `app_key_id` bigint(20) NOT NULL,
                            `user_id` bigint(20) NOT NULL,
                            `app_action` varchar(10) NOT NULL,
                            `app_data` varchar(1000) NOT NULL,
                            `app_timestamp` TIMESTAMP DEFAULT 0
                    ) {$charset_collate};";
    
    require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
    dbDelta($sql);
    update_site_option( 'bp-gsocr-db-version', BP_GSOCR_DB_VERSION );
    var_dump('<pre>'.$sql.'</pre>');
}
register_activation_hook( __FILE__, 'bp_gsocr_activate' );

function bp_gsocr_deactivate() {
    //TODO
}
register_deactivation_hook( __FILE__, 'bp_gsocr_deactivate' );
?>