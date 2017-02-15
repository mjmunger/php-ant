<?php
/* Set the default date and timezone, For a list of supported timezones, see: http://php.net/manual/en/timezones.php */
date_default_timezone_set('America/New_York');

/* Include Mocks and Mock functions */
include('classes/PDOMock.class.php');
include('functions/mockVars.php');

/* Functions that will probably stay in the global scope for consistency go here until refactored into a file under functions/ */

function getDefaultOptions() {

	$PM = new PHPAnt\Core\PermissionManager();
	$BL = new PHPAnt\Core\AppBlacklist();

	$vars = getMockVars();

	$options = ['safeMode' 		    => false
			   ,'permissionManager' => $PM
			   ,'verbosity'         => 0
			   ,'appRoot'           => $vars['document_root'] . '/includes/apps/'
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

function getWebConfigs($vars = false) {

	//Setup test.
	$v = ($vars?$vars:getMockVars());
	$pdo = gimmiePDO();
	$W = new PHPAnt\Core\ConfigWeb($pdo, $v);
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