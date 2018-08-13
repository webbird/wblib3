<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Select',false))
{
    class Select extends \wblib\wbForms\Element
    {
        protected $type     = 'select';
        protected $template = '<select name="{name}" title="{helptext}"{attributes}>{options}</select>';

        public function __construct(string $name,array $properties=array()) {
             // add property
            $this->properties['options'] = array();
            parent::__construct($name,$properties);
        }   // end function __construct()

        /**
         *
         * @access public
         * @return void
         **/
        public function render()
        {
            // make sure to have a correct field name
            if(!empty($this->properties['multiple']) && substr($this->name, -2) != '[]')
			    $this->name .= "[]";

            // selected option
            $selected = null;
            if(!empty($this->properties['value']))
                $selected = $this->properties['value'];
            unset($this->properties['value']);

            // render options
            $options = null;
            if(
                   isset($this->properties['options'])
                && is_array($this->properties['options'])
                && count($this->properties['options'])>0
            ) {
                foreach($this->properties['options'] as $key => $value) {
                    $options .= "<option value=\"$key\""
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Bedingung pruefen. Hash vs. Array
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                             .  ( ($selected==$value || $selected==$key) ? ' selected="selected"' : '' )
                             .  ">" . $this->lang()->t($value) . "</option>";
                }
            }

            $output = str_ireplace(
                array('{options}'),
                array($options),
                parent::render()
            );

            // return result
            return $output;
    	}   // end function render()

        /**
         * set element data
         **/
        public function setData($data) {
            $this->setAttribute('options',$data);
            $this->addValidation(new \wblib\wbForms\Validation\OneOf("",array_keys($data)));
        }   // end function setData()

    }
}