<?php
include('../includes/application_top.php');
$logger = new logger('query-user');

//Get the user object ready.
$o = new user($logger);

//Get the user we are looking for from the request
$name = $_GET['u'];
echo $o->getJSONResults($name);

?>