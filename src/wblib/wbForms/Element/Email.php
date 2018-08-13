<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Email',false))
{
    class Email extends \wblib\wbForms\Element
    {
        public $type = 'email';
    }
}