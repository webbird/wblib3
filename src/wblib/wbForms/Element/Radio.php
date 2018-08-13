<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Radio',false))
{
    class Radio extends \wblib\wbForms\Element\Checkbox
    {
        protected $type     = 'radio';
        protected $template = '<label><input type="radio" name="{name}" title="{helptext}"{checked}{attributes} /> {label}</label>';
    }
}