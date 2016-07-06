#!/usr/bin/env php
<?php

namespace PHPAnt\Core;
include('includes/bootstrap.php');

function help() {
?>

SUMMARY:
Creates and signs manifest files, keys, and other resources needed to publish
an app for the PHP-Ant framework.

USAGE:
php-ant-signer [options] [/path/to/private/key]

  -a [app name]             REQUIRED. The app name you're signing. (the directory
                            name as it appears under include/apps/)
                             
  -g                        Generates a new public and private key pair.
                            
  -h [hook]                 Append an action, to the specified hook, with
     -f [callback]          ...the specified callback function (f),
     -p [priority]          ...at the specified priority (p)
                            Requires -a.
                            
  -m                        Generate a new manifest for the specified app.
                            
  -s [/path/to/private/key] Generates a manfest file and signs it with your private key.
                             
  -v                        Verifies an app's manifest file, and the files listed
                            in it to ensure their authenticity.

IMPORTANT

When generating your keys, be sure to MOVE your private key out of the app
directory. The script will not allow you to sign your app unless it has been
moved out of the app directory; however, BE SURE to save this key in a safe
place! And, rename it to something other than private.key. Something like you-
app-name-private.key is better.

If you lose your private key, you will not be able to change your code, and
updates to your app may break.

<?php
exit();
}

$shortopts = "a:f::g::h::m::p::s::u::v::"; 
$longopts = [];
/*$longopts  = ['app'
             ,'generate-keys'
             ,'help'
             ,'sign-app'
             ,'verify-app'
             ];*/

$opts = getopt($shortopts,$longopts);
//var_dump($opts);
if(count($opts) === 0) help();

if(!array_key_exists('a', $opts) && !array_key_exists('g', $opts)) help();

$Signer = new PHPAntSigner([]);
$Signer->setApp($opts['a']);

if(array_key_exists('g', $opts)) $Signer->genKeys(true);
if(array_key_exists('s', $opts)) $Signer->signApp($opts['s']);
if(array_key_exists('v', $opts)) echo ($Signer->verifyApp()?PHP_EOL . "App integrity OK" . PHP_EOL: PHP_EOL . "App integrity could NOT be verified! Reinstall from the vendor!" . PHP_EOL);

if(array_key_exists('m', $opts)) {
  $path = $Signer->generateManifestFile();
  echo (file_exists($path)?"Manifest file generated successful at: $path":"Manifest file FAILED to generate!");
  echo PHP_EOL;
}

if(array_key_exists('h', $opts)) {
  $hook     = $opts['h'];
  $callback = $opts['f'];
  $priority = $opts['p'];
  $signature = $Signer->registerHook($hook,$callback,$priority);
  $buffer = file_get_contents($Signer->manifestPath);
  echo (stripos($buffer, $signature) !== false?"Hook successfully added to manifest file.".PHP_EOL:"Hook was NOT added to the manifest file!".PHP_EOL);
}

if(array_key_exists('u', $opts)) {
  $path = $opts['u'];
  if(!file_exists($path)) die("The specified private key file ($path) does not exist." . PHP_EOL);
  $Signer->updatePublicKey($path);
}