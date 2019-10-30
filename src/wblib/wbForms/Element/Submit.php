<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Submit',false))
{
    class Submit extends \wblib\wbForms\Element
    {
        public    $type     = 'submit'; // default type
        public function __construct(string $name,array $properties=array())
        {
            // check for label; use 'name' as fallback
            $btntext = '';
            if(isset($properties['label']) && strlen($properties['label']))
            {
                $properties['value'] = $properties['label'];
            } else {
                $properties['value'] = $this->humanize($name);
            }
            if(isset($properties['type']) && strlen($properties['type'])) {
                $this->type = $properties['type'];
                unset($properties['type']);
            }
            parent::__construct($name,$properties);
        }
    }
}