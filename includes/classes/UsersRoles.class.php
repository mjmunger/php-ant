<?php


/* boundary(custom-namespace)=--2a39aa24cf19a6c9cceb128c43e7fd64811c446d */
/* --2a39aa24cf19a6c9cceb128c43e7fd64811c446d */


namespace PHPAnt\Core;


/* --2a39aa24cf19a6c9cceb128c43e7fd64811c446d */
/**
 * PHPAnt abstraction of table: `users_roles`
 *
 * This class represents the database table users_roles.
 * It contains methods and properties that are auto generated, and
 * which can be automatically updated using PHPAnt's database
 * abstraction tool. 
 *
 * MAKE SURE YOU EDIT THIS CLASS ONLY WITHIN THE BOUNDARIES!
 * 
 * A boundary looks like this:
 */
   /* boundary(custom-methods)=--9fac6baf3c7d28fded0287ec00a9c9274a930a11 */
   /* --9fac6baf3c7d28fded0287ec00a9c9274a930a11 */
   
   
        /* - YOUR CUSTOM FUNCTIONS GO HERE -

        function sayHello() {
            echo "hello World" . PHP_EOL;
        }

        */
   
   
   /* --9fac6baf3c7d28fded0287ec00a9c9274a930a11 */
 /*
 * Boundaries are marked by a boundary(custom-[method|properties]) marker.
 * They are then followed by a 42 character string that begins with --.
 *
 * As long as your edits are WITHIN a boundary, they will be retained when 
 * the class is auto-updated at a later time.
 *
 * And remember: ALWAYS backup your files!
 *
 * @package      Some Package
 * @subpackage   Some Subpackage
 * @category     Some Category
 * @author       Your Name <youremail@example.com>
 * @license      MIT
 *
 * Generated with PHPAnt. https://php-ant.org/
 * PHPAnt is copyright (c) 2015-2016 High Powered Help, Inc. All rights reserved.
 */ 
 
class UsersRoles
{

	/*<table columns>*/
	var $users_roles_id;
	var $users_roles_title;
	var $users_roles_role;
	/*</table columns>*/
	var $pdo;
	var $errors = [];
	var $fields = [];
	var $autoUpdateFields = [];
	var $insertDefaultFields = [];
	var $table = "users_roles";
	var $primaryKey = false;
	var $logger = '';
	var $__oldlabel = '';



     /* boundary(custom-properties)=--ce610cb5af094aa52121c54e23b5a8a8cf6a580e */
     /* --ce610cb5af094aa52121c54e23b5a8a8cf6a580e */


     //Your properties go here!


     /* --ce610cb5af094aa52121c54e23b5a8a8cf6a580e */

    function __construct(\PDO $pdo, $logger='') {
        $this->pdo = $pdo;

        //Get the field list, and find the primary key field.
        $sql = "DESCRIBE users_roles;";
        $stmt = $this->pdo->prepare($sql);

        if(!$stmt->execute()) var_dump($stmt->errorInfo());

        if($stmt->rowCount() == 0) {
         //this should never happen.
         $error = sprintf("The table %s has zero columns. Nothing to do",$table);
         throw new Exception($error, 1);
         return false;
        }

        $props = array();

        while ($row = $stmt->fetchObject()) {
         array_push($props,$row->Field);
         //Grab ONLY the first primary key. We assume that the primary key (id) is always the first column that ia primary key.
         if(!$this->primaryKey && $row->Key = "PRI") $this->primaryKey = $row->Field;

         //Check to see if this field should be included in an insert
         //statement by looking at the default. If it contains any of the
         //default keywords, it is an automatic field, and should not be
         //included in insert statements.

         $defaultKeywords = ['CURRENT_TIMESTAMP'];
         if(in_array($row->Default, $defaultKeywords)) array_push($this->insertDefaultFields, $row->Field);

         //Check to see if this field should be included in update statements
         //by checking to see if keywords appear in Extra. If those keywords
         //are present, then it should be added to the
         //$this->autoUpdateFields, which means we will NOT set a value on
         //update. We'll let MySQL do that for us.


         $updateKeywords = ['on update CURRENT_TIMESTAMP'];
         if(in_array($row->Extra, $updateKeywords)) array_push($this->autoUpdateFields,$row->Field);

        }

        $this->fields = $props;

        if($logger) {
            $this->logger = $logger;
            $this->__oldlabel = $logger->label;
            $this->logger->label = 'company';
        } else {
            //TBA
        }
        
    }

    function __destruct() {
        if($this->logger)
        $this->logger->label = $this->__oldlabel;
    }

