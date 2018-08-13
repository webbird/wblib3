<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Text',false))
{
    class Text extends \wblib\wbForms\Element
    {
        public $type = 'text';
    }
}