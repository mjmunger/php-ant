<?php

//Never run via apache!
if(php_sapi_name() !== 'cli') die("Setup can only be run from the command line.");

require_once('includes/classes/CLISetup.class.php');

$Setup = new CLISetup();
$Setup->run();
