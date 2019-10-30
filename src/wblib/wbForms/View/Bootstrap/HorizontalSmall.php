<?php

namespace wblib\wbForms\View\Bootstrap;

if (!class_exists('\wblib\wbForms\View\Bootstrap\HorizontalSmall',false))
{
    class HorizontalSmall extends Horizontal
    {
        public $properties  = array(
            'cssclasses'    => array(
                'form'     => 'form-horizontal',
                'fieldset' => 'col-sm-12',
                'label'    => 'col-sm-2 control-label',
                'text'     => 'form-control form-control-sm',
                'email'    => 'form-control form-control-sm',
                'select'   => 'form-control form-control-sm',
            )
        );
    }
}