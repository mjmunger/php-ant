<?php
namespace PHPAnt\Core;

if(!file_exists('includes/config.php')) die("You must have a config.php file configured. Try renaming / copying config.php.sample to config.php, and follow the instructions in the file");

/* Require the configuration for this installation */
require('includes/config.php');

/* Make sure document_root exists */
if(!file_exists($vars['document_root'])) die(sprintf("Document root is either not configured, or doesn't exist. Here's what I've got, does it look right to you? (document_root = %s )" . PHP_EOL , print_r($vars['document_root'],true)));

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
require('includes/classes/PermissionManager.class.php');

/* Include composer files if present */
if(file_exists('includes/vendor/autoload.php')) include('includes/vendor/autoload.php');

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
        //Verify a token if it has been given.
        $WR->verifyAuthenticityToken();
        //Create a new one for this request.
        $WR->generateAuthenticityToken();
        $WR->parseGet($_GET);
        $WR->mergeRequest();
        $WR->setCookies($_COOKIE);
        $WR->importJSON('php://input');
        $WR->parsePut('php://input', getallheaders());
        $WR->parsePatch('php://input', getallheaders());
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
$hideErrors = 'hide';
$errorLevel = $antConfigs->getConfigs(['errors']);
if(count($errorLevel) > 0) $hideErrors = $errorLevel['errors'];

$configs = $antConfigs->getConfigs(['EngineVerbosity']);
if(isset($configs['EngineVerbosity'])) $dbVerbosity = $configs['EngineVerbosity'];

//Set the visual trace as it was saved - this overrides the command line params.
$visualTrace = false;
$configs = $antConfigs->getConfigs(['visualTrace']);
if(isset($configs['visualTrace'])) $visualTrace = ($configs['visualTrace'] == 'on' ? true : false);

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

//$logger = new \Logger('bootstrap');
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
if(isset($visualTrace))   $options['visualTrace']   = $visualTrace;
if(isset($loader_debug))  $options['loader_debug']  = $loader_debug;

//Add classes
$options['permissionManager']                       = new PermissionManager();
$options['AppBlacklist']                            = new AppBlacklist();

require('AppEngine.php');

$Engine = new AppEngine($antConfigs,$options);

//$Engine->log('Bootstrap', str_pad('REQUEST START', 33,'=',STR_PAD_BOTH) ,'AppEngine.log',1);
$Engine->log('Bootstrap','Verbosity level: ' . $verbosity,'AppEngine.log',1);

//Set the error handler to the AppEngine::handleError() method.
if($hideErrors == 'show') {
    error_reporting(E_ALL);
} else {
    set_error_handler(array(&$Engine,'handleError'));
}

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
if($Engine->visualTrace) printf('<span class="w3-tag w3-round w3-teal" style="margin:0.25em;">%s:%s</span>','Bootstrap','Boostrap Actions Begin');

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
if($results) $Engine->current_user = ( isset($results['current_user']) ? $results['current_user'] : false );

if($Engine->current_user) $Engine->current_user->load();

/*Determine the user's permissions*/
$Engine->runActions('set-user-permissions');

/* Do post-authorization tasks and cleanup*/
$Engine->runActions('post-auth');
if($Engine->visualTrace) printf('<span class="w3-tag w3-round w3-teal" style="margin:0.25em;">%s:%s</span>','Bootstrap','Boostrap Actions End');
