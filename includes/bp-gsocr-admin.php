<?php

/**
 * bp_gsocr_admin()
 *
 * Add applications and assign api keys
 */
function bp_gsocr_admin() {
    if ( isset( $_POST['submit'] ) && check_admin_referer('gsocr-addapp') ) {
        if(bp_gsocr_admin_newapp($_POST['gsocr-newapp-name'], $_POST['gsocr-newapp-uri']))
            $updated = true;
    }
    
    if ( isset( $_GET['delete_app'] ) && check_admin_referer('gsocr-delapp') ) {
        if(bp_gsocr_admin_delapp($_GET['delete_app']))
            $deleted = true;
    }

?>
    <div class="wrap">
        <h2><?php _e( 'Add an application', 'bp-gsocr' ) ?></h2>
        <br />

        <?php if ( isset($updated) ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'New application added.', 'bp-gsocr' ) . "</p></div>" ?><?php endif; ?>
        <?php if ( isset($deleted) ) : ?><?php echo "<div id='message' class='updated fade'><p>" . __( 'Application deleted.', 'bp-gsocr' ) . "</p></div>" ?><?php endif; ?>

        <form action="<?php echo site_url() . '/wp-admin/admin.php?page=bp-gsocr-newapp' ?>" name="gsocr-newapp-form" id="gsocr-newapp-form" method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="target_uri"><?php _e( 'Application name', 'bp-gsocr' ) ?></label></th>
                    <td>
                        <input name="gsocr-newapp-name" type="text" id="gsocr-newapp-name" value="" size="60" />
                        <small><?php _e('Some identifiation name, like: `My Fancy App`', 'bp-gsocr')?></small>
                    </td>
                </tr>
                    <th scope="row"><label for="target_uri"><?php _e( 'Application URI', 'bp-gsocr' ) ?></label></th>
                    <td>
                        <input name="gsocr-newapp-uri" type="text" id="gsocr-newapp-uri" value="" size="60" />
                        <small><?php _e('Just the hostname of the application, like: `app.domain.tld`', 'bp-gsocr')?></small>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" value="<?php _e( 'Register application', 'bp-gsocr' ) ?>"/>
            </p>
            <?php wp_nonce_field('gsocr-addapp');?>
        </form>
        <h2><?php _e( 'All applications', 'bp-gsocr' ) ?></h2>
        <br />
        <?=bp_gsocr_admin_listapps();?>
    </div>
<?php
}

/**
 * Handler of the new apps, stores and generates the api keys
 */
function bp_gsocr_admin_newapp( $name, $uri ) {
    global $wpdb, $bp;
    $new_app[] = $uri;
    $new_app[] = $name;
    $new_app[] = date('Y-m-d H:i:s');
    $new_app[] = sha1($new_app[0].$new_app[1].$new_app[2]);
    
    $result = $wpdb->query( $wpdb->prepare(
                                           "INSERT INTO {$bp->gsocr->keys_table_name} ( 
                                                `app_key`,
                                                `app_key_name`,
                                                `app_key_uri`,
                                                `app_key_creation_date`
                                            ) VALUES ( 
                                                    %s, %s, %s, %s
                                            )", 
                                                $new_app[3],
                                                $new_app[1],
                                                $new_app[0],
                                                $new_app[2]
                                            ) );
    if(!$result)
        return false;
    else
        return true;
}

/**
 * List existing keys in the db
 */

/**
 * Handler of the new apps, stores and generates the api keys
 */
function bp_gsocr_admin_listapps() {
    global $wpdb, $bp;
    $allapps = '<p>'.__('Sorry, no applications yet.', 'bp-gsocr').'</p>';
    
    $result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$bp->gsocr->keys_table_name}"));
    if(count($result)) {
        $allapps = '<ol id="gsocr-apps-list">';
        foreach($result as $app) {
            printf(__('<li class="gsocr-app">
                    <span class="gsocr-app-name">%s</span>
                    with api key: <span class="gsocr-app-key">%s</span>
                    from <span class="gsocr-app-uri">%s</span>,
                    added on <span class="gsocr-ts">%s</span>.
                    <a href="%s" class="button">Delete</a>', 'bp-gsocr'),
                    $app->app_key_name,
                    $app->app_key,
                    $app->app_key_uri,
                    $app->app_key_creation_date,
                    wp_nonce_url(site_url() . '/wp-admin/admin.php?page=bp-gsocr-newapp&amp;delete_app='.$app->id, 'gsocr-delapp'));
        }
        echo '</ol>'; 
    }
    
    return $allapps;
}

/**
 * Handle application deletion
 */
function bp_gsocr_admin_delapp($id) {
    global $wpdb, $bp;
    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$bp->gsocr->keys_table_name} WHERE id = '%d';", $id));
    if(!$result)
        return false;
    else
        return true;
}

?>