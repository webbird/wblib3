<?php

namespace wblib\wbForms\Validation;

class OneOf extends \wblib\wbForms\Validation {
	protected $message = "The given value [{value}] is not allowed for form element [{element}].";

	public function isValid($value) {
        if(empty($value)) // should be handeled by Required validator
            return true;
        if(!is_array($this->allowed) || empty($this->allowed))
            return false;
        elseif(!in_array($value,$this->allowed))
            return false;
        else
            return true;
	}   // end function isValid()
}
