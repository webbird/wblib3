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

if (!class_exists('\wblib\wbList\Formatter\BreadcrumbFormatter', false))
{
    class BreadcrumbFormatter extends \wblib\wbList\Formatter
    {

        /**
         *
         * @access public
         * @return
         **/
        public function render($tree)
        {
            if (!is_object($tree)) {
                return false;
            }
            $this->tree = $tree;
            $this->init($tree);

            $depth    = 1;
            $children = $tree->tree->getChildren();
            $nav      = '';
            ob_start();

            // iterate elements
            foreach (new \RecursiveIteratorIterator(new ListNodeIterator($children), \RecursiveIteratorIterator::SELF_FIRST) as $key => $item) {
                if ($item->getDepth() > $this->settings['maxdepth'] || $item->getDepth() < $this->settings['mindepth']) {
                    continue;
                }
                if(!$item->is_in_trail && !$item->is_current) {
                    continue;
                }
                $liclasses = $this->getClasses($item, 'li', $item->getDepth(), $item->hasChildren());
                $aclasses  = $this->getClasses($item, 'a', $item->getDepth(), $item->hasChildren());
                include $this->settings['tpldir'].'/li.menuitem.phtml';
                $nav .= ob_get_contents();
                ob_clean();
            }

            // outer ul
            $ulclasses = $this->getClasses($item, 'ul', 1);
            include $this->settings['tpldir'].'/ul.phtml';
            $output = ob_get_contents();
            $output .= $nav;

            // close outer level
            $output .= '</ul>';

            ob_clean();
            ob_end_flush();

            return $output;
        }   // end function render()
    }
}
