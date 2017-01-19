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
$rootDir  = __DIR__ . '/';

include('includes/bootstrap.php');
//$env['Authenticator'] = $Authenticator;

$results = [];
//if($Authenticator->isApi)

if($Engine->visualTrace) printf('<span class="w3-tag w3-round w3-blue" style="margin:0.25em;">%s:%s</span> ','Index Front Controller','Routed Actions Begin');
$results = $Engine->runRoutedActions();


//If a (single) routed action demands we stop executing after we complete routed actions, then stop execution.
if(isset($results['exit'])) {
    if($Engine->visualTrace) printf('<span class="w3-tag w3-round w3-blue" style="margin:0.25em;">%s:%s</span> ','Index Front Controller','Routed Actions End. Terminating');
    die();
}

if($Engine->visualTrace) printf('<span class="w3-tag w3-round w3-blue" style="margin:0.25em;">%s:%s</span>','Index Front Controller','Front Controller (Non-Routed Actions Begin)');
$Engine->runActions('include-header');

if(isset($current_user)) {
        $env = ['current_user' => $current_user];
        $Engine->runActions('show-dashboard');
}

$Engine->runActions('include-navigation');
$Engine->runActions('show-dashboard');
$Engine->runActions('include-footer');

if($Engine->visualTrace) printf('<span class="w3-tag w3-round w3-blue" style="margin:0.25em;">%s:%s</span>','Index Front Controller','Front Controller (Non-Routed Actions End)');
$end = microtime(true);
$t = $end - $start;
