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
    public $slug       = false;

    function __construct($title, $options = false) {

        if($options) {
            if(isset($options['uri']))  $this->uri = $options['uri' ];
            if(isset($options['slug'])) $this->uri = $options['slug'];
        }

        //If the slug is not set, create a default. 
        if($this->slug == false) $this->slug = $this->slugify($title);

        //Setup the title.
        $this->title = $title;

        //Create child items as a default NavMenuItemList();
        //Composition association. anti-pattern? Code smell?
        $this->childItems = new NavMenuItemList();
    }

    private function slugify($uri) {
        $regex = [ '/ /'      => '-'
                 , '/[^-\w]/' => ''
                 ];

        foreach($regex as $pattern => $replacement) {
            $uri = preg_replace($pattern, $replacement, $uri);
        }

        $uri = strtolower($uri);

        return $uri;
    }

    public function hasChildren() {
        return ($this->childItems->count() > 0);
    }

    public function getChildren() {
        return $this->childItems;
    }


}