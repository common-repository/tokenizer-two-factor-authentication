<?php

/**
 * Tokenizer Response Class
 * 
 * Parses the response of a Tokenizer api call into the corresponding object
 *
 * @author Frank Broersen <frank@amsterdamstandard.com>
 * @copyright Amsterdam Standard
 * @homepage http://amsterdamstandard.com
 */

namespace AmsterdamStandard\Tokenizer;

class Response {
    
    public static function parse($type, $json)
    {
        $data = json_decode($json, 1);    
        
        if(isset($data['status_code'])) {
            throw new Exception("Tokenizer error: " . $data['error_messages']);
        }
        
        if($type == 'create') {
            return new Response\Create($data);
        } 
        if($type == 'verify') {
            return new Response\Verify($data);
        }
        if($type == 'config') {
            return new Response\Config($data);
        }
        
        throw new Exception("Invalid tokenizer response type ($type)");
    }
    
}