<?php

/* boundary(custom-namespace)=--0297a59a2d3f796cb872eca96627d4dfd0aa9ab3 */
/* --0297a59a2d3f796cb872eca96627d4dfd0aa9ab3 */


namespace PHPAnt\Core;


/* --0297a59a2d3f796cb872eca96627d4dfd0aa9ab3 */
/**
 * PHPAnt abstraction of table: `settings`
 *
 * This class represents the database table settings.
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
 * PHPAnt is copyright (c) 2015-2017 High Powered Help, Inc. All rights reserved.
 */ 
 
class Settings
{

	/*<table columns>*/
	var $settings_id;
	var $settings_key;
	var $settings_value;
	/*</table columns>*/
	var $pdo;
	var $errors = [];
	var $fields = [];
	var $autoUpdateFields = [];
	var $insertDefaultFields = [];
	var $table = "settings";
	var $primaryKey = false;
	var $logger = '';
	var $__oldlabel = '';


     /* boundary(custom-properties)=--19ec56c3e24dbd5538e094f997707b46a5b3787f */
     /* --19ec56c3e24dbd5538e094f997707b46a5b3787f */


     //Your properties go here!


     /* --19ec56c3e24dbd5538e094f997707b46a5b3787f */

    function __construct(\PDO $pdo, $logger='') {
        $this->pdo = $pdo;

        //Get the field list, and find the primary key field.
        $sql = "DESCRIBE settings;";
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
        $sql        = "SELECT * FROM `settings` WHERE `settings_id` = ? LIMIT 1";
        $stmt       = $this->pdo->prepare($sql);
        $values     = [$this->settings_id];

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
     /* boundary(custom-methods)=--7b3c47378558084c1f6d790c281a69a6afa05ab9 */
     /* --7b3c47378558084c1f6d790c281a69a6afa05ab9 */


     /**
     * Prints the key value pair for this record in the CLI
     * Example:
     *
     * <code>
     * $s = new Setting();
     * $s->CLIPrintMe();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
        function CLIPrintMe() {
        echo str_pad($this->settings_key, 20);
        echo $this->settings_value;
        echo PHP_EOL;
    }


     /* --7b3c47378558084c1f6d790c281a69a6afa05ab9 */
}