<?php
namespace wblib\wbForms\Validation;

class RegExp extends wblib\wbForms\Validation {
	protected $message = "Form element [{element}] contains invalid characters.";
	protected $pattern;
	public function __construct($pattern,$message="") {
		$this->pattern = $pattern;
        parent::__construct($message);
	}   // end function __construct()
}
