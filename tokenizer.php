<?php

/**
 * @package Tokenizer
 * @version 1.0
 */
/*
  Plugin Name: Tokenizer Login
  Plugin URI: http://tokenizer.com
  Description: Secure your wordpress login with Tokenizer
  Author: <a href="http://nl.linkedin.com/in/frankbroersen/" target=_blank>Frank Broersen</a>
  Version: 1.0
  Author URI:
 */


// include tokenizer classes
include 'classes/Tokenizer.php';
include 'classes/Tokenizer/Exception.php';
include 'classes/Tokenizer/Connector.php';
include 'classes/Tokenizer/Response.php';
include 'classes/Tokenizer/Response/Create.php';
include 'classes/Tokenizer/Response/Verify.php';
include 'classes/Tokenizer/Response/Config.php';


if (is_admin()) {
    require_once dirname(__FILE__) . '/admin.php';
}

// Verify tokenizer application details
function tokenizer_verify_app() {
    // 
    $tokenizer = create_tokenizer();
    if ($tokenizer->checkConnection()) {
        return true;
    }
    return false;
}

function create_tokenizer() {
    return new Tokenizer(array(
        'app_id'  => get_option('tokenizer_id'),
        'app_key' => get_option('tokenizer_key'),
        'host'    => 'https://api.tokenizer.com/',
    ));
}

// This just echoes the chosen line, we'll position it later
function tokenizer_login() {
    
    if (!get_option('tokenizer_is_active'))
        return true;

    // 
    $tokenizer = create_tokenizer();

    // we need an app_id and app_key to be active
    if (!$tokenizer->validateOptions()) {
        return true;
    }

    // verify we have a user
    $current_user = wp_get_current_user();
    if (empty($current_user)) {
        $tokenizer->clean();
        return false;
    }

    $tokenizer->setUser($current_user);

    $id = filter_input(INPUT_GET, 'tokenizer_id', FILTER_DEFAULT);
    if (!empty($id)) {
        if ($tokenizer->verifyAuthentication($id) === true) {
            return wp_redirect(get_admin_url());
        } else {
            wp_logout();
            return wp_redirect( site_url( '/wp-login.php?tokenizer=error' ) );
        }
    }

    if (!$tokenizer->isAuthenticated()) {
        if (!$tokenizer->createAuthentication()) {
            return wp_redirect(get_admin_url());
        }
    }
}

function tokenizer_logout() {
    $tokenizer = new Tokenizer();
    $tokenizer->clean();
}

class Tokenizer {

    private $config = array(
        'create' => '{host}v1/authentications.json',
        'verify' => '{host}v1/authentication/{id}.json?app_id={app_id}&app_key={app_key}',
        'check' => '{host}v1/application/{app_id}.json?app_key={app_key}',
    );
    private $user;

    public function __construct($config = array()) {
        // merge config
        foreach ($config as $var => $value) {
            $this->config[$var] = $value;
        }
    }

