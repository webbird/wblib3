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

namespace wblib\wbList;

if (!class_exists('\wblib\wbList\Formatter', false))
{
    class Formatter extends \IteratorIterator
    {

        /**
         * @var array default settings
         **/
        protected static $defaults = array(
            // ***** template *****
            'template_dir'          => null,
            'template_type'         => 'bootstrap',
            'template_variant'      => 'navbar',
            // ***** depth *****
            'mindepth'        => 0,
            'maxdepth'        => 99,
            // ***** css classes *****
            'a'                     => array(
                'first'       => null,
                'last'        => null,
                'child'       => '',
                'current'     => '',
                'default'     => '',
                'trail'       => '',
                'title_as_id' => false,
                'value_as_id' => false,
                'id_prefix'   => null,
            ),
            'ul'                    => array(
                'first'       => null,
                'last'        => null,
                'child'       => '',
                'current'     => '',
                'default'     => '',
                'trail'       => '',
                'title_as_id' => false,
                'value_as_id' => false,
                'id_prefix'   => null,
            ),
            'li'                    => array(
                'first'       => null,
                'last'        => null,
                'child'       => '',
                'current'     => '',
                'default'     => '',
                'trail'       => '',
                'value_as_id' => false,
                'title_as_id' => false,
                'id_prefix'   => null,
            ),
        );
        /**
         * @var array
         **/
        protected $settings;
        /**
         * @var mixed
         **/
        protected $current  = null;
        /**
         * @var
         **/
        protected $tree;

        /**
         *
         * @access public
         * @return
         **/
        public function __construct(array $options = [])
        {
            $this->settings = array_merge(self::$defaults, $options);
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
        public static function getAvailableStyles()
        {
            $dir    = dirname(__FILE__).'/Formatter/templates';
            $styles = array();
            foreach(scandir($dir) as $item) {
                if(!is_dir($dir.'/'.$item)) {
                    continue;
                }
                if(substr($item,0,1)=='.') {
                    continue;
                }
                $styles[] = $item;
            }
            return $styles;
        }   // end function getAvailableStyles()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function getAvailableVariants($style=null)
        {
            $dir    = dirname(__FILE__).'/Formatter/templates';
            $variants = array();
            if(!$style) {
                $subdirs = self::getAvailableStyles();
            } else {
                $subdirs = array($style);
            }
            foreach($subdirs as $sub) {
                $subdir = $dir.'/'.$sub;
                foreach(scandir($subdir) as $item) {
                    if(!is_dir($subdir.'/'.$item)) {
                        continue;
                    }
                    if(substr($item,0,1)=='.') {
                        continue;
                    }
                    if(!isset($variants[$sub])) {
                        $variants[$sub] = array();
                    }
                    $variants[$sub][] = $item;
                }
            }
            return ( $style ? $variants[$style] : $variants );
        }   // end function getAvailableVariants()

        /**
         *
         * @access public
         * @return
         **/
        public function getClasses(&$item, string $for='ul', int $level=1, bool $hasChild=false) : string
        {
            $classes = array();
            if (isset($this->settings[$for.'_classes'])) {
                if (isset($this->settings[$for.'_classes'][$level])) {
                    $classes[] = $this->settings[$for.'_classes'][$level];
                }
            }
            if(isset($this->settings[$for])) {
                if(isset($this->settings[$for]['default'])) {
                    $classes[] = $this->settings[$for]['default'];
                }
                if ($hasChild && !empty($this->settings[$for]['child'])) {
                    $classes[] = $this->settings[$for]['child'];
                }
                if ($item->is_current && isset($this->settings[$for]['current'])) {
                    $classes[] = $this->settings[$for]['current'];
                }
                if ($item->is_in_trail) {
                    $classes[] = $this->settings[$for]['trail'];
                }
            }
            return trim(implode(' ', $classes));
        }   // end function getClasses()

        /**
         *
         * @access public
         * @return
         **/
        public function getKnownClasses(string $for='ul')
        {
            if (isset(self::$defaults[$for])) {
                return array_keys(self::$defaults[$for]);
            }
            return null;
        }   // end function getKnownClasses()

        /**
         *
         * @access public
         * @return
         **/
        public function useTitleAsId($for='ul')
        {
            return (
                   isset($this->settings[$for])
                && isset($this->settings[$for]['title_as_id'])
                && $this->settings[$for]['title_as_id']
            );
        }   // end function useTitleAsId()

        /**
         *
         * @access public
         * @return
         **/
        public function useValueAsId($for='ul')
        {
            return (
                   isset($this->settings[$for])
                && isset($this->settings[$for]['value_as_id'])
                && $this->settings[$for]['value_as_id']
            );
        }   // end function useValueAsId()

        /**
         *
         * @access public
         * @return
         **/
        public function setClasses(string $for='ul', string $key, string $classes, bool $add=false)
        {
            if (!isset($this->settings[$for])) {
                $this->settings[$for] = array(); // [ul]
            }
            if (!$add || !isset($this->settings[$for][$key])) {
                $this->settings[$for][$key] = $classes;
            } else {
                $this->settings[$for][$key] .= ' '.$classes;
            }
        }   // end function setClasses()

        /**
         * set css classes for list or items
         * $classes may be a string -> 1:ddmenu|2:dropdown|3:sub-menu
         * or an array where index is the level and value the classes to use
         *
         * @access public
         * @return
         **/
        public function setLevelClasses(string $for='ul', $classes)
        {
            if (!is_array($classes)) {
                $temp = $classes;
                $classes = array();
                foreach (explode('|', $temp) as $substring) {
                    list($level, $css) = explode(':', $substring, 2);
                    $classes[$level] = $css;
                }
                $this->settings[$for.'_classes'] = $classes;
            }
        }   // end function setLevelClasses()

        /**
         *
         * @access public
         * @return
         **/
        public function setMaxDepth(int $depth)
        {
            $this->settings['maxdepth'] = $depth;
        }   // end function setMaxDepth()

        /**
         *
         * @access public
         * @return
         **/
        public function setMinDepth(int $depth)
        {
            $this->settings['mindepth'] = $depth;
        }   // end function setMinDepth()

        /**
         *
         * @access public
         * @return
         **/
        public function setOption(string $option, string $value, $for=null)
        {
            if($for) {
                $this->settings[$for][$option] = $value;
            } else {
                $this->settings[$option] = $value;
            }
        }   // end function setOption()
        
        /**
         *
         * @access public
         * @return
         **/
        public function getOption(string $option, $for=null, string $default='')
        {
            $arr =& $this->settings;
            if($for) {
                $arr =& $this->settings[$for];
            }
            if(isset($arr[$option])) {
                return $arr[$option];
            }
            return $default;
        }   // end function getOption()
        

        /**
         * initialize settings
         *    + mark items in breadcrumb
         *    + set full path to templates
         *    + resolve level css
         *    + check validity of mindepth and maxdepth
         *
         * throws InvalidArgumentException if mindepth or maxdepth are invalid
         *
         * @access protected
         * @return void
         **/
        protected function init(&$tree)
        {
            // mark path to parent
            $this->markBreadcrumb($tree->getNode($tree->getCurrent()));

            // init templates
            if (!$this->settings['template_dir']) {
                $this->settings['template_dir'] = __DIR__.'/Formatter/templates';
            }
            $this->settings['tpldir'] = implode('/', array(
                $this->settings['template_dir'],
                $this->settings['template_type'],
                $this->settings['template_variant']
            ));

            // silently "fix" wrong maxdepth (> max tree level)
            if (!$this->settings['maxdepth'] || $this->settings['maxdepth'] > $tree->getDepth()) {
                $this->settings['maxdepth'] = $tree->getDepth();
            }
            // check if mindepth is greater than 1 and maxdepth
            if ($this->settings['mindepth'] > $this->settings['maxdepth']) {
                throw new InvalidArgumentException(sprintf(
                    "Min depth [%s] > max depth [%s]!",$this->settings['mindepth'],$this->settings['maxdepth']
                ));
            }

            // level classes
            foreach (array_values(array('ul_classes','li_classes','a_classes')) as $c) {
                if (isset($this->settings[$c]) && is_array($this->settings[$c])) {
                    foreach ($this->settings[$c] as $key => $css) {
                        // analyze >[=] and <[=]
                        // may not the most elegant way, but works for now
                        if (preg_match('/([\>|\<])(\=?)(\d+)/', $key, $m)) {
                            switch ($m[1]) {
                                case '>':
                                    $startlevel = (($m[2]=='=') ? $m[3] : $m[3]+1);
                                    for ($l=$startlevel;$l<$this->tree->getDepth()+1;$l++) {
                                        $this->settings[$c][$l] = $css;
                                    }
                                    unset($this->settings[$c][$key]);
                                    break;
                                case '<':
                                    $stoplevel = (($m[2]=='=') ? $m[3]-1 : $m[3]);
                                    for ($l=1;$l<$stoplevel;$l++) {
                                        $this->settings[$c][$l] = $css;
                                    }
                                    unset($this->settings[$c][$key]);
                                    break;
                            }
                        }
                    }
                }
            }
        }   // end function init()


        /**
         * mark parent items; allows to attach a CSS class to all items
         * in current path
         *
         * @access protected
         * @return void
         */
        protected function markBreadcrumb($node)
        {
            if(!$node) {
                return false;
            }
            while ($parent = $node->getParent()) {
                $parent->is_in_trail = true;
                $node = $parent;
            }
        }   // end function markBreadcrumb()
    }

    /**
     * Exception which will be thrown if a the data for a tree is given as an unusable type.
     **/
    class InvalidArgumentException extends \RuntimeException
    {
    }   // ---------- end class InvalidDatatypeException ----------

}