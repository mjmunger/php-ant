<?php
/**
 * Represents a UserRole object
 */

 /**
 *
 * This object is used to query and manage UserRole objects on behalf of users.
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     User Roles
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */     

class UserRole extends users_roles
{
    /**
     * Instantiates a UserRole object, and sets the database if one is not provided.
     * Example:
     *
     * <code>
     * $db = gimmieDB();
     * $r = new UserRole($db);
     * </code>
     *
     * @return void
     * @param object $db A current MySQLi object representing a connection to the database.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    /**
     * Tells us whether or not there was a database error on the connection associated with this object.
     * Example:
     *
     * <code>
     * if($u->dbError()) {
     *    // Do something
     * }
     * </code>
     *
     * @return boolean True if there was a database error, false otherwise.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

	function dbError() {
        return (count($this->errors) > 0?true:false);
    }

    /**
     * Returns a well formatted database error message.
     * Example:
     *
     * <code>
     * echo $u->getDBError();
     * </code>
     *
     * @return string The well formatted database errror message.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function getDBError() {
        foreach($this->errors as $error) {
            var_dump($error);
        }
    }

    /**
     * Dumps information about this UserRole to the CLI
     * Example:
     *
     * <code>
     * $u->CLIPrintMe();
     * </code>
     *
     * @return return value
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function CLIPrintMe() {
        echo str_pad($this->users_roles_id,    4);
        echo str_pad($this->users_roles_title,15);
        echo str_pad($this->users_roles_role, 3);
        echo PHP_EOL;
    }

    
    function generateAbbreviation() {
        $buffer = str_split($this->users_roles_title);
        
        foreach($buffer as $a) {
            $a = strtoupper($a);
            $query = "SELECT count(*) as usageCount FROM users_roles WHERE users_roles_role = ?";
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute([$a]);
            $row = $stmt->fetchObject();
            if($row->usageCount == 0) {
                $this->users_roles_role = $a;
                unset($m);
                return $a;
            }
        }

        die("There is not an appropriate letter abbreviation available for this type of user. Try changing the name to something else.");
    }
}
?>
