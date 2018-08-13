<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Checkbox',false))
{
    class Checkbox extends \wblib\wbForms\Element
    {
        protected $type     = 'checkbox';
        protected $template = '<label class="checkbox-custom" data-initialize="checkbox"><input type="checkbox" name="{name}" title="{helptext}"{checked}{attributes} /> {label}</label>';
        //protected $haslabel = false;

        public function __construct(string $name,array $properties=array()) {
             // add property
            $this->properties['options'] = array();
            parent::__construct($name,$properties);
        }

        /**
         *
         * @access public
         * @return void
         **/
        public function render()
        {
            // make sure to have a correct field name
            if($this->getType()=='checkbox' && substr($this->name, -2) != '[]')
			    $this->name .= "[]";
            if($this->getType()=='radio' && substr($this->name, -2) == '[]')
			    $this->name = substr($this->name,0,-2);

            // checked option(s)
            $checked = null;
            if(!empty($this->properties['value']))
                $checked = $this->properties['value'];

            // make sure 'options' is an array
            if(!isset($this->properties['options']) || empty($this->properties['options']))
                $this->properties['options'] = array($this->lang()->translate('Yes')=>'Y');
            if(!is_array($this->properties['options']))
                $this->properties['options'] = array($this->properties['options']);

            $output = '';
            $curr   = 0;
            $isIndexed = array_values($this->properties['options']) === $this->properties['options'];

            foreach($this->properties['options'] as $key => $val) {
                $id      = $this->properties['id'].'-'.$curr;
                $value   = ($isIndexed ? $val : $key);
                $output .= str_ireplace(
                    array('{id}','{value}','{name}','{helptext}','{attributes}','{label}','{checked}'),
                    array(
                        $id,                        // {id}
                        $value,                     // {value}
                        $this->name,                // {name}
                        $this->getHelptext(),       // {helptext}
                        $this->getAttributes(),
                        $this->lang()->translate($val),
                        ( $checked == $value ? ' checked="checked"' : '' )
                        //'CHECKED '.$checked.' KEY '.$key,
                    ),
                    $this->getTemplate()
                );
                $curr++;
            }

            // return result
            return $output;
    	}   // end function render()
    }
}