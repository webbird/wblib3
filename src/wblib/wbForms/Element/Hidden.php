<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Hidden',false))
{
    class Hidden extends \wblib\wbForms\Element
    {
        public $type = 'hidden';
        public $haslabel = false;
     }
}