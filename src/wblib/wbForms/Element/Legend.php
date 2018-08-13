<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Legend',false))
{
    class Legend extends \wblib\wbForms\Element
    {
        protected $type     = 'legend';
        protected $template = '<legend name="{name}" title="{helptext}">{label}</legend>';
    }
}