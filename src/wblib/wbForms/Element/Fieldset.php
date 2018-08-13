<?php

namespace wblib\wbForms\Element;

if (!class_exists('\wblib\wbForms\Element\Fieldset',false))
{
    class Fieldset extends \wblib\wbForms\Element
    {
        protected $template    = '<fieldset{attributes}><legend>{label}</legend>';
        protected $type        = 'fieldset';
        protected $haslabel    = true;
        public    static $open = false;

        /**
         *
         * @access public
         * @return void
         **/
        public function render()
        {
            $output = parent::render();
            $output = str_ireplace('{label}',$this->getLabel(),$output);
            if(self::$open) {
                $output = "\n</fieldset>\n".$output;
            }
            self::$open = true;
            return $output;
    	}   // end function render()

        /**
         * set element data
         **/
        public function setData($data) {
            $this->setAttribute('options',$data);
        }   // end function setData()

    }
}