<?php
namespace wblib\wbForms\Validation;

class Alphanum extends wblib\wbForms\Validation\RegExp {
	public function __construct($message="") {
		parent::__construct("/^[a-zA-Z0-9_-]+$/", $message);
	}
}
