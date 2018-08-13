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

if (!class_exists('\wblib\wbList\Tree', false))
{
    class Tree
    {
        /**
         * @var string
         * name of the array key that contains the item ID
         */
        protected $idKey     = 'id';
        /**
         * @var string
         * name of the array key that contains the parent ID
         */
        protected $parentKey = 'parent';
        /**
         * @var string
         * name of the array key that contains the value (list item text)
         **/
        protected $valueKey  = 'value';
        /**
         * @var Node[]
         **/
        public    $tree;
        /**
         * @var
         **/
        protected $lookup;

        /**
         *
         * @access public
         * @throws \InvalidArgumentException
         **/
        public function __construct(array $array, array $options = [])
        {
            if (!empty($options['id'])) {
                if (!\is_scalar($options['id'])) {
                    throw new \InvalidArgumentException('Option "id" must be a scalar');
                }
                $this->idKey = $options['id'];
                unset($options['id']);
            }
            if (!empty($options['parent'])) {
                if (!\is_scalar($options['parent'])) {
                    throw new \InvalidArgumentException('Option "parent" must be a scalar');
                }
                $this->parentKey = $options['parent'];
                unset($options['parent']);
            }
            if (!empty($options['value'])) {
                if (!\is_string($options['value'])) {
                    throw new \InvalidArgumentException('Option "value" must be a string');
                }
                $this->valueKey = $options['value'];
                unset($options['value']);
            }
            $this->buildTree($array,$options);
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
        public function exists($id)
        {
            return isset($this->lookup[$id]);
        }   // end function exists()
        
        /**
         *
         * @access public
         * @return
         **/
        public function getNode($id)
        {
            return ( isset($this->lookup[$id]) ? $this->lookup[$id] : null );
        }   // end function getNode()
        
        /**
         * Returns an array of all nodes in the root level.
         *
         * @return Node[] Nodes in the correct order
         **/
        public function getRootNodes() : array
        {
            return $this->tree->getChildren();
        }   // end function getRootNodes()

        /**
         *
         * @access protected
         * @throws InvalidDatatypeException
         * @return
         **/
        protected function buildTree(array $array, array $options = [])
        {
            if (!\is_array($array) && !($array instanceof \Traversable)) {
                throw new InvalidDatatypeException('Data must be an iterable (array or implement Traversable)');
            }

            $current_item = null;
            if(isset($options['current'])) {
                $current_item = $options['current'];
            }

            // virtual root node (never shown)
            $root   = new ListNode('__root__');
            // temp. lookup table
            $this->lookup = array();
            // convert to tree
            foreach($array as $item)
            {
                // create node
                $node = new ListNode();
                // set value
                $node->setValue($item[$this->valueKey]);
                $node->setID($item[$this->idKey]);
                // add any other data
                foreach(array_keys($item) as $key)
                {
                    $this->$key = $item[$key];
                }
                // allows to mark current item (in a page tree, for example)
                if($current_item && $current_item == $item[$this->idKey]) {
                    $node->setCurrent();
                }
                // add to lookup table
                $this->lookup[$item[$this->idKey]] = &$node;
                if($item[$this->parentKey]==0)
                {
                    $root->addChild($node);
                } else {
                    $parent = &$this->lookup[$item[$this->parentKey]];
                    $parent->addChild($node);
                }
                unset($node);
                unset($parent);
            }
            $this->tree = $root;
        }   // end function buildTree()
    }   // ---------- end class Tree ----------

    /**
     * Exception which will be thrown if a the data for a tree is given as an unusable type.
     **/
    class InvalidDatatypeException extends \RuntimeException
    {
    }   // ---------- end class InvalidDatatypeException ----------

    /**
     * Exception which will be thrown if a tree node's parent ID points to an inexistent node.
     **/
    class InvalidParentException extends \RuntimeException
    {
    }   // ---------- end class InvalidParentException ----------
}