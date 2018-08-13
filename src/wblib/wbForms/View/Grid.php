<?php
namespace wblib\wbForms\View;

trait Grid 
{
    public static function toGrid($form)
    {
        $elements = $form->getElements();
        $row      = 0;
        $rows     = array();
        $seen     = array();
        if(is_array($elements) && count($elements)) {
            foreach($elements as $e) {
                if(!isset($e->view_opt['row'])) {
                    $e->view_opt['row'] = $row;
                }
                if(!isset($e->view_opt['pos'])) {
                    $e->view_opt['pos'] = 1;
                }
                if(!isset($e->view_opt['cols'])) {
                    $e->view_opt['cols'] = 12;
                }
                if(isset($e->view_opt['right_of'])) {
                    if(isset($seen[$e->view_opt['right_of']])) {
                        $e->view_opt['row'] = $seen[$e->view_opt['right_of']]['row'];
                    }
                }
                $seen[$e->getName()] = array(
                    'row'  => $e->view_opt['row'],
                    'pos'  => $e->view_opt['pos'],
                    'cols' => $e->view_opt['cols'],
                );
                if(!isset($rows[$e->view_opt['row']])) {
                    $rows[$e->view_opt['row']] = array();
                }
                $rows[$e->view_opt['row']][] = array_merge(
                    array('element' => $e),
                    $seen[$e->getName()]
                );
                $row++;
            }
            return $rows;
        }
    }
}
