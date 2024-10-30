<?php

/**
 * gsocr_api_update()
 *
 * Will be used for api/update slug
 */
function gsocr_api_update() {
	global $bp;
	
	$request['api_key'] = filter_var($_POST['api_key'], FILTER_SANITIZE_STRING);
	$request['username'] = $bp->displayed_user->userdata->user_login;
	$request['app_data'] = filter_var($_POST['data'], FILTER_SANITIZE_STRING);
	$request['app_data_uri'] = esc_url($_POST['data_uri'], null, 'db');
	$request['timestamp'] = bp_gsocr_api_checkdate($_POST['ts']);
	$request['user_id'] = $bp->displayed_user->id;
	$request['app_key_id'] = bp_gsocr_api_get_appdetails($request['api_key'], true);
	$request['app_key_name'] = bp_gsocr_api_get_appdetails($request['api_key'], false, true);
	$request['app_record_type'] = strtolower(str_replace(' ', '_', $request['app_key_name'])); //TODO, use some regexp
	$request['app_action'] = $_SERVER['REQUEST_METHOD'];
	
	if($bp->displayed_user->userdata->user_login != $_POST['username'] || !empty($request['app_key_id'])) {
		die(json_encode(bp_gsocr_api_new_action($request)));
	}
}

/**
 * bp_gsocr_api_checkdate()
 *
 * Validate a timestamp
 */
function bp_gsocr_api_checkdate($ts) {
	$ts = trim($ts);
	$time = strtotime($ts);
	
	$is_ok = date("Y-m-d H:i:s", $time) == $ts;
	if($is_ok)
		return $ts;
	else
		return false;
}

/**
 * bp_gsocr_api_get_appid()
 *
 * Get application id
 */
function bp_gsocr_api_get_appdetails($key, $get_id = true, $get_name = false) {
	global $wpdb, $bp;
	$result = $wpdb->get_results($wpdb->prepare("
								SELECT id, app_key_name 
								FROM {$bp->gsocr->keys_table_name} 
								WHERE app_key='$key'"));
	if($get_id)
		return $result[0]->id;
	else
		return $result[0]->app_key_name;
}

/**
 * bp_gsocr_api_new_action()
 *
 * Registers the activity
 */
function bp_gsocr_api_new_action($data) {
	$ok = false;
	if(bp_gsocr_record_activity(array(
		'type' => $data['app_record_type'],
		'user_id' => $data['user_id'],
		'action' => $data['app_data'],
		'recorded_time' => $data['timestamp'],
		'primary_link' => $request['app_data_uri'])))
		if(bp_gsocr_api_new_log($data))
			$ok = true;
	return array("status" => $ok);
}

/**
 * bp_gsocr_api_new_log()
 *
 * Logs the api call
 */
function bp_gsocr_api_new_log($data) {
	global $wpdb, $bp;
	$wpdb->query($wpdb->prepare("
								INSERT INTO {$bp->gsocr->logs_table_name} 
								(app_action, app_data, user_id, app_timestamp, app_key_id)
								VALUES ('{$data['app_action']}', 
										'{$data['app_data']} {$data['app_data_uri']}', 
										'{$data['user_id']}',
										'{$data['timestamp']}',
										'{$data['app_key_id']}');"));
	return true;
}

/**
 * bp_gsocr_record_activity()
 *
 * Record activity items for api
 */
function bp_gsocr_record_activity( $args = '' ) {
    global $bp;

    if ( !function_exists( 'bp_activity_add' ) )
            return false;

    $defaults = array(
        'id' => false,
        'user_id' => '',
        'action' => '',
        'content' => '',
        'primary_link' => '', 
        'component' => $bp->gsocr->id,
        'type' => false,
        'item_id' => false,
        'secondary_item_id' => false,
        'recorded_time' => '',
        'hide_sitewide' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    return bp_activity_add( array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}


?>