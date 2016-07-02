<?php

/* Set the default date and timezone, For a list of supported timezones, see: http://php.net/manual/en/timezones.php */
date_default_timezone_set('America/New_York');

if(!file_exists('config.php')) {
    die("You must have a config.php file configured. Try renaming / copying config.php.sample to config.php, and follow the instructions in the file");
} else {
    require('config.php');
}

/* Require all the interfaces so we can protect our code! */
require('interfaces.php');

/* Require common functions */
require('includes/functions.php');

check_schema();

/* These are hard required because they are bootstrapping classes */
require('includes/classes/ConfigBase.class.php');
require('includes/classes/ConfigCLI.class.php');
require('includes/classes/ConfigWeb.class.php');
require('includes/classes/ConfigFactory.class.php');


/* Custom Error Handler */
/*include('error_handler.php');*/

/** LOAD CONFIGURATIONS **/

$pdo = gimmiePDO();
$antConfigs = PHPAnt\Core\ConfigFactory::getConfigs($pdo,$vars);

/* REGISTER THE AUTOLOADER! This has to be done first thing after we get the configs! */
if(!spl_autoload_register([$antConfigs,'bfw_autoloader'])) die("Autoloader failed!");


/** END LOAD CONFIGURATIONS **/

/** Setup Logger **/
$logger = new logger('bootstrap');
$current_user = null;

/**
 * application_local.php is not version controlled. This file should be created to either:
 * 1. Override existing constants or functions.
 * 2. Provide additional constants or functions specific to your project.
 **/

if(file_exists('../local/application_local.php')) include('../local/application_local.php');

/* This file handles all the plugins and their associated capabilities. This
/* is PURPOSELY placed after functions and the autoloaders so those classes
/* are available to plugins. */

/* Plugin Engine Options */

$options                      = [];
$options['verbosity']         = 0;
$options['safeMode']          = false;
$options['loader_debug']      = false;

//Override defaults if these options are set prior to loading this file. I.e., in the cli.php file.
if(isset($safeMode))      $options['safeMode']      = $safeMode;
if(isset($verbosity))     $options['verbosity']     = $verbosity;
if(isset($loader_debug))  $options['loader_debug']  = $loader_debug;

//Add classes
$options['permissionManager']                       = new PHPAnt\Core\PermissionManager();

require('AppEngine.php');

$Engine = new PHPAnt\Core\AppEngine($antConfigs,$options);

/* If we are running the CLI with the -d switch, initialize the pluging engine as such. */

switch ($Engine->Configs->environment) {
    case PHPAnt\Core\ConfigBase::WEB:
        $Engine->Configs->checkWebVerbosity($Engine);
        break;
    
    default:
        //Do nothing for now.
        break;
}


/* Load any libraries that are in the includes/libs/ directory. */
$Engine->runActions('lib-loader');

/* Load any spl-autoloaders that are contained in Plugins */
$Engine->runActions('load_loaders');

/* Run all actions for checking the database */
$Engine->runActions('db-check');

/* Run all actions that will include custom functions */
$Engine->runActions('include-functions');

/**** AUTHENTICATION AND CURRENT USER SETUP BEGINS ****/
$Engine->runActions('pre-auth');


/**** <AUTHENTICATION CLASSES AND FACTORIES> *****/
include('includes/classes/AuthBfwBase.class.php');
include('includes/classes/AuthCLI.class.php');
include('includes/classes/AuthMobile.class.php');
include('includes/classes/AuthWeb.class.php');
include("includes/classes/AuthEnvFactory.class.php");

/**** </AUTHENTICATION CLASSES AND FACTORIES> *****/

try {
    $Authenticator = AuthEnvFactory::getAuthenticator($antConfigs->pdo,$logger);
} catch (Exception $e) {
    $antConfigs->divAlert($e->getMessage());
    echo "<pre>"; echo $e->getTraceAsString(); echo "</pre>";
}

//Do not require authentication for the CLI
switch ($Authenticator->authType) {
    case BFWAuth::CLI:
        print "CLI Access is for administrators only. God like permissions are present. Caveat emptor" . PHP_EOL;
        break;
    case BFWAuth::WEB || BFWAuth::MOBILE:
        $Authenticator->checkCookies();
        
        if(!$Authenticator->authorized) {
            $Authenticator->authorize($Engine);
        }
        
        $Authenticator->redirect($Engine);

        $current_user = $Authenticator->current_user;
        break;
    default:
        throw new Exception("Invalid Authenticator - could not determine if you are authentication from mobile, web, or CLI", 1);
        break;
}

$Engine->current_user = $current_user;