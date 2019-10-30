<?php

/*
 * renders a horizontal form; please note that this view does not allow to have
 * multiple form elements in one row
 */

namespace wblib\wbForms\View\Bootstrap;

if (!class_exists('\wblib\wbForms\View\Bootstrap\Horizontal',false))
{
    class Horizontal extends \wblib\wbForms\View
    {
        protected static $template      = '<div class="form-group row">{label}<div class="col-9">{element}{helptext}</div></div>';
        protected static $errortemplate = '<div class="alert alert-danger">{errors}</div>';
        protected static $infotemplate  = '<div class="alert alert-info">{info}</div>';
        public $properties  = array(
            'cssclasses'    => array(
                'form'     => '',
                'fieldset' => 'col-12 form-group',
                'label'    => 'col-3 control-label',
                'text'     => 'form-control',
                'email'    => 'form-control',
                'select'   => 'form-control',
            )
        );

        public function render($form) 
        {
            $form->setAttribute('class',$this->properties['cssclasses']['form']);

            $elements = $form->getElements();
            $buttons  = array();
            $lines    = array();

            if(is_array($elements) && count($elements)) {
                foreach($elements as $e) {
                    $lines[] = $this->renderElement($e);
                }
            }

            // close last fieldset
            if(\wblib\wbForms\Element\Fieldset::$open) {
                $lines[] = "</fieldset>";
            }

            // check if we have buttons
            if($form->countElements('submit')==0) {
                $e = $form->addElement(new \wblib\wbForms\Element\Submit('Submit', array('label'=>'Submit','type'=>'submit')));
                $buttons[] = $e->render();
            } else {
                $btn       = $form->getElementsOfType('submit');
                if(count($btn)>0) {
                    foreach($btn as $b) {
                        $buttons[] = $b->render();
                    }
                }
            }

            $buttonline = null;
            if($form->properties['_auto_buttons']===true) {
                $buttonline = '<div class="form-group row buttonline"><div class="col-3"></div><div class="col-9">'.implode("&nbsp;",$buttons).'</div></div>';
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
        public function renderElement(\wblib\wbForms\Element $e)
        {
            $output = '';
            $tpl    = $e->getTemplate(); // get output template
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

            if($e->hasError()) {
                $e->setAttribute('class',$e->getAttribute('class').' alert alert-danger');
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
                            '<div class="col-3 col-form-label">{label}</div>'
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
                        self::$template
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
                    if(in_array($e->getType(),array('submit','button')) && !$e->hasAttribute('class')) {
                        $e->setAttribute('class','btn btn-secondary');
                    }
                    $output = str_ireplace(
                        array('{label}','{element}','{helptext}'),
                        array('<div class="col-3"></div>',$e->render(),$helptext),
                        self::$template
                    );
                    break;
                case $e instanceof \wblib\wbForms\Element\Submit:
                    if($e->getType()=='submit' && !$e->hasAttribute('class')) {
                        $e->setAttribute('class','btn btn-primary');
                    }
                    #$output = $e->render();
                    break;
                default:
                    $label = (
                          ($e->hasLabel() && strlen($e->getLabel()))
                        ? $this->renderLabel($e)
                        : ''
                    );
                    $output = str_ireplace(
                        array('{label}','{element}','{helptext}'),
                        array($label,$e->render(),$helptext),
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
                array('{for}','{text}','{css}'),
                array($element->getAttribute('id'),$label,$this->properties['cssclasses']['label']),
                '<label for="{for}" class="{css}">{text}</label>'
            );
            return $output;
        }   // end function renderLabel()
    }
}