<?php

/**
 * Represents a PHPAnt app.
 */

 /**
 *
 * This is the base app class, which is extended in order to create apps inside the BFW Toolkit.
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     Apps
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */

namespace PHPAnt\Core;

use \Exception;

Class ACL
{
    public $PDO      = NULL;
    public $action   = NULL;

    function __construct($PDO, $action) {
        $this->PDO      = $PDO;
        $this->action   = $action;
    }

    function roleCanExecute($roleId) {
        //Sys admins have god-like powers.
        if($roleId == 1) return true;
        
        $sql = <<<EOQ
SELECT 
    *
FROM
    acls
WHERE
    users_roles_id = ? and acls_event = ?
EOQ;

        $values = [$roleId, $this->action];

        $stmt = $this->PDO->prepare($sql);
        $result = $stmt->execute($values);
        if($result == false) {
            var_dump($stmt->errorInfo());
            die(__FILE__  . ':' . __LINE__ );
        }

        $return = ($stmt->rowCount() > 0);
        return $return;

    }

    function userCanExecute($usersId) {
        //Sys admins have god-like powers.
        if($this->userIsAdmin($usersId)) return true;

        $sql = <<<EOQ
SELECT 
    *
FROM
    acls
WHERE
    users_roles_id = (SELECT 
            users_roles_id
        FROM
            users
        WHERE
            users_id = ? and acls_event = ?)
EOQ;
        
        $values = [$usersId, $this->action];

        $stmt = $this->PDO->prepare($sql);
        $result = $stmt->execute($values);
        if($result == false) {
            throw new Exception("PDO SQL Query failed: " . $stmt->errorInfo()[2], 1);
            
            var_dump($stmt->errorInfo());
            die(__FILE__  . ':' . __LINE__ );
        }

        return ($stmt->rowCount() > 0);
    }

    private function userIsAdmin($usersId) {
        $sql = "SELECT users_roles_id FROM users where users_id = ? LIMIT 1";
        $values = [$usersId];

        $stmt = $this->PDO->prepare($sql);
        $result = $stmt->execute($values);
        if($result == false) {
            throw new Exception("PDO SQL Query failed: " . $stmt->errorInfo()[2], 1);
            
            var_dump($stmt->errorInfo());
            die(__FILE__  . ':' . __LINE__ );
        }

        $row = $stmt->fetchObject();
        return ($row->users_roles_id == 1);
    }

    function addPermission($roles_id) {
        $sql = "INSERT INTO `acls` (`users_roles_id`, `acls_event`) VALUES (?, ?)";
        $stmt = $this->PDO->prepare($sql);
        $values = [$roles_id, $this->action];
        return $stmt->execute($values);
    }

    function removePermission($roles_id) {
        //NO LIMIT statement here on purpose.
        $sql = "DELETE FROM `acls` WHERE `users_roles_id` = ? AND  `acls_event` = ?";
        $stmt = $this->PDO->prepare($sql);
        $values = [$roles_id, $this->action];
        return $stmt->execute($values);
    }
}