<?php

namespace PHPAnt\Setup;

function setupAutoloader($class) {

    $buffer = explode('\\', $class);
    $class = end($buffer);

    $baseDir = __DIR__ . '/includes';

    $candidate_files = array();

    //Try to grab it from the classes directory.
    $candidate_path = sprintf($baseDir. '/classes/%s.class.php',$class);
    array_push($candidate_files, $candidate_path);

    //Loop through all candidate files, and attempt to load them all in the correct order (FIFO)

    //Nasty nested if's are used because the short circuit doesn't work sometimes with &&

    foreach($candidate_files as $dependency) {
//        echo "Looking for: $dependency " . PHP_EOL;
        if(file_exists($dependency)) {
            if(is_readable($dependency)) {
//                echo "Including: $dependency" . PHP_EOL;
                require_once($dependency);
            }
        }
    }
}

//Never run via apache!
$canRun = (php_sapi_name() === 'cli' ? true : false);

if($canRun === false) die("This can only be run from the command line.");

//Register the autoloader.
spl_autoload_register('PHPAnt\Setup\setupAutoloader');

$userInteractiveSetup = ( file_exists('settings.json') ? false : true);

echo ($userInteractiveSetup ? "Settings file does not exist. Starting interactive setup" : "Settings file exists. Doing unattended setup");

$SetupConfigs = SetupConfigsFactory::getSetupConfigs($userInteractiveSetup);
$SetupConfigs->loadConfig(__DIR__ . '/settings.json');
$Installer = new Installer($SetupConfigs);

$Installer->install();