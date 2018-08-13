<?php

namespace wblib\wbForms;

if (!class_exists('\wblib\wbForms\Element',false))
{
    abstract class Element extends Base
    {
        /**
         * default type
         **/
        protected $type       = 'text';
        protected $name       = NULL;
        protected $id         = NULL;
        protected $label      = NULL;
        protected $haslabel   = true;
        protected $helptext   = NULL;
        protected $validation = array();
        public    $view_opt   = array();
        /**
         * view
         **/
        protected $view;
        /**
         * default output template; may be overridden by the view handler
         **/
        protected $template   = '<input type="{type}" name="{name}" title="{helptext}" value="{value}" {attributes} />';
        /**
         * config
         **/
        public $properties  = array(
            'aria-describedby'  => NULL,
            'aria-required'     => NULL,
            'disabled'          => false,
            'id'                => NULL,
            'pattern'           => NULL,
            'placeholder'       => NULL,
            'readonly'          => NULL,
            'required'          => NULL,
            'type'              => NULL,
            'value'             => NULL,
        );
        /**
         * universal attributes
         **/
        protected $universal = array(
            'accesskey'       => NULL,
            'class'           => NULL,
            'contenteditable' => NULL,
            'contextmenu'     => NULL,
            'dir'             => NULL,
            'draggable'       => NULL,
            'dropzone'        => NULL,
            'hidden'          => NULL,
            'id'              => NULL,
            'lang'            => NULL,
            'spellcheck'      => NULL,
            'style'           => NULL,
            'tabindex'        => NULL,
            'title'           => NULL,
            // eventhandler; should not be used, but are allowed anyway
            'onblur'          => NULL,
            'onchange'        => NULL,
            'onclick'         => NULL,
            'onfocus'         => NULL,
            'onselect'        => NULL,
        );

        /**
         *
         * @access public
         * @return
         **/
        public function __construct(string $name,array $properties = [])
        {
            // check name
            if (empty($name)) {
                $name = self::generateName();
            }
            $this->name  = $name;

            // check for unique ID and name
            if(empty($properties['id'])) {
                $properties['id'] = 'form_item_'.$name;
            }
            $this->id    = $properties['id'];

            // check for label; use 'name' as fallback
            if($this->hasLabel()) {
                if(isset($properties['label']) && strlen($properties['label']))
                {
                    $this->label = $properties['label'];
                } else {
                    $this->label = ucfirst($name);
                }
            }

            // check for helptext
            if(isset($properties['helptext']) && strlen($properties['helptext']))
            {
                $this->helptext = $properties['helptext'];
                unset($properties['helptext']);
            }

            // add validation for required fields
            if(isset($properties['required']) && $properties['required']) {
                $this->addValidation(new Validation\Required());
            }
            
            // check for view options
            if(isset($properties['view']))
            {
                $this->view_opt = $properties['view'];
                unset($properties['view']);
            }

            if(count($properties)>0) {
                $this->configure($properties);
            }
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
    	public function addValidation($validation)
        {
    		if(!\is_array($validation))
                {
                    $validation = array($validation);
                }
    		foreach($validation as $object) {
                    if($object instanceof Validation) {
                        $this->validation[] = $object;
                    }
    		}
    	}   // end function addValidation()


        /**
         * check assigned validation rules for Validation\Required
         *
         * @access public
         * @return boolean
         **/
        public function isRequired()
        {
            if (!empty($this->validation)) {
                foreach ($this->validation as $validation) {
                    if ($validation instanceof Validation\Required) {
                        return true;
                    }
                }
            }
            return false;
        }   // end function isRequired()

        public function isValid($value)
        {
            $valid = true;
            if (!empty($this->validation)) {
                foreach ($this->validation as $validation) {
                    if (!$validation->isValid($value)) {
                        $valid = false;
                        $this->addError(
                            str_ireplace(
                                array('{value}', 
                                '{element}'), 
                                array($value, $this->getName()), 
                                self::lang()->translate($validation->getMessage())
                            )
                        );
                    }
                }
            }
            return $valid;
        }   // end function isValid()
        
        /**
         * render element
         *
         * @access public
         * @return string
         **/
        public function render() {
            $output = str_ireplace(
                array('{type}','{name}','{attributes}','{helptext}','{value}'),
                array($this->type,$this->name,$this->getAttributes(),$this->getHelptext(),$this->getAttribute('value')),
                $this->template
            );
            return $output;
    	}   // end function render()

        /**
         * convenience methods
         **/
        public function getClass()             { return $this->class; }
        public function getHelptext()          { return (null != $this->helptext ? $this->lang()->translate($this->helptext) : ''); }
        public function getID()                { return $this->id; }
        public function getLabel()             { return $this->lang()->translate($this->label); }
        public function getName()              { return $this->name; }
        public function getType()              { return $this->type; }
        public function getValue()             { return $this->properties['value']; }
        public function hasLabel()             { return $this->haslabel; }
        public function setData($data)         { $this->configure(array('options'=>$data)); }
        public function setClass($class)       { $this->class = $class; }
        public function setLabel($label)       { $this->configure(array('label'=>$label)); }
        public function setPlaceholder($label) { $this->configure(array('placeholder'=>$label)); }
        public function setValue($value)       { $this->configure(array('value'=>$value)); }
        // set current value; same as setData() for most elements
        
    }
}