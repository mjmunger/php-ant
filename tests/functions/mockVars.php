<?php

/**
 * Returns a sample set of variables in the settings.
 * Example:
 *
 * <code>
 * //Get a non-ssl http_host
 * $vars = mockVars(false);
 * //Get an ssl http_host
 * $vars = mockVars();
 * </code>
 *
 * @return mixed Array of settings that emulate what would be returned by the config file.
 * @param boolean $ssl Whether or not to return an ssl link. True returns ssl (default). False otherwise.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/
function getMockVars($ssl = true) {
	$user = exec('whoami');

	$vars = ['http_host'     => ($ssl?'https://www.google.com':'http://www.google.com')
	        ,'document_root' => sprintf('/home/%s/www',$user)
	        ,'system_user'   => $user
	        ];
	return $vars;
}