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
    public $PDO       = NULL;
    public $action    = NULL;
    public $adEnabled = false;
    public $messages  = [];
    public $debug     = [];


    function __construct($PDO, $action) {
        $this->PDO      = $PDO;
        $this->action   = $action;
    }

    private function log($message) {
        array_push($this->messages, $message);
    }

    private function debug($message) {
        array_push($this->debug, $message);
    }

    function enableADChecks() {
        $this->adEnabled = true;
    }

    function disableADChecks() {
        $this->adEnabled = false;
    }

    function roleCanExecute($roleId) {
        //Sys admins have god-like powers.
        if($roleId == 1) {
            $this->log("Current user is an admin. Granting god-like access.");
            return true;
        }
        
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
        $this->debug( [__FUNCTION__ => sprintf( "Does role ID (%s) have permission to execute '%s'? Answer: %s"
                                            , $roleId
                                            , $this->action
                                            , ($return ? "Yes" : "No")
                                            )
                    ]
                   );
        return $return;

    }

    function userLocalGroupCanExecute($usersId) {
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
            users_id = ?) AND acls_event = ?
EOQ;
        
        $values = [$usersId, $this->action];

        $stmt = $this->PDO->prepare($sql);
        $result = $stmt->execute($values);
        if($result == false) {
            throw new Exception("PDO SQL Query failed: " . $stmt->errorInfo()[2], 1);
            
            var_dump($stmt->errorInfo());
            die(__FILE__  . ':' . __LINE__ );
        }

        $this->debug( [__FUNCTION__ => sprintf( "Does local user ID (%s) have permission to execute '%s'? Answer: %s"
                                            , $usersId
                                            , $this->action
                                            , ($return ? "Yes" : "No")
                                            )
                    ]
                   );

        return ($stmt->rowCount() > 0);
    }

    function userSecurityGroupsCanExecute($usersId) {
        $sql = <<<EOQ
SELECT 
    *
FROM
    acls
WHERE
    users_roles_id IN (SELECT 
    users_roles_id
FROM
    users_roles
WHERE
    users_roles_title IN (SELECT 
            user_groups_group
        FROM
            user_groups
        WHERE
            users_users_id = ?)) AND acls_event = ?
EOQ;

        $values = [$usersId, $this->action];

        $stmt = $this->PDO->prepare($sql);
        $result = $stmt->execute($values);
        if($result == false) {
            throw new Exception("PDO SQL Query failed: " . $stmt->errorInfo()[2], 1);
            
            var_dump($stmt->errorInfo());
            die(__FILE__  . ':' . __LINE__ );
        }

        $return = ($stmt->rowCount() > 0);        

        $this->debug( [__FUNCTION__ => sprintf( "Does user ID (%s) belong to an active directory security group that can execute '%s'? Answer: %s"
                                            , $usersId
                                            , $this->action
                                            , ($return ? "Yes" : "No")
                                            )
                    ]
                   );

        $this->log( sprintf( "User ID (%s) %s to a security group allowed to execute action '%s'"
                           , $usersId
                           , ($return ? "belongs" : "does not belong")
                           , $this->action
                           )
                  )

        return $return;

    }
    function userCanExecute($usersId) {

        //Sys admins have god-like powers.
        if($this->userIsAdmin($usersId))                  return true;

        //If their local group can execute, allow it.
        if($this->userLocalGroupCanExecute($usersId))     return true;

        //If AD is not enabled, return false.
        if($this->adEnabled == false)                     return false;

        //Check AD security groups.
        if($this->userSecurityGroupsCanExecute($usersId)) return true;

        //Default to access denied.
        $this->log("User ID (%s) does not have any authority to execute " . $this->action);
        
        return false;

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