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

    public function getHTML() {
        $buffer = "";

        foreach($this->items as $Item) {
            $Node = HTMLMenuNodeFactory::getNode($Item);
            $buffer .= $Node->getHTML();
        }

        //Wrap in the <nav> tags.
        $navTags = <<<EOF
<nav>
    <div class="w3-bar ant-nav-bar">
%s
    </div>
</nav>
EOF;
        $buffer = sprintf($navTags,$buffer);
        return $buffer;
    }
}