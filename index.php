<?php
namespace PHPAnt\Core;

/**
 * PHPAnt main controller.
 *
 * The main startup page. Most everything here can be configured for the
 * system you're building. There are three events that fire here: include-header,
 * show-dashboard, and include-footer. These are the essential pieces to every
 * webpage. This all exists in the PHPAnt\Core namespace (obviously), and the 
 * system is started by the includes/boostrap.php line, which not only makes
 * all the system classes available, but performs authentication, and starts the
 * app engine.
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     Pages
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

$start    = microtime(true);
$rootDir  = __DIR__;

include('includes/bootstrap.php');
//$env['Authenticator'] = $Authenticator;

$results = [];
//if($Authenticator->isApi) $results = $Engine->runRoutedActions();

//If a (single) routed action demands we stop executing after we complete routed actions, then stop execution.
if(isset($results['exit'])) die();

$Engine->runActions('include-header');

$perfIndexLogger = new \Logger('performance-index');

if(isset($current_user)) {
        $env = ['current_user' => $current_user];
        $Engine->runActions('show-dashboard');
}

$Engine->runActions('include-navigation');
$Engine->runActions('show-dashboard');
$Engine->runActions('include-footer');

$end = microtime(true);
$t = $end - $start;
$perfIndexLogger->log("Index page loaded in: $t seconds.");