<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Textarea',false))
{
    class Textarea extends \wblib\wbForms\Element
    {
        protected $template = '<textarea name="{name}" title="{helptext}" {attributes}>{value}</textarea>';
    }
}