    /**
     * Validate if we have an app id and key
     * @return boolean
     */
    public function validateOptions() {
        foreach (array('app_id', 'app_key') as $var) {
            if (!isset($this->config[$var]) || trim($this->config[$var]) == '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Remove the cookies
     */
    public function clean() {
        setcookie('TOKENIZER_ID', '', time(), COOKIEPATH, COOKIE_DOMAIN);
        setcookie('TOKENIZER_HASH', '', time(), COOKIEPATH, COOKIE_DOMAIN);
        setcookie('TOKENIZER_LOGIN', '', time(), COOKIEPATH, COOKIE_DOMAIN);
        setcookie('TOKENIZER_TOKEN', '', time(), COOKIEPATH, COOKIE_DOMAIN);
        foreach($_COOKIE as $key => $value) {
            if(substr($key,0,10) == 'wordpress_') {
                unset($_COOKIE[$key]);
            }
        }
    }

    private function buildUrl($url, $data = array()) {
        // replace config vars
        $url = strtr($url, array(
            '{host}' => $this->config['host'],
        ));

        // replace data vars
        $url = strtr($url, $data);

        return $url;
    }

    private function createCookie() {
        // expires in .. seconds
        $now = time();
        $expires = $now + 86400;

        // Store the id + hash in the cookie
        setcookie('TOKENIZER_LOGIN', $now, $expires, COOKIEPATH, COOKIE_DOMAIN);
        setcookie('TOKENIZER_TOKEN', md5($this->config['app_id'] . $now . $this->user->data->user_email), $expires, COOKIEPATH, COOKIE_DOMAIN);

    }

    private function verifyCookies() {
        if ($_COOKIE['TOKENIZER_TOKEN'] == md5($this->config['app_id'] . $_COOKIE['TOKENIZER_LOGIN'] . $this->user->data->user_email)) {
            return true;
        }
        return false;
    }

    public function setUser(WP_User $user) {
        $this->user = $user;
    }

    /**
     * Check if we have a valid cookie
     * @return bool
     */
    public function isAuthenticated() {
        // return true if we have a cookie ?
        if (!empty($_COOKIE['TOKENIZER_LOGIN']) && !empty($_COOKIE['TOKENIZER_TOKEN']) && $this->verifyCookies()) {
            return true;
        }
        return false;
    }

    /**
     * Check if we can make a conneciton with the given information
     */
    public function checkConnection() {
        try {
            $tokenizer = new AmsterdamStandard\Tokenizer(array(
                'app_id' => $this->config['app_id'],
                'app_key' => $this->config['app_key'],
            ));
            $tokenizer->verifyConfig();
            return true;
        } catch (AmsterdamStandard\Tokenizer\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * We are creating a tokenizer authentication
     */
    public function createAuthentication() {
        try {
            $tokenizer = new AmsterdamStandard\Tokenizer(array(
                'app_id' => $this->config['app_id'],
                'app_key' => $this->config['app_key'],
            ));
            $tokenizer->createAuth($this->user->data->user_email, get_admin_url() . '?tokenizer_id=', $redirect = true);
        } catch (AmsterdamStandard\Tokenizer\Exception $e) {
            new WP_Error('400', 'Tokenizer error: ' . $e->getMessage());
        }
    }

    /**
     * We are returning from the tokenizer auth
     */
    public function verifyAuthentication($tokenize) {

        try {
            
            list($id, $expires) = explode('|', $tokenize);

            $tokenizer = new AmsterdamStandard\Tokenizer(array(
                'app_id'  => $this->config['app_id'],
                'app_key' => $this->config['app_key'],
            ));

            if (empty($_COOKIE['TOKENIZER_ID']) || empty($_COOKIE['TOKENIZER_HASH'])) {
                return new WP_Error('400', 'No tokenizer session found');
            }

            if ($_COOKIE['TOKENIZER_ID'] != $id || $_COOKIE['TOKENIZER_HASH'] != md5($id . '|' . $this->user->data->user_email . '|' . $expires)) {
                return new WP_Error('400', 'Tokenizer session does not match url id');
            }
            
            if ($tokenizer->verifyAuth($id)) {
                $this->createCookie();
                return true;
            }
            
        } catch (AmsterdamStandard\Tokenizer\Exception $e) {
            $this->clean();    
            return new WP_Error('400', 'Tokenizer error: ' . $e->getMessage());
        }        
        $this->clean();    
        return new WP_Error('400', 'Tokenizer error: unknown');
    }

}

// we add this in administrator login and logout
add_action('admin_init', 'tokenizer_login', 100, 3);
add_action('wp_logout', 'tokenizer_logout', 100, 3);

add_action('admin_bar_init', function() {
    $tokenizer = create_tokenizer();
    $current_user = wp_get_current_user();
    if (!empty($current_user)) {
        $tokenizer->setUser($current_user);
        if (!$tokenizer->isAuthenticated()) {
            add_filter('show_admin_bar', '__return_false');
        }
    }
}, 100, 3);

function the_login_message( $message ) {
    if ( empty($message) ){
        $error  = filter_input(INPUT_GET, 'tokenizer', FILTER_DEFAULT);
        if($error && $error == 'error') {
            return '<div id="login_error"><strong>ERROR</strong>: Your authentication was not confirmed by Tokenizer</div>';
        } 
        $redirect_to = filter_input(INPUT_GET, 'redirect_to', FILTER_DEFAULT);
        $reauth = filter_input(INPUT_GET, 'reauth', FILTER_DEFAULT);
        if($reauth && strstr($redirect_to, 'tokenizer_id')) {
            return '<div id="login_error"><strong>ERROR</strong>: Your authentication was not confirmed by Tokenizer</div>';
        } 
        return '';
    } else {
        return $message;
    }
}
add_filter( 'login_message', 'the_login_message' );