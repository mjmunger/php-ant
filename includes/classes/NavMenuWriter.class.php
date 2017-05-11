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

    public function getMobileHTML() {
        $buffer = "";

        foreach($this->items as $Item) {
            $Node = HTMLMenuNodeFactory::getNode($Item);
            $buffer .= $Node->getMobileHTML();
        }

        //Wrap in the <nav> tags.
        $navTags = <<<EOF
    <a href="/" class="w3-bar-item w3-button ant-leaf">Home</a>
    <a href="javascript:void(0)" class="w3-bar-item w3-button w3-right w3-hide-large w3-hide-medium" onclick="showHideMobileMenu()">&#9776;</a>
    <div class="mobile-nav">

%s
    </div>
EOF;
        $buffer = sprintf($navTags,$buffer);
        return $buffer;        
    }

    public function getDesktopHTML() {
        $buffer = "";

        foreach($this->items as $Item) {
            $Node = HTMLMenuNodeFactory::getNode($Item);
            $buffer .= $Node->getHTML();
        }

        //Wrap in the <nav> tags.
        $navTags = <<<EOF
    <div class="w3-bar ant-nav-bar">
    <a href="/" class="w3-bar-item w3-button w3-hide-small ant-leaf">Home</a>
%s
    </div>
EOF;
        $buffer = sprintf($navTags,$buffer);
        return $buffer;
    }

    public function getHTML() {

        $desktopHTML = $this->getDesktopHTML();
        $mobileHTML  = $this->getMobileHTML();

        $format = <<<EOF
<nav>
<!-- Desktop HTML -->
%s
<!-- /Desktop HTML -->
<!-- Mobile HTML -->
%s
<!-- /Mobile HTML -->
</nav>
EOF;

        return sprintf($format,$desktopHTML, $mobileHTML);
    }
}