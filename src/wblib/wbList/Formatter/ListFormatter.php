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

namespace wblib\wbList\Formatter;
use \Tree\Visitor\PreOrderVisitor as PreOrderVisitor;
use \Tree\Visitor\PostOrderVisitor as PostOrderVisitor;

if (!class_exists('\wblib\wbList\Formatter\ListFormatter', false))
{
    class ListFormatter extends \IteratorIterator // extends \wblib\wbList\Formatter
    {
        /**
         * @var array default settings
         **/
        protected static $defaults = array(
            // ***** output templates *****
            'list_open'             => '<ul id="%%id%%" class="%%class%%">',
            'list_close'            => '</ul>',
            'item_open'             => '<li id="%%id%%" class="%%class%%">',
            'item_close'            => '</li>',
            // ***** css options *****
            'list_class'            => 'tree',      // ul element
            'li_class'              => 'item',      // default for li
            'first_li_class'        => 'first',     // first li in current depth
            'last_li_class'         => 'last',      // last li in current depth
            'trail_li_class'        => 'trail',
            'current_li_class'      => 'current',   // current item
            'has_child_li_class'    => 'has-children',
        );
        /**
         * @var array
         **/
        protected $settings;
        /**
         * @var mixed
         **/
        protected        $current  = null;

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
        public function render($tree)
        {
            if(!is_object($tree)) {
                return false;
            }

            $depth    = 1;
            $children = $tree->tree->getChildren();
            $output   = $this->openList();

/*
|- Demo
|- |- Demo 2
|- |- |- Demo 4
|- |- Demo 3
|- |- |- Demo 5
|- |- |- |- Demo 6
|- Homepage
|- Homepage englisch


Array
(
    [list_open] => <ul id="%%id%%" class="%%class%%">
    [list_close] => </ul>
    [item_open] => <li id="%%id%%" class="%%class%%">
    [item_close] => </li>
    [list_class] => nav nav-pills ddmenu dropdown-menu
    [li_class] => item
    [first_li_class] => first
    [last_li_class] => last
    [current_li_class] => active
    [has_child_li_class] => dropdown
    [trail_li_class] => active
    [root_id] => 53
)

*/

            $this->markBreadcrumb($tree->getNode($this->current));

            foreach (new \RecursiveIteratorIterator(new \wblib\wbList\ListNodeIterator($children), \RecursiveIteratorIterator::SELF_FIRST) as $key => $item)
            {
                // close open lists when the depth decreases
                if($item->getDepth()<$depth) {
                    for($i=$depth;$i>$item->getDepth();$i--) {
                        $output .= $this->closeList();
                    }
                }
                $classes = array( $this->settings['li_class'] );
                if( $item->isFirst() ) {
                    $classes[] = $this->settings['first_li_class'];
                }
                if( $item->isLast() ) {
                    $classes[] = $this->settings['last_li_class'];
                }
                if( $item->hasChildren() ) {
                    $classes[] = $this->settings['has_child_li_class'];
                }
                if( $this->isCurrent($item->getID()) ) {
                    $classes[] = $this->settings['current_li_class'];
                }
                if( $item->is_in_trail ) {
                    $classes[] = $this->settings['trail_li_class'];
                }

                if($item->isLeaf()) {
                    $output .= str_ireplace(
                        array( '%%id%%', '%%class%%' ),
                        array( '', implode(' ', $classes) ),
                        $this->settings['item_open']
                    ) . $item->getValue() . $this->settings['item_close'];
                } else {
                    $output .= str_ireplace(
                        array( '%%id%%', '%%class%%' ),
                        array( '', implode(' ', $classes) ),
                        $this->settings['item_open']
                    ) . $item->getValue();
                    if($item->hasChildren()) {
                        $output .= $this->openList();
                    } else {
                        $output .= $this->settings['item_close'];
                    }
                }
                $depth   = $item->getDepth();
            }
            echo $output . $this->closeList();
        }   // end function render()

        /**
         * mark parent items; allows to attach the trail_li_class to all items
         * in current path
         *
         * @access public
         * @return void
         */
        public function markBreadcrumb($node)
        {
            while ($parent = $node->getParent()) {
                $parent->is_in_trail = true;
                $node = $parent;
            }
        }   // end function markBreadcrumb()

        /**
         *
         * @access public
         * @return bool
         **/
        public function isCurrent($id) : bool
        {
            return $this->current == $id;
        }   // end function isCurrent()

        /**
         *
         * @access public
         * @return void
         **/
        public function setCurrent($id)
        {
            $this->current = $id;
        }   // end function setCurrent()

        /**
         * close list
         *
         * @access protected
         * @return string
         **/
        protected function closeList()
        {
            return $this->settings['list_close'];
        }

        /**
         * open list
         *
         * @access protected
         * @return string
         **/
        protected function openList()
        {
            return str_ireplace(
                array( '%%id%%', '%%class%%' ),
                array( '', $this->settings['list_class'] ),
                $this->settings['list_open']
            );
        }
        
    }
}
