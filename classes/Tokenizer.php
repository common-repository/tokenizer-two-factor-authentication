<?php

namespace AmsterdamStandard;

/**
 * PHP Tokenizer Class
 *
 * @author Frank Broersen <frank@amsterdamstandard.com>
 * @copyright Amsterdam Standard
 * @homepage http://amsterdamstandard.com
 */
use AmsterdamStandard\Tokenizer\Exception as Exception,
    AmsterdamStandard\Tokenizer\Connector as Connector;

class Tokenizer {

    /**
     * Basic Tokenize configuration
     * @var array
     */
    private $config = array(
        'host'   => 'https://api.tokenizer.com/',
        'create' => 'v1/authentications.json',
        'verify' => 'v1/authentication/{id}.json',
        'check'  => 'v1/application/{app_id}.json?app_key={app_key}',
    );

    /**
     * Constructor
     * @param array $config array that should contain app_id, app_key and host
     * @throws \Exception
     */
    public function __construct($config = array()) {
        foreach ($config as $var => $value) {
            $this->config[$var] = $value;
        }
        if(!$this->validateConfig()) {
            throw new Exception("Please supply the correct tokenizer config [app_id,app_key,host]");
        }
    }

    /**
     * Validate if we have an app id, key and host
     * @return boolean
     */
    private function validateConfig() {
        foreach (array('app_id', 'app_key', 'host', 'create', 'verify') as $var) {
            if (!isset($this->config[$var]) || trim($this->config[$var]) == '') {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Create a tokenizer authentication
     * @param string $email The email address of the user you want to login
     * @param string $returnUrl The url of the script that checks the token
     * @param bool $redirect Auto redirect the user to the waiting page
     */
    public function createAuth($email, $returnUrl, $redirect = true)
    {
        $connector = new Connector();
        $connector->setHost($this->config['host']);
        $connector->setPath($this->config['create']);
        
        // Create authentication
        $response = $connector->curlPost('create',array(
            'app_id'    => $this->config['app_id'],
            'app_key'   => $this->config['app_key'],
            'usr_email' => $email,
        ));
        
        // Store the id + hash in the cookie
        $expires = time() + 180;
        setcookie('TOKENIZER_ID'  ,$response->id, $expires, COOKIEPATH, COOKIE_DOMAIN);
        setcookie('TOKENIZER_HASH',md5($response->id . '|' . $email . '|' . $expires), $expires, COOKIEPATH, COOKIE_DOMAIN);

        // Build waiting page url
        $uri = $response->wait_url . '?redirect=' . $returnUrl . $response->id  . '|' . $expires;
                
        // Do a direct redirect
        if($redirect === true) {
            header("Location: $uri");
            exit;
        }
        
        return $redirect;    
    }    
    
    /**
     * Verify if the id of a given token has been confirmed by the user
     * @param int $id
     */
    public function verifyAuth($id)
    {
        $connector = new Connector();
        $connector->setHost($this->config['host']);
        $connector->setPath($this->config['verify'],array('{id}' => $id));
        
        // Verify authentication
        $response = $connector->curlGet('verify', array(
            'app_id'    => $this->config['app_id'],
            'app_key'   => $this->config['app_key'],
        ));
        
        if($response->state == 'accepted') {
            return true;
        }
        return false;
    }   
          
    /**
     * Verify that the application details are working
     */
    public function verifyConfig()
    {
        $connector = new Connector();
        $connector->setHost($this->config['host']);
        $connector->setPath($this->config['check'],array(
            '{app_id}'    => $this->config['app_id'],
            '{app_key}'   => $this->config['app_key'],
        ));
        
        // 
        return $connector->curlGet('config');            
    }   
    
    /**
     * Set session value
     * @param string $name the session name
     * @param mixed $value the session value
     */
    public function setSession($name, $value) 
    {
        $_SESSION[$name] = $value;
    } 
    
    /**
     * Get session value
     * @param string $name the session name
     * @return mixed the session value
     */
    public function getSession($name) 
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }
    

}
