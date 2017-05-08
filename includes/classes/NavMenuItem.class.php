<?php

namespace PHPAnt\Core;

/**
 * Represents an individual menu item.
 * 
 * @author Michael Munger <michael@highpoweredhelp.com>
 * */

class NavMenuItem
{
    public $title      = NULL;
    public $uri        = NULL;
    public $childItems = NULL;

    function __construct($title, $uri = NULL) {
        $this->title = $title;
        $this->uri   = $uri;
        $this->childItems = new NavMenuItemList();
    }

    public function hasChildren() {
        return ($this->childItems->count() > 0);
    }

    public function getChildren() {
        return $this->childItems;
    }


}