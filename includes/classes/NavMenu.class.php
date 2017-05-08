<?php

namespace PHPAnt\Core;

/**
 * Abstracts, builds, and returns well formed HTML for menu navigation systems
 * in PHPAnt projects.
 * 
 * @author Michael Munger <michael@highpoweredhelp.com>
 * */

class NavMenu
{
    public $items = NULL;

    public function __construct() {
        $this->items = new NavMenuItemList();
    }

    public function addMenuItem(NavMenuItem $MenuItem, $parent = NULL) {

        //Add this item as a root item.
        if(is_null($parent)) {
             // echo "Adding root item! " . PHP_EOL;
            $this->items->add($MenuItem);

            //quit, because we're done.
            return;
        }

        //add child items because a parent was specified.
        foreach($this->items as $Item) {
            if($Item->title == $parent->title) {
                // echo "Adding child Item!" . PHP_EOL;
                $Item->childItems->add($MenuItem);
                return;
            }
        }
    }

    private function traverseItems(NavMenuItemList $Items, $pathArray) {
        $needle = array_shift($pathArray);

        foreach($Items as $Item) {
            // echo "Checking: " . $Item->title . PHP_EOL;
            if($needle == $Item->title) {

                //If count($pathArray) == 0 we have reached max depth. We can return regardless of other things.
                if(count($pathArray) == 0) return $Item;

                //If there is more children (and more in the path), keep traversing.
                if($Item->hasChildren())   return $this->traverseItems($Item->childItems, $pathArray);

                //If we have run out of children, but not out of path, we lose. Return false.
                if(!$Item->hasChildren())  return false;
            }
        }
    }

    /**
     * Traverses all menu items looking for items specified, and returns the last of the array specified items.
     * */

    public function getMenuItemByPath($pathArray) {
        $this->items->rewind();
        $Item = $this->traverseItems($this->items,$pathArray);
        return $Item;
    }
}