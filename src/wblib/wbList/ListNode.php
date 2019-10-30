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

use \Tree\Node\Node as Node;
use \Tree\Node\NodeInterface as NodeInterface;

if (!class_exists('\wblib\wbList\ListNode', false)) {
    class ListNode extends Node
    {
        /**
         * @var
         **/
        protected $guid;
        protected $id;
        protected $link;
        public    $is_in_trail = false;
        public    $is_current  = false;

 
        /**
         * overrides the constructor to add a unique GUID
         *
         * @access public
         * @return
         **/
        public function __construct()
        {
            parent::__construct();
            $this->guid = $this->createGUID();
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
        public function asArray()
        {
            return array(
                'guid'        => $this->guid,
                'id'          => $this->getID(),
                'link'        => $this->getLink(),
                'is_in_trail' => $this->is_in_trail,
                'is_current'  => $this->is_current,
                'value'       => $this->getValue(),
                'level'       => $this->getDepth(),
            );
        }   // end function asArray()
        

        /**
         * creates a guid for each item; allows to clearly identify each item
         *
         * @access public
         * @return string
         **/
        public function createGUID() : string
        {
            $s = strtoupper(md5(uniqid(rand(), true)));
            $guidText =
                substr($s, 0, 8) . '-' .
                substr($s, 8, 4) . '-' .
                substr($s, 12, 4). '-' .
                substr($s, 16, 4). '-' .
                substr($s, 20);
            return $guidText;
        }   // end function createGUID()

        /**
         * returns true if the element has children
         *
         * @access public
         * @return
         **/
        public function hasChildren() : bool
        {
            return count($this->getChildren())>0;
        }   // end function hasChildren()

        /**
         * returns true if the current element is the first child of it's parent
         *
         * @access public
         * @return
         **/
        public function isFirst() : bool
        {
            return ($this->getParent()->getChildren()[0]->guid == $this->guid);
        }   // end function isFirst()

        /**
         * returns true if the current element is the last child of it's parent
         *
         * @access public
         * @return
         **/
        public function isLast() : bool
        {
            $last = array_values(array_slice($this->getParent()->getChildren(), -1))[0];
            return ($last->guid == $this->guid);
        }   // end function isLast()

        public function getID()
        {
            return $this->id;
        }
        public function setID($id)
        {
            $this->id = $id;
        }
        /**
         *
         * @access public
         * @return
         **/
        public function setLink(string $link)
        {
            $this->link = $link;
        }   // end function setLink()
        
        /**
         *
         * @access public
         * @return
         **/
        public function getLink()
        {
            return $this->link;
        }   // end function getLink()
        
        /**
         *
         * @access public
         * @return
         **/
        public function hasLink() : bool
        {
            return isset($this->link);
        }   // end function hasLink()
        
    }   // ---------- end class ListNode ----------
}
