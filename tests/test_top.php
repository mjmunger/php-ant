<?php
/* Set the default date and timezone, For a list of supported timezones, see: http://php.net/manual/en/timezones.php */
date_default_timezone_set('America/New_York');

/* Include Mocks and Mock functions */
include('classes/PDOMock.class.php');
include('functions/mockVars.php');

/* Functions that will probably stay in the global scope for consistency go here until refactored into a file under functions/ */

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

function getMyConfigs() {
	//Setup test.
	$vars = getMockVars();
	$pdo = gimmiePDO();
	$C = new PHPAnt\Core\ConfigCLI($pdo, $vars);
	return $C;
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

function getMyAppEngine($appPath = 'includes/apps/') {
	$C = getMyConfigs();

	$PM = new PHPAnt\Core\PermissionManager();

	$options = ['safeMode' 		 => false
			   ,'permissionManager' => $PM
			   ,'verbosity'         => 0
			   ];

	$A = new PHPAnt\Core\AppEngine($C,$options,$appPath);
	return $A;		
}
