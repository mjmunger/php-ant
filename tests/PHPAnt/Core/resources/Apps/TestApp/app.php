<?php

namespace PHPAnt\Apps;

/**
 * App Name: Test Ant App
 * App Description: Provides the Test Ant App for commands in the CLI.
 * App Version: 1.0
 */

 /**
 * This App adds the Test Ant App and commands into the CLI by adding in
 * the grammar for commands into an array, and returning it up the chain.
 *
 * @package      PHPAnt
 * @subpackage   TestApp
 * @category     Testing Suite
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 


class TestAntApp extends \PHPAnt\Core\AntApp implements \PHPAnt\Core\AppInterface  {

    /**
     * Instantiates an instance of the TestAntApp class.
     * Example:
     *
     * <code>
     * $AppTestAntApp = new TestAntApp();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct() {
        $this->AppName = 'Test Ant App';
        $this->canReload = false;
        $this->path = __DIR__;
    }

    /**
     * Callback for the cli-load-grammar action, which adds commands specific to this App to the CLI grammar.
     * Example:
     *
     * <code>
     * $AppTestAntApp->addHook('cli-load-grammar','loadTestAntApp');
     * </code>
     *
     * @return array An array of CLI grammar that will be merged with the rest of the grammar. 
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function loadTestAntApp() {

        $grammar['test'] = [ 'grammar' => NULL
                           ];
        
        return $grammar;
    }
    
    /**
     * Callback function that prints to the CLI during cli-init to show this App has loaded.
     * Example:
     *
     * <code>
     * $AppTestAntApp->addHook('cli-init','declareMySelf');
     * </code>
     *
     * @return array An associative array declaring the status / success of the operation.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function declareMySelf() {
        if($this->verbosity > 4 && $this->loaded ){
            print("Test Ant App App loaded.\n");
        }
        return array('success' => true);
    }

    function processCommand($args) {
        $cmd = $args['command'];

        if($cmd->startsWith('test app')) {
            $return = [ 'success' => true
                      , 'test-value' => 17
                      ];
        }

        return ['success' => true];
    }

    function doAppHookTest() {
        $data = [];
        $data['success'] = true;
        $data['test-value'] = 7;
        return $data;
    }

}

$appTestAntApp = new TestAntApp();
$appTestAntApp->addHook('app-hook-test','doAppHookTest');
$appTestAntApp->addHook('cli-load-grammar','loadTestAntApp');
$appTestAntApp->addHook('cli-init','declareMySelf');
$appTestAntApp->addHook('cli-command','processCommand');

array_push($this->apps,$appTestAntApp);