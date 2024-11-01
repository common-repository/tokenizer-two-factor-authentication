<?php

/**
 * Tokenizer Connector class
 * 
 * Wraps curl POST and GET
 *
 * @author Frank Broersen <frank@amsterdamstandard.com>
 * @copyright Amsterdam Standard
 * @homepage http://amsterdamstandard.com
 */

namespace AmsterdamStandard\Tokenizer;

class Connector {

    /**
     * The hostname to call
     * @var string 
     */
    private $host;
    
    /**
     * The path on the host to call
     * @var string 
     */
    private $path;

    /**
     * Set the hostname
     * @param string $host
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Set te path
     * @param string $path
     * @param array $replace Optionally replace key value pair in the path
     */
    public function setPath($path, $replace = array()) {
        $this->path = strtr($path,$replace);
    }

    /**
     * Send a POST requst using cURL 
     * @param array $post values to send 
     * @param array $options for cURL 
     * @return mixed 
     */
    public function curlPost($type, array $post = array(), array $options = array()) {
        
        $defaults = array(
            CURLOPT_POST            => 1,
            CURLOPT_HEADER          => 0,
            CURLOPT_URL             => $this->host . $this->path,
            CURLOPT_FRESH_CONNECT   => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_FORBID_REUSE    => 1,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_POSTFIELDS      => http_build_query($post)
        );
        
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return Response::parse($type, $result);
    }

    /**
     * Send a GET requst using cURL 
     * @param array $get values to send 
     * @param array $options for cURL 
     * @return mixed
     */
    public function curlGet($type, array $get = array(), array $options = array()) {
        
        $url  = $this->host . $this->path;
        $url .=  (strpos($url, '?') === FALSE ? '?' : '&');
        $url .=  http_build_query($get);
        
        $defaults = array(
            CURLOPT_URL             => $url,
            CURLOPT_HEADER          => 0,
            CURLOPT_RETURNTRANSFER  => TRUE,
            CURLOPT_TIMEOUT         => 30
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return Response::parse($type, $result);
    }

}
