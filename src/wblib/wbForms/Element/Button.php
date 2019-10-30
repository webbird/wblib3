<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Button',false))
{
    class Button extends \wblib\wbForms\Element\Submit
    {
        public    $type     = 'button'; // default type
        protected $template   = '<button type="{type}" name="{name}" {attributes}>{value}</button>';
#        protected $haslabel = false;

        

        public function getLabel() { return $this->lang()->translate($this->getAttribute('value')); }
    }
}