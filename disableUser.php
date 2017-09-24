<?php
include('includes/application_top.php');
$logger = new logger("disableUser");

//Get the user object ready.
$o = new user($PE->Configs->pdo,$logger);
$o->users_id = $_GET['uid'];
$o->load_me();
if($_GET['action'] == 'disable') {
	$o->users_active = 'N';
} else {
	$o->users_active = 'Y';
}
$result = $o->update_me();

echo json_encode(['success' => $result]);

?>