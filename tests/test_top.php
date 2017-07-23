<?php
$debugMode = false;
include('tests/test-config.php');

/* Set the default date and timezone, For a list of supported timezones, see: http://php.net/manual/en/timezones.php */
date_default_timezone_set('America/New_York');

/* Include Mocks and Mock functions */
include('tests/classes/PDOMock.class.php');
/* These are hard required because they are bootstrapping classes */
require_once('includes/classes/ServerEnvironment.class.php');
require_once('includes/classes/SSLEnvironment.class.php');
require_once('includes/classes/HTTPEnvironment.class.php');
require_once('includes/classes/Execution.class.php');
require_once('includes/classes/WebRequest.class.php');
require_once('includes/classes/ConfigBase.class.php');
require_once('includes/classes/ConfigCLI.class.php');
require_once('includes/classes/ConfigWeb.class.php');
require_once('includes/classes/ConfigFactory.class.php');
require_once('includes/classes/AppBlacklist.class.php');

/* Functions that will probably stay in the global scope for consistency go here until refactored into a file under functions/ */

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
	if(!file_exists('tests/test-config.php')) die("You must configure tests/test-config.php before you can run unit tests." . PHP_EOL);

	include('tests/test-config.php');

	return $vars;
}

function getDefaultOptions() {

	$PM = new PHPAnt\Core\PermissionManager();
	$BL = new PHPAnt\Core\AppBlacklist();

	$vars = getMockVars();

	$options = ['safeMode' 		    => false
			   ,'permissionManager' => $PM
			   ,'verbosity'         => 0
			   ,'appRoot'           => $vars['document_root'] . 'includes/apps/'
			   ,'AppBlacklist'      => $BL
			   ,'verbosity'         => 0
			   ];

	return $options;
}

/**
 * Returns an instance of PHPAnt\Core\CLIConfig
 * Example:
 *
 * <code>
 * $C = getMyConfigs()
 * </code>
 *
 * @return object An instance of PHPAnt\Core\Config
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function getMyConfigs($vars = false) {

	//Setup test.
	$v = ($vars?$vars:getMockVars());
	$pdo = gimmiePDO();
	$C = new PHPAnt\Core\ConfigCLI($pdo, $v);
	return $C;
}

/**
 * Returns an instance of PHPAnt\Core\ConfigWeb
 * Example:
 *
 * <code>
 * $C = getWebConfigs()
 * </code>
 *
 * @return object An instance of PHPAnt\Core\Config
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function getWebConfigs($vars = false, $pdo = false) {

	//Setup test.
	$v = ($vars?$vars:getMockVars());
	
	if($pdo == false) $pdo = gimmiePDO();

	$W = new PHPAnt\Core\ConfigWeb($pdo, $v);

	$W->Server            = new PHPAnt\Core\ServerEnvironment();
	$W->Server->Execution = new PHPAnt\Core\ScriptExecution();
	$W->Server->Request   = new PHPAnt\Core\WebRequest();
	$W->Server->HTTP      = new PHPAnt\Core\HTTPEnvironment();
	$W->Server->SSL       = new PHPAnt\Core\SSLEnvironment();
	return $W;
}

/**
 * Returns an AppEngine instance for testing.
 * Example:
 *
 * <code>
 * $A = getMyAppEngine();
 * </code>
 *
 * @return object An instance of PHPAnt\Core\AppEngine.
 * @param $appPath string The path the AppEngine should use to load apps.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

function getMyAppEngine($options, $C = false) {

	$C = ($C?$C:getMyConfigs());

	$A = new PHPAnt\Core\AppEngine($C,$options);
	return $A;		
}