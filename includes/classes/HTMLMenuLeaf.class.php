<?php

namespace PHPAnt\Core;

class HTMLMenuLeaf extends HTMLMenuNode implements MenuTree
{
    public function getHTML() {
        $pattern = '<a href="%s" class="w3-bar-item w3-button ant-leaf">%s</a>' . PHP_EOL;
        return sprintf($pattern,$this->uri,$this->title);
    }
}