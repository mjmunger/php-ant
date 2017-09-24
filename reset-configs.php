<?php

if(php_sapi_name() !== 'cli') die("This can only be executed from the command line");

$credentialsFile = 'includes/mysql-credentials.php';
$webConfigFile   = 'includes/config.php';
$cliConfigFile   = 'includes/cli-config.php';

$configFiles = [ $credentialsFile
    , $webConfigFile
    , $cliConfigFile
];

foreach($configFiles as $config) {
    if(file_exists($config) == true ) unlink($config);
}

echo "All configs have been removed. You can re-install now." . PHP_EOL;