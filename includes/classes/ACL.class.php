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

Class ACL
{
    public $PDO      = NULL;
    public $action   = NULL;

    function __construct($PDO, $action) {
        $this->PDO      = $PDO;
        $this->action   = $action;
    }

    function userCanExecute($usersId) {
        $sql = <<<EOQ
SELECT 
    *
FROM
    phpant.acls
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
            var_dump($stmt->errorInfo);
            die(__FILE__  . ':' . __LINE__ );
        }

        return ($stmt->rowCount() > 0);
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