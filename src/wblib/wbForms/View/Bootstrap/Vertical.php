<?php

namespace wblib\wbForms\View\Bootstrap;

if (!class_exists('\wblib\wbForms\View\Bootstrap\Vertical',false))
{
    class Vertical extends \wblib\wbForms\View
    {
        use \wblib\wbForms\View\Grid;
        
        protected static $start_row     = '<div class="form-row">';
        protected static $end_row       = '</div>';
        protected static $template      = '<div class="form-group col-md-{cols}">{label}{element}{helptext}</div>';
        protected static $errortemplate = '<div class="alert alert-danger">{errors}</div>';
        protected static $infotemplate  = '<div class="alert alert-info">{info}</div>';
        public $properties  = array(
            'cssclasses'    => array(
                'form'     => '',
                'fieldset' => 'col-sm-12',
                'label'    => 'control-label',
                'text'     => 'form-control',
                'select'   => 'form-control',
            )
        );

        public function render($form) 
        {
            $form->setAttribute('class',$this->properties['cssclasses']['form']);

            $lines   = array();
            $buttons = array();

            // sort elements into grid
            $rows  = $this->toGrid($form);
            
            // populate the grid
            foreach($rows as $row => $items) {
                $lines[] = self::$start_row;
                foreach($items as $i => $item) {
                    $lines[] = $this->renderElement($item['element'], $item['cols']);
                }
                $lines[] = self::$end_row;
            }

            // close last fieldset
            if(\wblib\wbForms\Element\Fieldset::$open) {
                $lines[] = "</fieldset>";
            }

            // check if we have buttons
            if(!count($buttons)) {
                $e = $form->addElement(new \wblib\wbForms\Element\Button('Submit'));
                $buttons[] = $e->render();
            }

            $buttonline = null;
            if($form->properties['_auto_buttons']===true) {
            $buttonline = '<div class="form-group row buttonline"><div class="col-md-2"></div><div class="col-md-10">'.implode("&nbsp;",$buttons).'</div></div>';
            }
            
            $errors     = null;
            $info       = null;

            if($form->hasInfo()) {
                $info   = str_ireplace(
                    '{info}',
                    $form->getInfo(),
                    self::$infotemplate
                );
            }
            if($form->hasErrors()) {
                $errors     = str_ireplace(
                    '{errors}',
                    $form->getErrors(),
                    self::$errortemplate
                );
            }

            // <form{attributes}>\n{form}\n{buttons}\n</form>
            $output = str_ireplace(
                array('{errors}','{info}','{attributes}','{form}','{buttons}'),
                array($errors,$info,$form->getAttributes(),implode("\n",$lines),$buttonline),
                $form->getTemplate()
            );
            
            return $output;
        }   // end function render()

        /**
         *
         * @access public
         * @return
         **/
        public function renderElement(\wblib\wbForms\Element $e, int $cols)
        {
            $output = '';
            $type   = $e->getType();     // element type for css class

            if(isset($this->properties['cssclasses'][$type])) {
                if(strlen($e->getAttribute('class'))>0) {
                    $classes = explode(' ',$e->getAttribute('class'));
                    $prop    = explode(' ',$this->properties['cssclasses'][$type]);
                    $cssclasses = array_merge($prop,$classes);
                    $e->setAttribute('class',implode(' ',$cssclasses));
                } else {
                    $e->setAttribute('class',$this->properties['cssclasses'][$type]);
                }
            }
            if($e->getHelptext()!=='') {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Richtiger Name
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                $e->setAttribute('aria-describedby',$e->getID().'_helpText');
            }
            $helptext = (
                  (strlen($e->getHelptext()))
                ? str_ireplace(
                    array('{id}','{helptext}'),
                    array($e->getID().'_helpText',$e->getHelptext()),
                    '<small id="{id}" class="form-text text-muted">{helptext}</small>'
                )
                : ''
            );
            switch(true) {
                case $e instanceof \wblib\wbForms\Element\Radio:
                case $e instanceof \wblib\wbForms\Element\Checkbox:
                    $label = null;
                    if(strlen($e->getLabel())) {
                        $label = str_ireplace(
                            array('{label}'),
                            array($e->getLabel()),
                            '<div class="col-sm-2">{label}</div>'
                        );
                    }
                    $e->setAttribute('class','form-check-input');
                    $e->setTemplate('<div class="form-check form-check-inline">
                          <label class="form-check-label">
                            <input class="form-check-input" type="'.$e->getType().'" name="{name}" id="{id}" value="{value}"{checked}> {label}
                          </label>
                        </div>'
                    );
                    $output = str_ireplace(
                        array('{label}','{element}','{helptext}'),
                        array($label,$e->render(),$helptext),
                        $this->template
                    );
                    break;
                case $e instanceof \wblib\wbForms\Element\Hidden:
                    $e->setTemplate('<input type="hidden" name="{name}" value="{value}" {attributes} />');
                    $output = $e->render();
                    break;
                case $e instanceof \wblib\wbForms\Element\Fieldset:
                    $output = $e->render();
                    break;
                case $e instanceof \wblib\wbForms\Element\Button:
                    if($e->getType()=='submit' && !$e->hasAttribute('class')) {
                        $e->setAttribute('class','btn btn-primary');
                    }
                    $buttons[] = $e->render();
                    break;
                default:
                    $label = (
                          ($e->hasLabel() && strlen($e->getLabel()))
                        ? $this->renderLabel($e)
                        : ''
                    );
                    $output = str_ireplace(
                        array('{label}','{element}','{helptext}','{cols}'),
                        array($label,$e->render(),$helptext,$cols),
                        self::$template
                    );
                    break;
            }

            return $output;
        }   // end function renderElement()
        

        /**
         *
         * @access public
         * @return
         **/
        public function renderLabel(\wblib\wbForms\Element $element)
        {
            $label = $element->getLabel();
            if($element->isRequired())
				 $label .= ' <span class="required">*</span>';
            $output = str_ireplace(
                array('{for}','{text}'),
                array($element->getAttribute('id'),$label),
                '<label for="{for}" class="">{text}</label>'
            );
            return $output;
        }   // end function renderLabel()
    }
}