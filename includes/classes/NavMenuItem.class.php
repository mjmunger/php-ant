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
    public $uri        = false;
    public $childItems = NULL;
    public $slug       = false;

    function __construct($title, $options = false) {

        if($options) {
            if(isset($options['uri']))  $this->uri = $options['uri' ];
            if(isset($options['slug'])) $this->slug = $options['slug'];
        }

        //If the slug is not set, create a default. 
        if($this->slug == false) $this->slug = $this->slugify($title);
        if($this->uri  == false) $this->uri  = $this->slug; //Assumes that the slug is on the root.

        //Setup the title.
        $this->title = $title;

        //Create child items as a default NavMenuItemList();
        //Composition association. anti-pattern? Code smell?
        $this->childItems = new NavMenuItemList();
    }

    private function slugify($title) {
        $regex = [ '/ /'       => '-'
                 , '/[^\w-]+/' => ''
                 ];

        $slug = $title;

        foreach($regex as $pattern => $replacement) {
            $slug = preg_replace($pattern, $replacement, $slug);
        }

        $slug = strtolower($slug);

        return $slug;
    }

    public function hasChildren() {
        return ($this->childItems->count() > 0);
    }

    public function getChildren() {
        return $this->childItems;
    }

    public function addMenuItem(NavMenuItem $Item) {
        $this->childItems->add($Item);
    }
}