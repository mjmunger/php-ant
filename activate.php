<?php
/**
 * Account activation page
 *
 * This page allows a user of the system to activate their accounts by setting
 * a password. It also checks the password strength as well as its
 * crackability by talking to https://bugzy.highpoweredhelp.com/ to see if the
 * password is part of the common password lists. If it is an easily crackable
 * or leaked password, it will notify the user, and give them the option to
 * either: 1) change their password to something better, or 2) continue using
 * that password without any expectation or privacy or security.
 *
 * @package      BFW Toolkit
 * @subpackage   Core
 * @category     User Accounts
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

/*if(file_exists('local/header.php')) {
  include ('local/header.php');
} else {
  include('includes/header.php');
}*/

if(file_exists('local/activate.php')) {
  include ('local/activate.php');
} else {
  include('activate-default.php');
}