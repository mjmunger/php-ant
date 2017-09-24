<?php

namespace PHPAnt\Core;

class HTMLMenuBranch extends HTMLMenuNode implements MenuTree
{
    public function getHTML() {
        $buffer = "";

        foreach($this->children as $Node) {
            $HTMLNode = HTMLMenuNodeFactory::getNode($Node);
            $buffer  .= $HTMLNode->getHTML();
        }

        $format = <<<EOF
<div class="w3-dropdown-hover w3-hide-small %s">
    <button class="w3-button">%s</button>
    <div class="w3-dropdown-content w3-bar-block w3-card-4">
%s
    </div>
</div>

EOF;

        //Create a class from the title for CSS styling.
        $dropDownClass = str_replace(' ', '-', $this->title);
        $dropDownClass = strtolower($dropDownClass);

        //Try to make it unique to the nav.
        $dropDownClass = 'nav-' . $dropDownClass;

        return sprintf($format,$dropDownClass, $this->title, $buffer);
        
    }

    public function getMobileHTML() {
        $buffer = "";

        foreach($this->children as $Node) {
            $HTMLNode = HTMLMenuNodeFactory::getNode($Node);
            $buffer  .= $HTMLNode->getMobileHTML();
        }

        $format = <<<EOF
<button class="w3-button w3-block w3-left-align mobile-nav-button" data-target="%s">%s</button>
<div id="%s" class="w3-hide">
    %s
</div>

EOF;

        //Create a class from the title for CSS styling.
        $dropDownID = str_replace(' ', '-', $this->title);
        $dropDownID = strtolower($dropDownID);

        //Try to make it unique to the nav.
        $dropDownID = 'mobile-nav-' . $dropDownID;

        return sprintf( $format
                      , $dropDownID  // Button data target.
                      , $this->title // Button text.
                      , $dropDownID  // Target div ID.
                      , $buffer      // Contents of the drop down. 
                      );
    }
}