<?php

namespace wblib\wbForms\Validation;

class Required extends \wblib\wbForms\Validation {
	protected $message = "[{element}] is a required field.";
	public function isValid($value) {
        $valid = false;
		if(!is_null($value) && ((!is_array($value) && $value !== "") || (is_array($value) && !empty($value))))
			$valid = true;
		return $valid;
	}   // end function isValid()
}
