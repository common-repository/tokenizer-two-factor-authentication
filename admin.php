<?php

add_action('admin_menu', 'tokenizer_admin_menu');

function tokenizer_nonce_field($action = -1) {
    return wp_nonce_field($action);
}

$tokenizer_nonce = 'tokenizer-update-key';

function tokenizer_plugin_action_links($links, $file) {
    if ($file == plugin_basename(dirname(__FILE__) . '/tokenizer.php')) {
        $links[] = '<a href="' . admin_url('admin.php?page=tokenizer-key-config') . '">' . __('Settings') . '</a>';
    }
    return $links;
}

add_filter('plugin_action_links', 'tokenizer_plugin_action_links', 10, 2);

/**
 * Display Tokenize rsettings page
 * @global string $tokenizer_nonce
 * @global type $current_user
 */
function tokenizer_conf() {
    global $tokenizer_nonce, $current_user;

    $saved_ok = $do_reload = false;

    $messages = array();

    if (isset($_POST['submit'])) {
        
        if (function_exists('current_user_can') && !current_user_can('manage_options'))
            die(__('Cheatin&#8217; uh?'));

        if (isset($_POST['tokenizer_id'])) {
            update_option('tokenizer_id', $_POST['tokenizer_id']);
        } else {
            update_option('tokenizer_id', '');
        }
        if (isset($_POST['tokenizer_key'])) {
            update_option('tokenizer_key', $_POST['tokenizer_key']);
        } else {
            update_option('tokenizer_key', '');
        }
                
        if(get_option('tokenizer_id') && get_option('tokenizer_key') && tokenizer_verify_app()) {
            update_option('tokenizer_is_active',true);
            $do_reload = true;
            $messages[] = array('class' => 'updated fade', 'text' => __('Your app id and key have been stored, you will be asked to login again.'));
        } else {
            $messages[] = array('class' => 'error fade', 'text' => __('Your app info is incorrect!'));
            update_option('tokenizer_is_active',false);
        }
    }
    ?>
    <div class="wrap">
        <?php if($do_reload == true):?>
        <script>
        setTimeout(function(){
            window.location = window.location.href;
        },2000);
        </script>
        <?php endif;?>
        <h2 class="ak-header"><?php _e('Tokenizer app settings'); ?></h2>
        <p class="need-key description"><?php printf(__('You must enter a valid Tokenizer app id and key here. If you do not have a tokenizer app, <a href="%s" target="_blank">register your app and domain here</a>'), 'http://tokenizer.com'); ?></p>
        <div>
            <?php if (!empty($_POST['submit']) && $saved_ok) : ?>
                <div id="message" class="updated fade"><p><strong><?php _e('Settings saved, you will be asked to login tokenizer now.') ?></strong></p></div>
            <?php endif; ?>                
            <?php foreach ($messages as $message) : ?>
                <div class="<?php echo $message['class']; ?>"><p><strong><?php echo $message['text']; ?></strong></p></div>
            <?php endforeach; ?>
            <form action="" method="post" id="tokenizer-conf">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th><label for="tokenizer_id"><?php _e('Tokenizer app id'); ?></label></th>
                            <td>
                                <input id="tokenizer_id" name="tokenizer_id" type="text" value="<?php echo esc_attr(get_option('tokenizer_id')); ?>" class="regular-text code <?php echo $key_status; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="tokenizer_key"><?php _e('Tokenizer app key'); ?></label></th>
                            <td>
                                <input id="tokenizer_key" name="tokenizer_key" type="text" value="<?php echo esc_attr(get_option('tokenizer_key')); ?>" class="regular-text code <?php echo $key_status; ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php tokenizer_nonce_field($tokenizer_nonce) ?>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>">
                </p>
            </form>	
        </div>
        <p><br/></p>        
        <h2 class="ak-header"><?php _e('Support'); ?></h2>
        <p>For any support or questions, visit <a href="https://tokenizer.com" target="_blank">tokenizer.com</a> or email me on <a target="_blank" href="mailto:frank@tokenizer.com">frank@tokenizer.com</a></p>
    </div>
    <?php
}


function is_tokenizer_configured() {
   
    global $pagenow;

    if ( 'admin.php' == $pagenow && isset($_GET['page']) && $_GET['page'] == 'tokenizer-key-config' )
        return;	

    if(!get_option('tokenizer_is_active'))
        echo "<div class='update-nag'>Tokenizer has been activated, but is not configured. <a href='admin.php?page=tokenizer-key-config'>Configure it here.</a></div>";
    
}
add_action( 'admin_notices', 'is_tokenizer_configured', 3 );

/**
 * Add Tokenizer menu
 */
function tokenizer_admin_menu() {
    if (class_exists('Jetpack')) {
        add_action('jetpack_admin_menu', 'tokenizer_load_menu');
    } else {
        tokenizer_load_menu();
    }
}

/**
 * Tokenizer menu
 */
function tokenizer_load_menu() {
    if (class_exists('Jetpack')) {
        add_submenu_page('jetpack', __('Tokenizer'), __('Tokenizer'), 'manage_options', 'tokenizer-key-config', 'tokenizer_conf');
        add_submenu_page('jetpack', __('Tokenizer Stats'), __('Tokenizer Stats'), 'manage_options', 'tokenizer-stats-display', 'tokenizer_stats_display');
    } else {
        add_submenu_page('plugins.php', __('Tokenizer'), __('Tokenizer'), 'manage_options', 'tokenizer-key-config', 'tokenizer_conf');
        add_submenu_page('index.php', __('Tokenizer Stats'), __('Tokenizer Stats'), 'manage_options', 'tokenizer-stats-display', 'tokenizer_stats_display');
    }
}
