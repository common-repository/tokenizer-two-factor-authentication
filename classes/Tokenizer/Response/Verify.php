<?php

/**
 * Tokenizer Token Verification response
 * 
 * Returns token state
 *
 * @author Frank Broersen <frank@amsterdamstandard.com>
 * @copyright Amsterdam Standard
 * @homepage http://amsterdamstandard.com
 */

namespace AmsterdamStandard\Tokenizer\Response;

class Verify {
    
    public $state;
    
    public function __construct($data)
    {        
        if(isset($data['state'])) {
            $this->state = $data['state'];
        }
    }
    
}