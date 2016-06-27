<?php
include('includes/application_top.php');

//Save sudo token so we can return to that account.
$token = $_COOKIE['sudo'];
//Destroy the sudo token.
setcookie("sudo","",time()-3600,'/');

//Reset the current_user token back to the sudoly saved token.
if($Authenticator->authType == BFWAuth::MOBILE) {
	setcookie("mobile_user",$token,0,'/');
} else {
	setcookie("current_user",$token,0,'/');
}

header("location: " . $PE->Configs->getHostFQDN());
?>