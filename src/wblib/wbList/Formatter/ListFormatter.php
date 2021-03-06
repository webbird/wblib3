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

use \wblib\wbList\ListNodeIterator as ListNodeIterator;

if (!class_exists('\wblib\wbList\Formatter\ListFormatter', false))
{
    class ListFormatter extends \wblib\wbList\Formatter
    {
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
            if (!is_object($tree) || $tree->getDepth()==0) {
                return false;
            }

            $this->tree = $tree;

            $depth    = 1;
            $children = $tree->tree->getChildren();
            $nav      = '';

            $this->init($tree);

            ob_start();

            // iterate elements
            foreach (new \RecursiveIteratorIterator(new ListNodeIterator($children), \RecursiveIteratorIterator::SELF_FIRST) as $key => $item) {
                if ($item->getDepth() > $this->settings['maxdepth'] || $item->getDepth() < $this->settings['mindepth']) {
                    continue;
                }
                // close open lists when the depth decreases
                if ($item->getDepth()<$depth) {
                    for ($i=$depth;$i>$item->getDepth();$i--) {
                        $nav .= '</ul></li>';
                    }
                }
                $liclasses = $this->getClasses($item, 'li', $item->getDepth(), $item->hasChildren());
                $aclasses  = $this->getClasses($item, 'a', $item->getDepth(), $item->hasChildren());

                // default
                if ($item->isLeaf()) {
                    include $this->settings['tpldir'].'/li.menuitem.phtml';
                    $nav .= ob_get_contents();
                    ob_clean();
                } elseif ($item->hasChildren()) {
                    $ulclasses = $this->getClasses($item, 'ul', $item->getDepth()+1);
                    include $this->settings['tpldir'].'/li.menuitem.dropdown.phtml';
                    include $this->settings['tpldir'].'/ul.phtml';
                    $nav .= ob_get_contents();
                    ob_clean();
                }
                $depth   = $item->getDepth();
            }

            // outer ul
            if($item) {
                $ulclasses = $this->getClasses($item, 'ul', 1);
            }

            include $this->settings['tpldir'].'/ul.phtml';
            $output = ob_get_contents();
            $output .= $nav;

            // close all sub levels
            while ($depth>1) {
                $output .= "</ul></li>";
                $depth--;
            }

            // close outer level
            $output .= '</ul>';

            ob_end_clean();


            return $output;
        }   // end function render()
    }
}
