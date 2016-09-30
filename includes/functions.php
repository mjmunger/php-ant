<?php

/**
 * Common functions for the entire site.
 *
 * This file contains functions that are in use throughout the site. Do not
 * modify this file, instead, create a file (application.local.php), and put
 * your custom functions in there. Functions in this file may not be
 * overriden.
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Core Functions
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 


/**
 * Instantiate a MySQLi class with a connection to the database as used by your application.
 * Example:
 *
 * <code>
 * $m      = gimmieDB();
 * $sql    = "SELECT * FROM some_database";
 * $result = $m->query($sql);
 *
 * while($row = $result->fetch_assoc()) {
 *   debug_print($row);
 * }
 * unset($m); 
 * </code>
 *
 * @return object an instantiated class of MySQLi with a connection to the database.
 * @param None.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

/*function gimmieDB() {
    //Check for the existence of mysql-credentials.php 
    $creds = 'includes/mysql-credentials.php';
    if(file_exists($creds)){
        $permissions = decoct(fileperms($creds) & 0777);
        if(! ("600" === $permissions)) {
            divAlert("includes/mysql-credentials.php exists, but does not have the correct file permissions. Change the permissions of this file to 0600", 'danger');
            return false;
        }
    } else {
        divAlert("You must configure myql-credentials.php. See: <a href=\"https://www.highpoweredhelp.com/codex/index.php/BFW_Toolkit#Database_Connection\">https://www.highpoweredhelp.com/codex/index.php/BFW_Toolkit#Database_Connection</a>",'danger');
        return false;
    }
    require($creds);
    $m = new mysqli($dbHost,$dbUsername,$dbPassword,$dbDatabase);
    if($m->connect_error) {
        divAlert(sprintf('gimmieDB failure: (%s) %s',$m->connect_errno, $m->connect_error),'danger');
        return false;
    }
    return $m;
}*/

function gimmiePDO($testing=false) {
    /* Check for the existence of mysql-credentials.php */
    $creds = 'includes/mysql-credentials.php';

    if(!file_exists($creds)) die("You must configure myql-credentials.php. See: <a href=\"https://php-ant.org/index.php/Database_Connection\">https://php-ant.org/index.php/Database_Connection</a>");

    require($creds);

    if($testing) {
        $dsn = "mysql:dbname=$dbTesting;host=$dbHost";
    } else {
        $dsn = "mysql:dbname=$dbDatabase;host=$dbHost";
    }
    $dbh = null;

    try {
        $dbh = new PDO($dsn, $dbUsername, $dbPassword);
    } catch (PDOException $e) {
        print PHP_EOL;
        print str_pad('', 80,'*') . PHP_EOL;
        printf('gimmiePDODB failure: %s' . PHP_EOL,$e->getMessage());
        print str_pad('', 80,'*') . PHP_EOL;
        print PHP_EOL;
    }

    return $dbh;
}
/**
 * Includes a local (customized) copy of a file if it exists (in local/), otherwise, it includes the default (core) version.
 * 
 * Example:
 *
 * <code>
 * localInclude('header.php');
 * </code>
 *
 * @return void
 * @param string $filename the file to include.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function localInclude($filename) {
  if(file_exists('local/$filename')) {
    include('local/$filename');
  } else {
    include('includes/$filename');
  }
}

/**
 * Gets the current roles available in the database
 * Example:
 *
 * <code>
 * $roles = getRole();
 * </code>
 *
 * @return array. All roles are returned as an associative array with the role
 *         name as the key and the role ID as the value.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/
function getRoles() {
    $pdo = gimmiePDO();
    $sql    = "SELECT * FROM users_roles";
    $stmt = $pdo->prepare($sql);

    if(!$stmt->execute()) {
        printf("ERROR: Query failed. Could not get access to the database!");
    }

    if($stmt->errorCode()) {
        var_dump($stmt->errorInfo());
        die();
    }

    $return = array();
    while($row = $stmt->fetchObject()) {
        $return[$row->users_roles_title] = $row->users_roles_id;
    }

    unset($m);
    return $return;

}

/**
 * Determines if the given array is an associative array or not.
 * Example:
 *
 * <code>
 * $result = is_assoc($somearray);
 * </code>
 *
 * @return return value
 * @param array $var The array to be tested.
 * @author  skaimauve@yahoo.ca via http://php.net/manual/en/function.is-array.php
 **/

