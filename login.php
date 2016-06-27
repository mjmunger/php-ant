<?php
/**
* Prompts a user for login
*
* Login and authentication take place in application_top.php, which is included in includes/header.php.
*
* @package      BFW Toolkit
* @subpackage   Core
* @category     Pages
* @author       Michael Munger <michael@highpoweredhelp.com>
*/ 

if(file_exists('local/header.php')) {
  include ('local/header.php');
} else {
  include('includes/header.php');
}

if(isset($_GET['msg'])) {
  $PE->Configs->divAlert($_GET['msg'],'error');
}

if(file_exists('local/signin.php')) {
  include ('local/signin.php');
} else {
  include('includes/signin.php');
}
?>