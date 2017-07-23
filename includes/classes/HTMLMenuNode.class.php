<?php

namespace PHPAnt\Core;

class HTMLMenuNode
{
    public $title    = NULL;
    public $uri      = NULL;
    public $slug     = NULL;
    public $children = false;

    public function __construct(NavMenuItem $Item) {
        $this->title = $Item->title;
        $this->uri   = $Item->uri;
        $this->slug  = $Item->slug;

        if($Item->hasChildren()) $this->children = $Item->getChildren();

    }
}