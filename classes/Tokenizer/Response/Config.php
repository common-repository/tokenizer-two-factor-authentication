<?php

/**
 * Tokenizer Connection check response
 * 
 * Returns application information
 *
 * @author Frank Broersen <frank@amsterdamstandard.com>
 * @copyright Amsterdam Standard
 * @homepage http://amsterdamstandard.com
 */

namespace AmsterdamStandard\Tokenizer\Response;

class Config {
    
    public $name;
    
    public $domain;
    
    public $max;
    
    public $server_time;
    
    public $icons;
    
    public function __construct($data)
    {
        if(isset($data['name'])) {
            $this->name = $data['name'];
        }
        if(isset($data['domain'])) {
            $this->domain = $data['domain'];
        }
        if(isset($data['max'])) {
            $this->max = $data['max'];
        }
        if(isset($data['server_time'])) {
            $this->server_time = $data['server_time'];
        }
        if(isset($data['icon'])) {
            $this->icons = $data['icon'];
        }
    }
    
}