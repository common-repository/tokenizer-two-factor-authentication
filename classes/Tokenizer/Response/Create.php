<?php

/**
 * Tokenizer Token Create response
 * 
 * Returns token id and waiting page url
 *
 * @author Frank Broersen <frank@amsterdamstandard.com>
 * @copyright Amsterdam Standard
 * @homepage http://amsterdamstandard.com
 */

namespace AmsterdamStandard\Tokenizer\Response;

class Create {
    
    public $id;
    
    public $wait_url;
    
    public function __construct($data)
    {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        if(isset($data['wait_url'])) {
            $this->wait_url = $data['wait_url'];
        }
    }
    
}