    function load_me() {
        
        if($this->users_roles_id == false) return false;

        $sql        = "SELECT * FROM `users_roles` WHERE `users_roles_id` = ? LIMIT 1";
        $stmt       = $this->pdo->prepare($sql);
        $values     = [$this->users_roles_id];

        if(!$stmt->execute($values)) {
            $info = $stmt->errorInfo();
            throw new Exception(sprintf("Database error generating the parent class for $table. Database error (%s) %s",$info[1],$info[2]), 1);
        } 

        $row = $stmt->fetchObject();

        foreach($this->fields as $column) {
            $this->$column = $row->$column;
        }
    }

    function commit_suicide() {

        $table = $this->table;
        $primaryKey = $this->primaryKey;
        $errors = [];
        
        $sql = sprintf("DELETE FROM `%s` WHERE `%s`= ?",$table,$primaryKey);
        $stmt = $this->pdo->prepare($sql);
        $values = [$this->$primaryKey];
        $result = $stmt->execute($values);

        if(!$result) {
            array_push($errors, $stmt->errorInfo);
            array_push($errors, $stmt);
            array_push($errors, $values);
            array_push($this->errors, $errors);
        }
        return $result;
    }

    function update_me() {
        $buffer = [];
        $errors = [];
        $values = [];
        $primaryKey = $this->primaryKey;
        
        //Prepare the SQL parts
        
        $updateSQL = "UPDATE `%s` SET " . PHP_EOL;
        $update = sprintf($updateSQL,$this->table);

        //Prepare the bound fields
        foreach($this->fields as $f) {
            //Skip the primary key.
            if($f == $this->primaryKey) continue;

            //Skip this if MySQL wants to use an update (Extra) value (on update CURRENT_TIMESTAMP)
            if(in_array($f, $this->autoUpdateFields)) continue;

            $boundField = " %s = :%s ";
            array_push($buffer,sprintf($boundField,$f,$f));    
        }

        $boundFields = implode(', ' . PHP_EOL, $buffer);

        //Prepare the where
        $whereSQL = sprintf(" WHERE `%s`= :whereVal LIMIT 1; ", $this->primaryKey);

        //Concatenate them for the final statement.
        $finalSQL = $update . $boundFields . $whereSQL;

        //Prepare the statment
        $stmt = $this->pdo->prepare($finalSQL);

        //Generate the fields.
        foreach($this->fields as $f) {
            if($f == $this->primaryKey) continue;
            $values[$f] = $this->$f;
        }

        $values['whereVal'] = $this->$primaryKey;

        $result = $stmt->execute($values);

        if(!$result) {
            array_push($errors, $stmt->errorInfo());
            array_push($errors, $stmt);
            array_push($errors, $values);
            array_push($this->errors, $errors);
        }
        
        return $result;
    } 

    function insert_me() {
        $buffer = [];
        $errors = [];
        $primaryKey = $this->primaryKey;
        
        //Prepare the SQL parts
        $baseSql = "INSERT INTO `%s` ( %s ) VALUES ( %s )";

        $fbuffer = [];
        $vbuffer = [];

        foreach($this->fields as $field) {
            if($field == $this->primaryKey) continue;

            //Skip this if MySQL wants to use an default value (CURRENT_TIMESTAMP)
            if(in_array($field, $this->autoUpdateFields)) continue;

            array_push($fbuffer,$field);
            array_push($vbuffer,":". $field);
        }

        $fields = implode(', ', $fbuffer);
        $values = implode(', ', $vbuffer);

        $sql = sprintf($baseSql,$this->table,$fields,$values);

        $stmt = $this->pdo->prepare($sql);

        //Generate the fields.
        foreach($fbuffer as $f) {
            $buffer[$f] = $this->$f;
        }

        $result = $stmt->execute($buffer);

        if(!$result) {
            array_push($errors, $stmt->errorInfo());
            array_push($errors, $stmt);
            array_push($errors, $values);
            array_push($this->errors, $errors);
        } else {
            $this->$primaryKey = $this->pdo->lastInsertId();
        }

        return $result;
    }

    function threw_db_error() {
        return (count($this->errors) > 0)?true:false;
    }

     /* boundary(custom-methods)=--b51b8b00ee7a4b2abe03c11724f1e16e2d618972 */
     /* --b51b8b00ee7a4b2abe03c11724f1e16e2d618972 */



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
        $TL = new TableLog();
        $TL->addHeader(['ID','Title','Role']);
        $TL->addRow([$this->users_roles_id ,$this->users_roles_title ,$this->users_roles_role]);
        $TL->showTable();
    }

    
    function generateAbbreviation() {
        if(is_null($this->users_roles_title)) throw new \Exception("Cannot generate an abbreviation before you set the role title.", 1);
        
        $buffer = $this->users_roles_title;
        $upperBound = strlen($buffer);

        for($x=0; $x < $upperBound; $x++) {
            $a = strtoupper($buffer[$x]);
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


     /* --b51b8b00ee7a4b2abe03c11724f1e16e2d618972 */
}