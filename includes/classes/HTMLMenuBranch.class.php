<?php

namespace PHPAnt\Core;

class HTMLMenuBranch extends HTMLMenuNode implements MenuTree
{
    public function getHTML() {
        $buffer = "";

        foreach($this->children as $Node) {
            $HTMLNode = HTMLMenuNodeFactory::getNode($Node);
            $buffer .= $HTMLNode->getHTML();
        }

        $format = <<<EOF
<div class="w3-dropdown-hover">
    <button class="w3-button">Top1</button>
    <div class="w3-dropdown-content w3-bar-block w3-card-4">
%s
    </div>
</div>

EOF;

        return sprintf($format,$buffer);
        
    }
}