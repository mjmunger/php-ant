<?php
namespace PHPAnt\Core;

$homedir  = getenv("HOME");
$logdir   = $homedir . '/log/';
$errorlog = $logdir  . 'errors.log';

\ini_set("log_errors", 1);
\ini_set("error_log", $errorlog);

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
$Engine->runActions('pre-auth');


/**** <AUTHENTICATION CLASSES AND FACTORIES> *****/
include('includes/classes/AuthBfwBase.class.php');
include('includes/classes/AuthCLI.class.php');
include('includes/classes/AuthMobile.class.php');
include('includes/classes/AuthWeb.class.php');
include("includes/classes/AuthEnvFactory.class.php");

/**** </AUTHENTICATION CLASSES AND FACTORIES> *****/
/*@Todo: Refactor this to create a factory for bootstrap objects so we don't have to use this if switch. */
if(!isset($NOAUTH)) {
    try {
        $Authenticator = AuthEnvFactory::getAuthenticator($antConfigs->pdo,$logger);
    } catch (Exception $e) {
        $antConfigs->divAlert($e->getMessage());
        echo "<pre>"; echo $e->getTraceAsString(); echo "</pre>";
    }
    
    //Do not require authentication for the CLI
    switch ($Authenticator->authType) {
        case AntAuth::CLI:
            //print "CLI Access is for administrators only. God like permissions are present. Caveat emptor" . PHP_EOL;
            break;
        case AntAuth::WEB || AntAuth::MOBILE:
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
    if(!is_null($Engine->current_user)) $Engine->log($Engine->current_user->getFullName(),"Accessed: " . $Engine->Configs->Server->Request->uri);
}