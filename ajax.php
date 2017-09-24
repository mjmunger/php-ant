<?php
include('includes/bootstrap.php');

/**
 * Handles access to all ajax functions.
 *
 * This file recieves data from a given AJAX call, and passes it to the appropriate plugin Action.
 *
 * @package      BFW
 * @subpackage   Core
 * @category     AJAX`
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

/* 1. Get all the arguments that are passed in the GET request. Find the one REQUIRED argument: runAction, so we can try to run that hook.*/

$hook = false;
$args = array();

foreach($_GET as $key => $value) {
    if($key == 'hook') {
        $hook = $value;
    }
}

if($hook === false) {
    die("When creating an Ajax call, you must specify the hook to be run with the 'hook' variable. For example, you can use https://www.yourdomain.com/ajax.php?hook=executeMyPlugin?var1=foo&var2=bar. This will cause the plugin engine to run the executeMyPlugin action and pass the remaining values as an array of arguments to that function / method. Arguments are optional, of course, but the runActions varible is NOT. Please modify your code, and try again.");
}

/* 2. Get everything else, and package it into the $args array */
foreach($_GET as $key => $value) {
    if($key != 'hook') {
        $args[$key] = $value;
    }
}

/* 3. Run the action. */
$Engine->runActions($hook,$args);

/* 4. This page will return whatever the hook / plugin outputs. If it's an image, it should be the entirety of the HTML required to display it. If it's JSON, be sure to include the headers for the MIME type, etc... */