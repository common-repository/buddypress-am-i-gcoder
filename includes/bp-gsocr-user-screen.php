<?php

/**
 * bp_gsocr_setup_nav()
 *
 * Sets up the user profile navigation items
 */
function bp_gsocr_setup_user_nav() {
    global $bp;
    
    bp_core_new_nav_item( array(
        'name' => __( 'Application Logs', 'bp-gsocr' ),
        'slug' => $bp->gsocr->slug,
        'position' => 80,
        'screen_function' => 'gsocr_latest_user_api_screen',
        'show_for_displayed_user' => true,
        'default_subnav_slug' => 'status'));

    bp_core_new_subnav_item( array(
        'name' => __( 'Latest entries', 'bp-gsocr' ),
        'slug' => 'status',
        'parent_slug' => $bp->gsocr->slug,
        'parent_url' => $bp->loggedin_user->domain . $bp->gsocr->slug . '/',
        'screen_function' => 'gsocr_latest_user_api_screen',
        'show_for_displayed_user' => true,
        'position' => 10));
}
//add_action( 'bp_setup_nav', 'bp_gsocr_setup_user_nav' ); // doesn't work
add_action( 'wp', 'bp_gsocr_setup_user_nav', 2 );
add_action( 'admin_menu', 'bp_gsocr_setup_user_nav', 2 );

/**
 * Get the latest logs related to this user and show them
 */
function gsocr_latest_user_api_screen() {
	global $bp;
	
	if($bp->action_variables[0] == 'update') {
		gsocr_api_update();
	}
	
	if ( !bp_is_my_profile() ) {
		bp_core_add_message( __( 'Are you lost? You can\'t browse others logs.', 'bp-gsocr' ), 'error' );
		bp_core_redirect( $bp->loggedin_user->domain . $bp->gsocr->slug . '/latest' );
	}
    do_action( 'gsocr_latest_user_logs' );
    add_action( 'bp_template_title', 'gsocr_latest_user_logs_title' );
    add_action( 'bp_template_content', 'gsocr_latest_user_logs_content' );
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function gsocr_latest_user_logs_title() {
    _e('Latest entries', 'bp-gsocr');
}

function gsocr_latest_user_logs_content() {
    global $wpdb, $bp;
    $results = $wpdb->get_results($wpdb->prepare("SELECT app_data,
                                  app_timestamp,
                                  app_key_name,
                                  user_id
                                  FROM {$bp->gsocr->logs_table_name} LEFT JOIN {$bp->gsocr->keys_table_name}
                                  ON {$bp->gsocr->logs_table_name}.app_key_id = {$bp->gsocr->keys_table_name}.id
                                  WHERE user_id = {$bp->loggedin_user->id}
                                  LIMIT 10;"));
    if(count($results)) {
        echo '<ol id="gsocr-user-log">';
        foreach($results as $log) {
            printf(__('<li class="gsocr-log-item">
            <span class="gsocr-log-data">%s</span> 
            from <span class="gsocr-app-name">%s</span>, 
            on <span class="gsocr-ts">%s</span>.'),
            $log->app_data,
            $log->app_key_name,
            $log->app_timestamp);
        }
        echo '</ol>';
	}
}