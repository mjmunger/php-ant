<?php

$rootDir = '/home/michael/php-ant/';
$homedir  = getenv("HOME");
$logdir   = $homedir . '/log/';
$errorlog = $logdir  . 'errors.log';

\ini_set("log_errors", 1);
\ini_set("error_log", $errorlog);

/* Set the default date and timezone, For a list of supported timezones, see: http://php.net/manual/en/timezones.php */
date_default_timezone_set('America/New_York');

/**
 * Determines whether or not the current script is running in a command line environment or not.
 * Example:
 *
 * <code>
 * if(inCLI()) {
 *   dosomething();
 * }
 * </code>
 *
 * @param None.
 * @return boolean. True if we are in the CLI, and false if we are running via Apache.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function inCLI() {
	return php_sapi_name() != 'cli';
}

/**
 * Gets current list of variables for the command line that parallel the ones in the $_SERVER array under Apache
 * Example:
 *
 * <code>
 * $vars = getCLIVars();
 * echo $vars['http_host'];
 * echo $vars['document_root'];
 * </code>
 *
 * @return mixed. an array containing the http_host and document_root of the current site.
 * @param None
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function getCLIVars() {
	$who = trim(`whoami`);
	$vars = ['http_host'     => '%HTTPHOST%'
			,'document_root' => $rootDir
			];

	return $vars;
}
?>