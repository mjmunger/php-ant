<?php
namespace PHPAnt\Core;

if(!file_exists('includes/config.php')) die("You must have a config.php file configured. Try renaming / copying config.php.sample to config.php, and follow the instructions in the file");

/* Require the configuration for this installation */
require('includes/config.php');

/* Require all the interfaces so we can protect our code! */
require('interfaces.php');

/* Require common functions */
require('includes/functions.php');

check_schema();
/* These are hard required because they are bootstrapping classes */
require('includes/classes/ServerEnvironment.class.php');
require('includes/classes/SSLEnvironment.class.php');
require('includes/classes/HTTPEnvironment.class.php');
require('includes/classes/Execution.class.php');
require('includes/classes/WebRequest.class.php');
require('includes/classes/ConfigBase.class.php');
require('includes/classes/ConfigCLI.class.php');
require('includes/classes/ConfigWeb.class.php');
require('includes/classes/ConfigFactory.class.php');
require('includes/classes/AppBlacklist.class.php');


/* Custom Error Handler */
/*include('error_handler.php');*/

/** LOAD CONFIGURATIONS **/

$pdo = gimmiePDO();
$antConfigs = ConfigFactory::getConfigs($pdo,$vars);

//Provision the server variables if we have a ConfigWeb object.
switch($antConfigs->environment) {
    case ConfigBase::WEB:

        //Abstract and objectify $_SERVER
        $Server = new ServerEnvironment();
        $Server->setup($_SERVER);

        //Setup the HTTP Environment
        $HTTP = new HTTPEnvironment();
        $HTTP->setup($_SERVER);
        $Server->HTTP = $HTTP;

        //Setup SSL.
        $SSL = new SSLEnvironment();
        $SSL->setup($_SERVER);
        $Server->SSL = $SSL;

        //Setup the Web Request
        $WR = new WebRequest();
        $WR->setup($_SERVER);
        $WR->parsePost($_POST);
        $WR->parseGet($_GET);
        $WR->mergeRequest();
        $WR->setCookies($_COOKIE);
        $Server->Request = $WR;

        //Setup script execution environment
        $ScriptExecution = new ScriptExecution();
        $ScriptExecution->setup($_SERVER);
        $Server->Execution = $ScriptExecution;

        $antConfigs->Server = $Server;
        break;
}

//Set the EngineVerbosity as it was saved - this overrides the command line params. 
$dbVerbosity = false;
$configs = $antConfigs->getConfigs(['EngineVerbosity']);
if(isset($configs['EngineVerbosity'])) $dbVerbosity = $configs['EngineVerbosity'];

//Keep the higher verbosity between the CLI and the DB.
if(isset($verbosity)) {
    $verbosity = max($verbosity,$dbVerbosity);
} else {
        $verbosity = ($dbVerbosity?$dbVerbosity:0);
}

/* REGISTER THE AUTOLOADER! This has to be done first thing after we get the configs! */
if(!spl_autoload_register([$antConfigs,'ant_autoloader'])) die("Autoloader failed!");

/** END LOAD CONFIGURATIONS **/

/** Setup Logger **/

$logger = new \Logger('bootstrap');
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

/* App Engine Options */

$options                      = [];
$options['verbosity']         = 0;
$options['safeMode']          = false;
$options['loader_debug']      = false;

//Override defaults if these options are set prior to loading this file. I.e., in the cli.php file.
if(isset($safeMode))      $options['safeMode']      = $safeMode;
if(isset($verbosity))     $options['verbosity']     = $verbosity;
if(isset($loader_debug))  $options['loader_debug']  = $loader_debug;

//Add classes
$options['permissionManager']                       = new PermissionManager();
$options['AppBlacklist']                            = new AppBlacklist();

require('AppEngine.php');

$Engine = new AppEngine($antConfigs,$options);

$Engine->log('Bootstrap','Verbosity level: ' . $verbosity,'AppEngine.log',1);

//Set the error handler to the AppEngine::handleError() method.
set_error_handler(array(&$Engine,'handleError'));

switch ($Engine->Configs->environment) {
    case ConfigBase::WEB:
        //$Engine->Configs->checkWebVerbosity($Engine);
        break;
    
    default:
        //Do nothing for now.
        break;
}

/* Setup things based on the settings in the database. */

//Enable / disable the AppBlacklist
$configs = $Engine->Configs->getConfigs(['BlacklistDisabled','EngineVerbosity']);
$Engine->AppBlacklist->disabled = (isset($configs['BlacklistDisabled'])?(bool)$configs['BlacklistDisabled']:false);

/* NOTE: YOU CANNOT DO LOGGING THAT DOES debug_print (the final option) UNILT AFTER YOU'VE AUTHENTICATED THE USER! */

/* Load any libraries that are in the includes/libs/ directory. */
$Engine->runActions('lib-loader');

/* Load any spl-autoloaders that are contained in Apps */
$Engine->runActions('load_loaders');

/* Run all actions for checking the database */
$Engine->runActions('db-check');

/* Run all actions that will include custom functions */
$Engine->runActions('include-functions');

/**** AUTHENTICATION AND CURRENT USER SETUP BEGINS ****/

/* Run actions that setup authentication.*/
$Engine->runActions('pre-auth');

/* Authorize the user. */
$results = $Engine->runActions('auth-user');

//If we authorized a user, store it here.
if($results) $Engine->current_user = ( isset($results['current_user']) ? $resuls['current_user'] : false );

/*Determine the user's permissions*/
$Engine->runActions('set-user-permissions');

/* Do post-authorization tasks and cleanup*/
$Engine->runActions('post-auth');