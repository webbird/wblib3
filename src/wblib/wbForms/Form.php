<?php

/**
 *          _     _  _ _    ______
 *         | |   | |(_) |  (_____ \
 *    _ _ _| |__ | | _| |__ _____) )
 *   | | | |  _ \| || |  _ (_____ (
 *   | | | | |_) ) || | |_) )____) )
 *    \___/|____/ \_)_|____(______/
 *
 *
 *
 *   @category     wblib3
 *   @package      wblib3
 *   @author       BlackBird Webprogrammierung
 *   @copyright    2018 BlackBird Webprogrammierung
 **/

namespace wblib\wbForms;

if (!class_exists('\wblib\wbForms\Form', false)) {
    class Form extends Base
    {
        /**
         * config
         **/
        public $properties = array(
            'accept-charset' => null,
            'action'         => null,
            'autocomplete'   => null, // 'on','off'
            'class'          => null,
            'enctype'        => null, // application/x-www-form-urlencoded, multipart/form-data, text/plain
            'id'             => null,
            'lang_path'      => null,
            'method'         => 'POST', // 'GET','POST'
            'novalidate'     => null,
        );
        /**
         * form elements
         **/
        protected $elements  = array();
        /**
         * form data
         **/
        protected $data      = array();
        /**
         * global info text
         **/
        protected $info      = null;
        /**
         * schema to use for the form action url
         **/
        protected $schema    = 'http';
        /**
         * output template
         **/
        protected $template  = "{info}{errors}<form{attributes}>\n{form}\n{buttons}\n</form>\n";
        /**
         * view
         **/
        protected $view;
        /**
         * form data loaded from a file
         **/
        protected $FORMS;
        /**
         * name of current form
         **/
        protected $curr;

        /**
         *
         * @access public
         * @return
         **/
        public function __construct($id='wbForm', $action=null)
        {
            Base::$form_id = $id;
            // configure using some defaults
            $this->configure(array(
                'action' => (empty($action) ? $_SERVER["SCRIPT_NAME"] : $action),
                'id'     => preg_replace("/\W/", "-", $id)
            ));
            if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
                $this->schema = 'https';
            }
        }   // end function __construct()

        /**
         * add element to internal elements array
         **/
        public function addElement(Element $element)
        {
            $this->elements[$element->getID()] = $element;
            return $element;
        }   // end function addElement()
        
        /**
         *
         */
        public function addElementsArray(array $elements)
        {
            foreach ($elements as $element) {
                $type  = $element['type'];
                $name  = isset($element['name'])
                       ? $element['name']
                       : self::generateName();
                $class = '\wblib\wbForms\Element\\'.ucfirst($type);
                $this->addElement(new $class($name, $element));
            }
        }   // end function addElementsArray()

        /**
         *
         * @access public
         * @return
         **/
        public function getData()
        {
            return $this->data;
        }   // end function getData()

        /**
         *
         * @access public
         * @return
         **/
        public function getElement($name)
        {
            // to avoid chaining errors, we create a hidden element here
            if (!isset($this->elements[$name])) {
                return $this->addElement(new Element\Hidden($name));
            }
            return $this->elements[$name];
        }   // end function getElement()

        /**
         * get all elements
         **/
        public function getElements()
        {
            return $this->elements;
        }   // end function getElements()

        /**
         *
         * @access public
         * @return
         **/
        public function getInfo()
        {
            return self::lang()->translate($this->info);
        }   // end function getInfo()

        /**
         *
         * @access public
         * @return
         **/
        public function hasInfo()
        {
            return strlen($this->info);
        }   // end function hasInfo()
        

        /**
         *
         * @access public
         * @return
         **/
        public function isSent()
        {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $data = $_POST;
            } else {
                $data = $_GET;
            }
            // check if we have data for any of the form elements
            foreach ($this->elements as $e) {
                if ($e->getType() == 'submit' && isset($data[$e->getName()])) {
                    return true;
                }
            }
            return false;
        }   // end function isSent()

        /**
         *
         * @access public
         * @return
         **/
        public function isValid()
        {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $data = $_POST;
            } else {
                $data = $_GET;
            }

            foreach ($this->elements as $e) {
                if ($e instanceof Element\Fieldset) {
                    continue;
                }
                if ($e instanceof Element\Button) {
                    continue;
                }
                $value = (!empty($data[$e->getName()]) ? $data[$e->getName()] : null);
                if ($e->isValid($value)) {
                    $this->data[$e->getName()] = $value;
                } else {
                    #echo "INVALID<br />";
                }
            }

            return ($this->hasErrors() ? false : true);
        }   // end function isValid()

        /**
         *
         * @access public
         * @return
         **/
        public function removeData($key)
        {
            if (isset($this->data[$key])) {
                unset($this->data[$key]);
            }
        }   // end function removeData()

        /**
         *
         * @access public
         * @return
         **/
        public function removeElement($name)
        {
            if (isset($this->elements[$name])) {
                unset($this->elements[$name]);
            }
        }   // end function removeElement()

        /**
         * render form
         **/
        public function render(bool $returnHTML=false, array $data = array())
        {
            #if(!empty($data)) $this->setData($data);
            $this->applyData();
            if (empty($this->view)) {
                $this->view = new View\Bootstrap\Horizontal();
            }
            $output = $this->view->render($this);
            if ($returnHTML) {
                return $output;
            } else {
                echo $output;
            }
        }   // end function render()

        /**
         * set form data
         *
         * @access public
         * @return
         **/
        public function setData(array $data)
        {
            foreach ($data as $key => $val) {
                $this->data[$key] = $val;
            }
        }   // end function setData()

        /**
         *
         * @access public
         * @return
         **/
        public function setInfo($info)
        {
            $this->info = $info;
        }   // end function setInfo()
        
        /**
         *
         * @param type $view
         */
        public function setView($view)
        {
            $view_name = '\wblib\wbForms\View\Bootstrap\\'.$view;
            $this->view = new $view_name();
        }
        
        /**
         * apply collected form data
         **/
        protected function applyData()
        {
            foreach ($this->elements as $element) {
                $name = $element->getName();
                if (isset($this->data[$name])) {
                    #if(is_array($this->data[$name])) {
                    #    $element->setOptions($this->data[$name]);
                    #} else {
                    $element->setValue($this->data[$name]);
                #}
                } elseif (substr($name, -2) == "[]" && isset($this->data[substr($name, 0, -2)])) {
                    $element->setValue($this->data[substr($name, 0, -2)]);
                }
            }
        }   // end function applyData()

        /**
         *
         * @access public
         * @return
         **/
        public static function loadFromFile($formname, $file='inc.forms.php', $path=null, $var=null)
        {
            $data = \wblib\wbForms\Utils::loadFile($file, $path, $var);
            if (is_array($data) && count($data)>0) {
                if (!isset($data[$formname])) {
                    return false;
                }
                $form = new self($formname);

                foreach ($data[$formname] as $item) {
                    $type  = 'wblib\wbForms\Element\\'.ucfirst($item['type']);
                    $name  = isset($item['name'])
                        ? $item['name']
                        : 'xxx';
                    $form->addElement(new $type($name, $item));
                    if (isset($item['value'])) {
                        $form->setData(array($name=>$item['value']));
                    }
                }
                return $form;
            } else {
                return false;
            }
        }   // end function loadFromFile()
    }
}
