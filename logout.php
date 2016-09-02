<?php
require_once('includes/bootstrap.php');

/**
 * Logs a user out of the system.
 *
 * Unsets all cookies that control logins. Since the cookie is
 * cryptographically generated, there is no way to brute-force recover a
 * login.
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     Login / Logout
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

function getHostFQDN()
{
	//returns the FQDN of the host. Ex: https://www.thedomain.com/
	$prefix = '';
	
	if($_SERVER['HTTPS'])
	{
		$prefix = 'https://';
	} else {
		$prefix = 'http://';
	}
	
	return $prefix . $_SERVER['HTTP_HOST'] . '/';
}
	setcookie("current_user","",time()-3600);
	setcookie("mobile_user","",time()-3600);
	header(sprintf("location: %s",getHostFQDN()));
?>