function is_assoc($var) {
    return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
}

/**
 * Checks to variables to see if they are the same type, and if they are, compares them to see if they are the same or not.
 * Example:
 *
 * <code>
 * debug_compare("this","that");
 * </code>
 *
 * @return void
 * @param mixed $value1 The first value to be compared
 * @param mixed $value2 The second value to be compared
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/
function debug_compare($value1,$value2) {
    $type1 = gettype($value1);
    $type2 = gettype($value2);
    if(!($type1 === $type2)) {
        printf("Variables are of two different types! (%s vs %s)\n",$type1,$type2);
        return;
    }

    printf("Comparing %s to %s: %s\n",$value1,$value2,($value1 == $value2)?"Same":"Different");
}

function getSVNVersion($path) {
    chdir(dirname($path));
    $cmd = "svn info | grep Revision | awk -F ':' '{ print $2 }'";
    return trim(shell_exec($cmd));
}

function hex_print($value) {
    $buffer = str_split($value);
    $output = array();

    foreach($buffer as $char) {
        array_push($output, dechex(ord($char)));
    }

    $orignial = array();

    foreach($buffer as $char) {
        array_push($orignial, str_pad($char, 2));
    }

    $string = implode(' ', $orignial);
    $final = implode(' ', $output);

    echo $string . PHP_EOL;
    echo $final . PHP_EOL;
}

function enquote($string) {
    return sprintf("'%s'",$string);
}

function create_check_version_1($pdo) {

    $queries = [];

    //Create the version ID for the database schema.
    $sql = <<<eof
CREATE TABLE IF NOT EXISTS `Version` (
  `VersionId` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`VersionId`));
eof;

    array_push($queries, $sql);
    
    //Create thet settings table if we need it.
    $sql = <<<eof
CREATE TABLE IF NOT EXISTS `settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `settings_key` varchar(255) DEFAULT NULL,
  `settings_value` text,
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
eof;

    array_push($queries, $sql);

    //Create the users roles, if needed:
    $sql = <<<eof
CREATE TABLE `users_roles` (
  `users_roles_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_roles_title` varchar(45) DEFAULT NULL,
  `users_roles_role` varchar(1) DEFAULT 'U' COMMENT 'A - Administrator\nU - Standard User',
  PRIMARY KEY (`users_roles_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
eof;

    array_push($queries, $sql);

    $pdo->beginTransaction();

    foreach($queries as $q) {
        $stmt = $pdo->prepare($q);
        $stmt->execute();
    }

    $pdo->commit();
}

function check_schema() {

    $pdo = gimmiePDO();

    //Check to see if the version table exists.
    $sql = "SELECT * 
FROM information_schema.tables
WHERE table_schema = 'bugreport' 
    AND table_name = 'version'
LIMIT 1";

    $stmt = $pdo->prepare($sql);

    if($stmt->rowCount() == 0) {
        //This is version 1 or below. Create the default stuff to get it to the first tracked version.
        create_check_version_1($pdo);
    } else {
        //Work with the version.
    }
}

function get_json_error_msg($error) {

    $errors = [ JSON_ERROR_NONE           => 'Maximum stack depth exceeded'
              , JSON_ERROR_DEPTH          => 'Underflow or the modes mismatch'
              , JSON_ERROR_STATE_MISMATCH => 'Unexpected control character found'
              , JSON_ERROR_CTRL_CHAR      => 'Syntax error, malformed JSON'
              , JSON_ERROR_SYNTAX         => 'Malformed UTF-8 characters, possibly incorrectly encoded'
              , JSON_ERROR_UTF8           => 'Unknown error'
    ];

    return $errors[$error];
}

/** END FUNCTION LIBRARY **/