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

require __DIR__.'/vendor/autoload.php';

use \Tree\Node\Node as Node;
use \Tree\Node\NodeInterface as NodeInterface;

if (!class_exists('\wblib\wbList\ListNodeIterator', false))
{

    class ListNodeIterator extends \RecursiveArrayIterator
    {

        private $tree;

        public function __construct($tree)
        {
           $this->tree = $tree;
        }

        public function next()
        {
           next($this->tree);
           return;
        }

        public function current()
        {
           return current($this->tree);
        }

        public function rewind()
        {
           reset($this->tree);
           return;
        }

        public function key()
        {
           return key($this->tree);
        }

        public function valid()
        {
            return (isset($this->tree[key($this->tree)]) && is_object($this->tree[key($this->tree)]) && $this->tree[key($this->tree)] instanceof ListNode);
        }

        public function getChildren()
        {
            return new ListNodeIterator($this->current()->getChildren());
        }
        public function hasChildren()
        {
            return ( count($this->tree[key($this->tree)]->getChildren()) > 0 );
        }
    }
}