<?php
include('includes/application_top.php');
$logger = new logger('sudo');

//Save current token as a return token.
setcookie("sudo",$current_user->users_token,0,'/');

//Change current_user to the token for the given user.
$u = new user($PE->Configs->pdo,$logger);
$u->users_id = $_GET['uid'];
$u->load_me();

if($Authenticator->authType == BFWAuth::MOBILE) {
	setcookie("mobile_user",$u->users_token,0,'/');
} else {
	setcookie("current_user",$u->users_token,0,'/');
}

header("location: " . $PE->Configs->getHostFQDN());
?>
