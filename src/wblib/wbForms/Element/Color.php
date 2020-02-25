<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Color',false))
{
    class Color extends \wblib\wbForms\Element
    {
        public $type = 'text';
        public $properties = array(
            'pattern' => '#[a-f0-9]{6}',
        );
    }
}