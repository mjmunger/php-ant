<?php

  /**
   * BFW Starting Page
   *
   * The main startup page. Most everything here can be configured for the
   * system you're building. THe only thing that is really, truely necessary
   * is the inclusion of includes/header.php and includes/footer.php, which
   * contain important things like the includsion of application_top.php in
   * header.php as well as hooks for events.
   *
   * @package      BFW Toolkit
   * @subpackage   Core
   * @category     Pages
   * @author       Michael Munger <michael@highpoweredhelp.com>
   */ 


	$start = microtime(true);

  if(file_exists('local/header.php')) {
    include ('local/header.php');
  } else {
    include('includes/header.php');
  }

	$perfIndexLogger = new logger('performance-index');
	
	if(isset($current_user)) {
      $env = ['current_user' => $current_user];
      $Engine->runActions('show-dashboard', $env);
	}

if(file_exists('local/footer.php')) {
    include ('local/footer.php');
  } else {
    include('includes/footer.php');
  }

	$end = microtime(true);
	$t = $end - $start;
	$perfIndexLogger->log("Index page loaded in: $t seconds.");