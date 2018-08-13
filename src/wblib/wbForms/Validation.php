<?php

namespace wblib\wbForms;

if (!class_exists('\wblib\wbForms\Validation',false))
{
    abstract class Validation extends Base {
    	protected $message = "Form element [{element}] is invalid.";
        protected $allowed = array();

    	public function __construct($message="",$data=null) {
    		if(!empty($message))
    			$this->message = $message;
            if(!empty($data))
                $this->setAllowed($data);
    	}

    	public function getMessage() {
    		return $this->message;
    	}

    	public abstract function isValid($value);

        public function setAllowed($data) {
            $this->allowed = $data;
        }

    }
}
