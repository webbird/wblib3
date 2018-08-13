<?php

namespace wblib\wbForms;

if (!class_exists('\wblib\wbForms\Base',false))
{
    abstract class Base
    {
        /**
         * debug
         **/
        protected $debug           = false;
        /**
         * config
         **/
        public $properties         = array();
        /**
         * collected errors
         **/
        private static $errors     = array();
        /**
         * accessor to wbLang if available
         **/
        public  static $lang_path  = NULL;
        private static $wblang     = NULL;
        /**
         * stores the name of the current form
         **/
        public  static $form_id    = NULL;

        /**
         * configure element
         *
         * Available properties are configured in the element's class
         * $properties attribute. Only available properties can be configured.
         * Properties starting with an underscore are read-only.
         *
         * @access public
         * @param  array
         * @return void
         **/
    	public function configure(array $properties = null) {
            if(!empty($properties)) {
                foreach($properties as $key => $val) {
                    $this->setAttribute($key,$val);
                }
            }
        }   // end function configure()

        /**
         *
         * @access public
         * @return
         **/
        public function addError($message)
        {
            self::$errors[] = $message;
        }   // end function addError()

        /**
         *
         * @access public
         * @return
         **/
        public function getAttribute($attribute)
        {
            if(!empty($this->properties)) {
                if(array_key_exists($attribute,$this->properties)) {
                    return $this->properties[$attribute];
                }
            }
            return false;
        }   // end function getAttribute()
        

    	public function getAttributes($ignore = "") {
            $str = "";
    		if(!empty($this->properties)) {
    			if(!is_array($ignore))
    				$ignore = array($ignore);
    			$attributes = array_diff(array_keys($this->properties), $ignore);
    			foreach($attributes as $attribute) {
       				if(is_string($this->properties[$attribute]) && strlen($this->properties[$attribute])) {
                        $str .= ' ' . $attribute;
    					#$str .= '="' . $this->filter($this->properties[$attribute]) . '"';
    					$str .= '="' . $this->properties[$attribute] . '"';
                    }
    			}
    		}
            return $str;
        }   // end function getAttributes()

        /**
         *
         * @access public
         * @return
         **/
        public function getErrors()
        {
            return implode("<br />\n",self::$errors);
        }   // end function getErrors()
        
        /**
         * generates an unique element name if none is given
         *
         * @access protected
         * @param  integer  $length
         * @return string
         **/
        protected static function generateName($length=8,$prefix='')
        {
            for(
                   $code_length = $length, $newcode = '';
                   strlen($newcode) < $code_length;
                   $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
            );
            return $prefix.$newcode;
        }   // end function generateName()

        public function hasAttribute(string $key)
        {
            return (isset($this->properties[$key]));
        }

        /**
         *
         * @access public
         * @return
         **/
        public function hasErrors()
        {
            return count(self::$errors)>0;
        }   // end function hasErrors()

        /**
         * converts variable names like "default_template_variant" into human
         * readable labels like "Default template variant"
         *
         * @access public
         * @return
         **/
        public function humanize($string)
        {
            return ucfirst(str_replace('_',' ',$string));
        }   // end function humanize()

        /**
         *
         * @access public
         * @return
         **/
        public function setAttribute($key, $val)
        {
            if(
                   substr($key,0,1) != '_'
                && (
                       array_key_exists($key,$this->properties)
                    || ( isset($this->universal) && array_key_exists($key,$this->universal) )
                )
            ) {
                switch($key) {
                    case 'lang_path':
                        self::$lang_path = $val;
                        self::lang()->addFile(self::lang()->current(),$val);
                        break;
                    case 'required':
                        if(strlen($val)) {
                            $this->properties['required']      = 'required'; // valid XHTML
                            $this->properties['aria-required'] = 'true';     // WAI-ARIA
                        }
                        break;
                    case 'id': // make sure ID is unique
                        #$val = self::$form_id.'_'.$val;
                        break;
                    default:
                        $this->properties[$key] = $val;
                        break;
                }
                if($this->debug) {
                    echo sprintf(
                        "<pre>option [%20s] for element [%20s] set to [%s]!</pre><br />",
                        $key,
                        (isset($this->name) ? $this->name : 'unnamed'),
                        (is_array($val) ? 'array ('.count($val).')' : $val)
                    );
                }
            } else {
                if($this->debug) {
                    echo "setting internal or unknown option [$key] is prohibited!<br />";
                }
            }
        }   // end function setAttribute()

        /**
         *
         **/
        public function setTemplate($tpl)
        {
            $this->template = $tpl;
        }
        

       /**
         * convenience methods
         **/
        public function getName()              { return $this->name; }
        public function getTemplate()          { return $this->template; }

        /**
         * accessor to wbLang (if available)
         **/
        public function lang()
        {
            #self::log('> lang()',7);
            if(!self::$wblang && !self::$wblang == -1)
            {
                #self::log('Trying to load wbLang',7);
                try
                {
                    include_once dirname(__FILE__).'/../wbLang.php';
                    self::$wblang = \wblib\wbLang::getInstance();
                    $lang_path = self::$lang_path;

#echo sprintf('wbLang loaded, current language [%s], lang path [%s]',self::$wblang->current(), $lang_path),"<br />";
                    // auto-add lang file by lang_path global
                    if(null !== $lang_path)
                    {
                        if(is_dir($lang_path)) {
                            #self::log(sprintf('adding global lang path [%s]',$lang_path,7);
                            self::$wblang->addPath($lang_path);
                            if(
                                   file_exists(self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php'))
                                || file_exists(self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php'))
                                || file_exists(self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.strtolower(self::$wblang->current()).'.php'))
                            ) {
#echo sprintf('adding file [%s]',self::path(pathinfo($lang_path,PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php')), "<br />";
                                self::$wblang->addFile(self::$wblang->current());
                            }
                        }
                    }
                    // auto-add lang file by caller's path
                    $callstack = debug_backtrace();
                    $caller    = array_pop($callstack);
                    $i         = 0; // avoid deep recursion
                    while(!strcasecmp(dirname(__FILE__),$caller['file']))
                    {
                        if($i>=3) break;
                        $i++;
                        $caller    = array_pop($callstack);
                    }
                    if(isset($caller['file']))
                    {
                        if(is_dir(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages')))
                        {
                            #self::log(sprintf('adding path [%s]',self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages')),7);
                            self::$wblang->addPath(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages'));
                        }
                        if(
                               file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php'))
                            || file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.strtoupper(self::$wblang->current()).'.php'))
                            || file_exists(self::path(pathinfo($caller['file'],PATHINFO_DIRNAME).'/languages/'.strtolower(self::$wblang->current()).'.php'))
                        ) {
                            #self::log(sprintf('adding file [%s]',self::$wblang->current()),7);
                            self::$wblang->addFile(self::$wblang->current());
                        }
                        // This is for BlackCat CMS, filtering backend paths
                        if(isset($caller['args']) && isset($caller['args'][0]) && is_scalar($caller['args'][0]) && file_exists($caller['args'][0]))
                        {
                            if(is_dir(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages')))
                                self::$wblang->addPath(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages'));
                            if(file_exists(self::path(pathinfo($caller['args'][0],PATHINFO_DIRNAME).'/languages/'.self::$wblang->current().'.php')))
                                self::$wblang->addFile(self::$wblang->current());
                        }
                    }
                }
                catch(Exception $e)
                {
                    #self::log(sprintf(
                    #    'Unable to load wbLang: [%s]',$e->getMessage()
                    #),7);
                    self::$wblang = -1;
                }
            }
            if(is_object(self::$wblang)) return self::$wblang;
        }   // end function lang()

        /**
         * fixes a path by removing //, /../ and other things
         *
         * @access public
         * @param  string  $path - path to fix
         * @return string
         **/
        public static function path($path)
        {
            // remove / at end of string; this will make sanitizePath fail otherwise!
            $path       = preg_replace( '~/{1,}$~', '', $path );
            // make all slashes forward
            $path       = str_replace( '\\', '/', $path );
            // bla/./bloo ==> bla/bloo
            $path       = preg_replace('~/\./~', '/', $path);
            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts      = array();
            foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
            {
                if ($part === ".." || $part == '')
                    array_pop($parts);
                elseif ($part!="")
                    $parts[] = $part;
            }
            $new_path = implode("/", $parts);
            // windows
            if ( ! preg_match( '/^[a-z]\:/i', $new_path ) )
                $new_path = '/' . $new_path;
            return $new_path;
        }   // end function path()

    }
}