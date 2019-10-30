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

if (!class_exists('\wblib\wbList\Tree', false))
{
    class Tree
    {
        /**
         * @var string
         * name of the array key that contains the item ID; default 'id'
         */
        protected $idKey     = 'id';
        /**
         * @var string
         * name of the array key that contains the parent ID; default 'parent'
         */
        protected $parentKey = 'parent';
        /**
         * @var string
         * name of the array key that contains the value (list item text)
         * default 'value'
         **/
        protected $valueKey  = 'value';
        /**
         * @var string
         * name of an optional array key that contains a link (href)
         **/
        protected $linkKey   = null;
        /**
         * @var string
         * name of an optional array key that contains a position (sort order)
         **/
        protected $posKey    = null;
        /**
         * @var Node[]
         **/
        public    $tree;
        /**
         * @var
         **/
        protected $lookup;
        /**
         * @var
         **/
        protected $maxdepth = 0;
        /**
         * @var
         **/
        protected $current;

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
            if (!empty($options['linkKey'])) {
                if (!\is_string($options['linkKey'])) {
                    throw new \InvalidArgumentException('Option "linkKey" must be a string');
                }
                $this->linkKey = $options['linkKey'];
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
        public function exists($id) : bool
        {
            return isset($this->lookup[$id]);
        }   // end function exists()

        /**
         *
         * @access public
         * @return
         **/
        public function getCurrent()
        {
            return $this->current;
        }
        
        /**
         *
         * @access public
         * @return
         **/
        public function getDepth()
        {
            return $this->maxdepth;
        }   // end function getDepth()
        
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
         * sort array by parent -> children
         *
         * @access public
         * @param  $a
         * @param $b
         * @return
         **/
        public function sortByParent($a, $b)
        {
            // same id
            if ($a[$this->idKey] == $b[$this->idKey]) {
                return 0;
            // make sure parent is before item
            } elseif ($a[$this->parentKey]) {
                if ($a[$this->parentKey] == $b[$this->parentKey]) {
                    return ($a[$this->idKey] < $b[$this->idKey] ? -1 : 1);
                } else {
                    return ($a[$this->parentKey] >= $b[$this->idKey] ? 1 : -1);
                }
            } elseif ($b[$this->parentKey]) {
                return ($b[$this->parentKey] >= $a[$this->idKey] ? -1 : 1);
            } else {
                return ($a[$this->idKey] < $b[$this->idKey] ? -1 : 1);
            }
        }   // end function sortByParent()

        /**
         * array multisort; creates an array_multisort() call
         *
         * @access public
         * @param  array
         * @param  array
         * @return array
         **/
        public function sortArrayByFields(array $arr, array $fields) : array
        {
            $sortFields = array();
            $args       = array();

            foreach ($arr as $key => $row) {
                foreach ($fields as $field => $order) {
                    $sortFields[$field][$key] = $row[$field];
                }
            }

            foreach ($fields as $field => $order) {
                $args[] = $sortFields[$field];

                if (is_array($order)) {
                    foreach ($order as $pt) {
                        $args[$pt];
                    }
                } else {
                    $args[] = $order;
                }
            }

            $args[] = &$arr;

            call_user_func_array('array_multisort', $args);

            return $arr;
        }   // end function sortArrayByFields()

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

            $root_id = isset($options['root_id']) ? $options['root_id'] : 0;

            // mark current item (useful for menus)
            $current_item = null;
            if(isset($options['current'])) {
                $this->current = $options['current'];
            }

            // virtual root node (never shown)
            $root   = new ListNode('__root__');
            $root->setID('__root__');

            // temp. lookup table
            $this->lookup = array();

            if($root_id !=0 ) {
                // find root element
                foreach($array as $item) {
                    if($item[$this->idKey] == $root_id) {
echo "adding root node ", $item[$this->valueKey], "as first element<br />";
                        // create node
                        $node = new ListNode();
                        // set value
                        $node->setValue($item[$this->valueKey]);
                        $node->setID($item[$this->idKey]);
                        // menu items may have a link
                        if(isset($item[$this->linkKey])) {
                            $node->setLink($item[$this->linkKey]);
                        }
                        // allows to mark current item (in a page tree, for example)
                        if($this->current && $this->current == $item[$this->idKey]) {
                            $node->is_current = true;
                        }
                        $node->is_in_trail = true;
                        $root->addChild($node);
                        // add to lookup table
                        $this->lookup[$item[$this->idKey]] = &$node;
                        break;
                    }
                }
            }

            // sort array by parent -> children
            if(isset($options['sort'])) {
                $opt   = array(
                    $this->parentKey => SORT_ASC,
                    $this->idKey => SORT_ASC,
                );
                if(isset($this->posKey)) {
                    $opt[$this->posKey] = SORT_ASC;
                }

                $array = self::sortArrayByFields(
                    $array, $opt
                );
            }

            // convert to tree
            foreach($array as $item)
            {
                // create node
                $node = new ListNode();
                // set value
                $node->setValue($item[$this->valueKey]);
                // set ID
                $node->setID($item[$this->idKey]);
                // menu items may have a link
                if(isset($item[$this->linkKey])) {
                    $node->setLink($item[$this->linkKey]);
                }
                // allows to mark current item (in a page tree, for example)
                if($this->current && $this->current == $item[$this->idKey]) {
                    $node->is_current = true;
                }
                // add to lookup table
                $this->lookup[$item[$this->idKey]] = &$node;
                #if($item[$this->parentKey]==$root_id)
                #{
                #    $root->addChild($node);
                #} else {
                    if(isset($this->lookup[$item[$this->parentKey]])) {
                        $parent = &$this->lookup[$item[$this->parentKey]];
                    } else {
// !!!!! TODO: throw exception? resort array?
                        $parent = &$root;
                    }
                    $parent->addChild($node);
                #}
                if($node->getDepth()>$this->maxdepth) {
                    $this->maxdepth = $node->getDepth();
                }
                unset($node);
                unset($parent);
            }
            $this->tree = $root;
        }   // end function buildTree()

        /**
         *
         * @access public
         * @return
         **/
        public function flattened()
        {
            if (!is_object($this->tree) || $this->getDepth()==0) {
                return array();
            }
            $flat = array(); // initialize return array
            $children = $this->tree->getChildren();
            foreach (new \RecursiveIteratorIterator(new ListNodeIterator($children), \RecursiveIteratorIterator::SELF_FIRST) as $key => $item) {
                $flat[] = $item->asArray();
            }
            return $flat;
        }   // end function flattened()



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