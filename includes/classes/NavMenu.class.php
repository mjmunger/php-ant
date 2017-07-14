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

    /**
     * Adds a NavMenuItem to the given menu. By default, it will add to the
     * root. However, if you want to add a child item, then pass the parent
     * node as the second (optional) parameter.
     * 
     * Note: Adding parents only allows you to go "up one level". To go 3+
     * layers deep, you need to add a node to a parent, then add that parent to
     * a parent, etc...
     * 
     * @param $MenuItem object the NavMenuItem that will be added to the menu.
     * @param $parent object The node that will act as the parent for the node being added.
     * @author Michael Munger <michael@highpoweredhelp.com>
     * 
     * */

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
            if($Item->slug == $parent->slug) {
                // echo "Adding child Item!" . PHP_EOL;
                $Item->childItems->add($MenuItem);
                return;
            }
        }
    }

    /**
     * Traverses the given NavMenuItemList trying to find a specific node,
     * which is represented by the $pathArray. When it finds it, it returns
     * that node. Otherwise, it will return false.
     * 
     * @param $Items object The NavMenuItemList to traverse looking for the node.
     * @param $pathArray array An array the represents the location of the node we are looking for.
     * @author Michael Munger <michael@highpoweredhelp.com>
     * */

    private function traverseItems(NavMenuItemList $Items, $pathArray) {
        $needle = array_shift($pathArray);

        foreach($Items as $Item) {

            if($needle == $Item->slug) {

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


    /**
     * Recurses a menu array to convert elements to MenuItems and add them to a MenuItemList to be returned up the chain.
     * @param $menuArray array An associative array that is passed from buildMenu(), and is the result of apps reporting their published menus and structure.
     * @return object NavMenuItemList of ManvMenuItems.
     * */

    private function recurseMenuArray($menuArray, $MenuItem) {

        //echo "Processing menu node: " . key($menuArray) . PHP_EOL;

        foreach($menuArray as $key => $value) {

            //Recurse into submenus.
            if(is_array($value)) {
                $SubMenu = new NavMenuItem($key);
                $Submenu = $this->recurseMenuArray($value,$SubMenu);
                $MenuItem->addMenuItem($SubMenu);
            }

            //Create menu items of all the items on this level.
            $options = [];
            $options['uri'] = $value;
            $Item = new NavMenuItem($key,$options);
            $MenuItem->addMenuItem($Item);
        }

        return $MenuItem;
    }

    /**
     * Build a menu from an associative array (as published by apps)
     *
     * @param $menuArray array The (aggregated) associative array that is published by all apps.
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @return mixed True if the menu build was successful. An array of errors if there were problems.
     * */

    public function buildMenu($menuArray) {

        //Add all the root items. We need something to use as parents.

        foreach($menuArray as $element => $menuArray) {

            //Create the root item
            $RootItem = new NavMenuItem($element);

            //Recurse the corresponding array, adding things to this item, and
            //return the finished product to ourselves.
            $RootItem = $this->recurseMenuArray($menuArray, $RootItem);

            //Add this $this menu.
            $this->items->add($RootItem);
        }
        return true;
    }
}