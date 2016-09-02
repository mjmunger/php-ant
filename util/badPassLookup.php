<?php
include('../includes/application_top.php');
$m=gimmieDB();

$sql = sprintf("SELECT * FROM bugreport.bad_passwords where passwords_password = '%s'",strtolower($_GET['p']));

$result = $m->query($sql);
if($result->num_rows > 0)
	echo "FAIL";
else 
	echo "OK";

?>