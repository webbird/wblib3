<?php

namespace wblib\wbForms;

if (!class_exists('\wblib\wbForms\View',false))
{
    abstract class View extends Base {
        public function render($form) {}
    }
}