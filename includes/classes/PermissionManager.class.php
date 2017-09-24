<?php

namespace PHPAnt\Core;

/**
 * Manages permissions for a given feature or function.
 *
 * This class manages who can access what features by using a list of declared
 * features as they are loaded by the plugins, and then comparing those
 * features to roles and permissions in the database.
 *
 * @package      BFW
 * @subpackage   Core
 * @category     Permissions
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 
     
class PermissionManager
{
    var $functions = array();

    function __construct() {

    }

    function declareFunction($name) {
        array_push($this->functions, $name);
    }

}
?>