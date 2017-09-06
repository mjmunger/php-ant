<?php
/**
* MYSQL CREDENTIALS FILE
* This file should be used to configure the MySQL credentials for your project. It's very simple: username, password, host, and database.
* In order to protect it from being downloadable and disclosed, there are several precautions that have been taken:
*
*  1. .htaccess was modified to prevent direct access.
*  2. It does not display any information were it to be access directly.
*  3. File permissions MUST be set at 600.
*
* This example file is version controlled, but does NOT interact directly with
* the system. In order to use this file, you must copy it to mysql-
* creentials.php (remove the 'example-'), and then set the database connection
* information.
**/

//$dbUsername = 'someuser';
//$dbPassword = 'somepass';
//$dbDatabase = 'somedatabase';
//$dbHost     = 'localhost';

$dbUsername = '%USER%';
$dbPassword = '%PASSWORD%';
$dbDatabase = '%DATABASE%';
$dbHost     = '%HOST%';