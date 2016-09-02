<?php

if(file_exists('local/header.php')) {
  include ('local/header.php');
} else {
  include('includes/header.php');
}

if(file_exists('local/resetpass.php')) {
  include ('local/resetpass.php');
} else {
  include('includes/resetpass.php');
}