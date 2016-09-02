<?php
include('../includes/application_top.php');
$logger = new logger('sendPasswordReset');
//Get the user object ready.
$o = new user($logger);
$o->users_id = $_GET['uid'];
$o->load_me();
$o->users_nonce = md5(time());
$o->users_setup = 'N';
$o->update_me();
$o->sendPasswordResetEmail();
?>
