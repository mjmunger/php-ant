<?php

namespace PHPAnt\Core;

/**
 * Abstracts, builds, and returns well formed HTML for menu navigation systems
 * in PHPAnt projects.
 * 
 * @author Michael Munger <michael@highpoweredhelp.com>
 * */
interface MenuTree {
    public function getHTML();
}

class NavMenuWriter extends NavMenu
{
    public function __construct(NavMenu $Menu) {
        $this->items = $Menu->items;
    }

    private function traverseNodes($Node) {
        if($Node->hasChildren) {
            foreach($Node->getChildren() as $childNode) {
                return $this->traverseNodes($childNode);
            }
        }

        $HTMLNode = HTMLMenuNodeFactory::getNode($Node);
        return $HTMLNode->getHTML();
    }

    public function getHTML() {
        $buffer = "";

        // foreach($this->items as $Item) {
        //     $Node = HTMLMenuNodeFactory::getNode($Item);
        //     $buffer .= $this->traverseNodes($Node);
        // }

        //Wrap in the <nav> tags.
        $navTags = <<<EOF
<nav>
%s
</nav>
EOF;
        $buffer = sprintf($navTags,$buffer);
        return $buffer;
    }
}