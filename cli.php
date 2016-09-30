#!/usr/bin/env php
<?php
use PHPAnt\Core;

$rootDir  = __DIR__;

/* Get command line options */
$options = getopt('v::d::s::');

/*Default options*/
$loader_debug = false;
$safeMode     = false;
/* Load bootstrap options. */
if(sizeof($options) > 0) {
    if(array_key_exists('v', $options)) {
        if(is_numeric($options['v'])) {
            $verbosity = $options['v'];
        } else {
            $verbosity = strlen($options['v']) +1;
        }
    }

    if(array_key_exists('d', $options)) {
        /* Allow loader debug to run */
        printf("Command line options received: \n");
        var_dump($options);
        $loader_debug = true;
        if(isset($verbosity)) {
            $verbosity = ($verbosity>10?$verbosity:10);
        } else {
            $verbosity = 10;
        }
    }

    if(array_key_exists('s',$options)) {
        printf("Safemode requested" . PHP_EOL);
        $safeMode = true;
    }
    
}

/* Include the application top... and everything else. */
include('includes/bootstrap.php');

$C = new PHPAnt\Core\Cli($Engine);
if(sizeof($options) > 0) {
    /* Set debug mode if -d is specified. Also sets verbosity to 10, but may be overridden with the -v command. */
    if(array_key_exists('d',$options)) {
        $C->setDebugMode();
    }
    /* Sets verbosity. Overrides what was set by -d */
    if(array_key_exists('v', $options)) {
        $level = 1 + strlen($options['v']);
        $C->setVerbosity($level);
    }
}
$C->run();