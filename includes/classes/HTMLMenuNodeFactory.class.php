<?php

namespace PHPAnt\Core;

class HTMLMenuNodeFactory
{
    public static function getNode(NavMenuItem $Item) {
        switch($Item->hasChildren()) {
            case true:
                return new HTMLMenuBranch($Item);
                break;
            case false:
                return new HTMLMenuLeaf($Item);
                break;
        }
    }
}