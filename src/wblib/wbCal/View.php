<?php

namespace wblib\wbCal;

if (!class_exists('\wblib\wbCal\View',false))
{
    abstract class View extends Base {
        public function render($cal) {}
    }
}