<?php

namespace PHPAnt\Core;

/**
 * Represents an individual menu item.
 * 
 * @author Michael Munger <michael@highpoweredhelp.com>
 * */

class NavMenuItemList implements \Countable, \Iterator
{
    private $position = 0;
    private $itemList = [];

    public function __construct() {
        $this->rewind();
    }

    public function current() {
        return $this->itemList[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function previous() {
        $this->position--;
    }

    public function valid () {
        return isset($this->itemList[$this->position]);
    }

    public function count() {
        return count($this->itemList);
    }

    public function rewind() {
        $this->position = 0;
    }

    public function add($NavMenuItem) {
        array_push($this->itemList,$NavMenuItem);
    }

    public function getItem($title) {
        foreach($this->itemList as $Item) {
            if($Item->title == $title) return $Item;
        }

        return false;
    }

    public function getItems() {
        return $this->itemList;
    }
}