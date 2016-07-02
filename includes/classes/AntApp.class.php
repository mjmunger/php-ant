<?php

/**
 * Represents a PHPAnt app.
 */

 /**
 *
 * This is the base app class, which is extended in order to create apps inside the BFW Toolkit.
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     Apps
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */     

namespace PHPAnt\Core;

Class AntApp
{


    /**
    * @var array $consoleMessages An array of messages that will be printed to the console. 
    **/

    var $consoleMessages = [];
    

    /**
    * @var string $appName The name of the app as it will be displayed to users and admins. 
    **/
    
    var  $appName    = 'OverrideMe';

    /**
    * @var string $version The version number of this app. 
    **/
    
    var  $version       = '1';

    /**
    * @var array $hooks The hooks upon which this app will operate. 
    **/
    
    var  $hooks         = array();

    /**
    * @var boolean $loaded A flag denoting whether or not this app is loaded.
    **/
    
    var  $loaded        = false;


    /**
    * @var boolean $enabled A flag denoting whether or not this app should be allowed to fire.
    **/
    
    var  $enabled       = false;

    /**
    * @var integer $verbosity The degree to which this app will print debugging information. This is usually inherited from the CLI object itself. 
    **/
    
    var  $verbosity     = 0;


    /**
    * @var array $errors Errors that are assocaited with this app for this session. This  should be cleared every time we restart or when we fix an error. 
    **/

    var $errors         = array();


    /**
    * @var boolean $hasACL Reports to the app engine whether or not we should check an access control list before we allow anything in the app to fire. 
    **/
    
    var $hasACL         = false;


    /**
    * @var array $features An array of features that submit themselves to access control. 
    **/
    
    var $features       = array();


    /**
    * @var string path The full path to where this app is stored in the file system. 
    **/
    
    var $path             = NULL;

    

    function __construct() {
        $cmd = "svn info | grep Revision | awk -F ':' '{ print $1 }'";
        $this->version = trim(shell_exec($cmd));
        $this->path = __DIR__;
    }  


    /**
     * Hooks this app to a hook in the system.
     * 
     * When a appg is created, it can operate at many different areas inside the BFW Toolkit based web application. Each hook that is added in the various pages of the web app or functions of the CLI can have any number of hooks. If this app is to operate at the time that hook is fired, it should be "hooked" to that hook using this funciton. Usually, you'll see this being executed at the bottom of app.php after the app class has been extended and defined above it. See the standard apps for reference on structure. Each hook has a signature, which is generated from the hook, the callback function, and the priority. This signature is the key of the associative array that holds all apps.
     * Example:
     *
     * <code>
     * $appDefaultGrammar = new DefaultGrammar();
     * $appDefaultGrammar->addHook('cli-load-grammar','loadDefaultGrammar');
     * $appDefaultGrammar->addHook('cli-init','declareMySelf');
     * </code>
     *
     * @return Void
     * @param string  $hook      The text hook within the web application or CLI that will initiate the callback, which this app will handle.
     * @param string  $callback  The name of the function that will be executed when the hook is encountered during execution of the web app or CLI.
     * @param integer $priority  The priority (ordering) of this app's hook and callback in relation to others. A priority may be a value between 1 and 100. Hooks that have lower priorities are executed first whereas hooks with higher priorities are executed later. This allows you to effectively order which apps will fire and in what order for a given hook. The default value is 50.
     * @param array   $arguments An array of arguments that will be passed to the app's hooked callback function during execution. Defaults to NULL.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function addHook($hook, $callback, $priority = 50, $arguments = NULL)
    {
        $sig = md5($hook.$callback.$priority);
        $this->hooks[$sig]=array('hook'      => $hook
                                ,'callback'  => $callback
                                ,'priority'  => $priority
                                ,'arguments' => $arguments
                                );
    }

    /**
     * Determines whether or not this app has a specific hook registered.
     * Example:
     *
     * <code>
     * if($someapp->usesHook('foo-hook')) echo "This app uses hook: foo-hook";
     * </code>
     *
     * @return boolean True if this pluing has the hook in its "hooks" property, false otherwise.
     * @param string $hook the hook we are testing for
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function usesHook($requestedHook) {

        foreach($this->hooks as $hook) {
            if($hook['hook'] == $requestedHook) {
                return true;
            }
        }
        return false;
    }

    /**
     * Triggers (fires) a app.
     * Example:
     *
     * <code>
     * $p->trigger();
     * </code>
     *
     * @return array The merged arrays of each of the resulting callback functions. Each function called with a app is triggered must return an array. Each function called during that execution must also return an array. These arrays may be returned "up" the cascaded path from this function, or they may drop the data. However, an array must ultimately be returned to this function after callback execution or it will throw an error.
     * @param string $requested_hook The hook for which we are executing callback functions.
     * @param boolean $args Arguments passed to the callback function. Defaults to false.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function trigger($requested_hook,$args = false)
    {
        $return = array();
        if($this->verbosity > 14) {
            echo "Triggering hooks for app: " . $this->appName . PHP_EOL;
            echo "AVAILABLE HOOKS:" . PHP_EOL;
            $args['PE']->Configs->debug_print($this->hooks,$this->appName . " hooks",true);            
        }

        foreach($this->hooks as $hook) {

            if($this->verbosity > 14) {
                $args['PE']->Configs->debug_print($hook,"HOOK");
                //$args['PE']->Configs->debug_compare($requested_hook, $hook['hook']);
            }

            if($requested_hook == $hook['hook']) {

                if($args) {
                    $result    = call_user_func(array($this,$hook['callback']),$args);
                } else {
                    $result    = call_user_func(array($this,$hook['callback']));
                }

                if($this->verbosity > 14) {
                    $args['PE']->Configs->debug_print($result,"RESULT");
                }
                //We always return an array.
                if(!is_array($result)) {
                    /*$this->showError(sprintf("Error! The app %s is not returning an array from the function %s. All app functions should return an array as a result: even if you are just returing array('success' => true) or array('success' => false) to indicate the success of your app acation." . PHP_EOL,$this->appName,$hook['callback']));*/
                    $error = sprintf("Error! The app %s is not returning an array from the function %s. All app functions should return an array as a result: even if you are just returing array('success' => true) or array('success' => false) to indicate the success of your app action." . PHP_EOL,$this->appName,$hook['callback']);
                    throw new \Exception($error, 0, null);
                }
                $return = array_merge($result,$return);
            }
        }
        if($this->verbosity > 14) {
            //debug_print($return,"RETURN");
        }
        return $return;
    }

    function checkACL($feature,$args) {

        $PE = $args['PE'];

        /*echo "<pre>"; print_r($args); echo "</pre>";*/

        /* Check to see if we need to reference the ACL */
        if(!$this->hasACL) {
            /* If ACL is not enabled, short-circuit with a return true to allow access to the app. */
            return true;
        }

        if(!array_key_exists($feature, $this->features)) {
            throw new Exception("You're trying to verify ACL for a feature not declared in the app's feature list. Cannot verify user access to this feature.", 0, null);
            return false;
        }        

        /* From this point forward, we need the user object to determine access. */
        if(!isset($PE->current_user)) {
            $PE->Configs->divAlert(sprintf("You are attempting to trigger a app via %s that has access control enabled without passing the current_user object as an argument. Either pass the current_user object for this hook and callback, or set hasACL to false",$feature),'danger');
            $PE->Configs->divAlert('System shutdown to protect security.','danger');
            die();
        }

        /* Administrators can do whatever they want. */
        
        if($PE->current_user->role->users_roles_role == 'A') {
            return true;
        }

        
        /* was the current user passed? */

        /* Are they allowed to trigger this app? */
    }
     

    /**
     * Sets the verbosity level for the app (and should set the verbosity level of all sub-functions, and sub-apps)
     * Example:
     *
     * <code>
     * $p->setVerbosity(10);
     * </code>
     *
     * @return void
     * @param integer $int The desired verbosity level (debugging information to be shown)
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function setVerbosity($int) {
        $this->verbosity = $int;
    }

    /**
     * Gets the name of the app.
     * Example:
     *
     * <code>
     * echo $p->getName();
     * </code>
     *
     * @return string The name of this app.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function getName() {
        return $this->appName;
    }

    /**
     * Gets the version of this app.
     * Example:
     *
     * <code>
     * echo $p->getVersion();
     * </code>
     *
     * @return integer The version of this app.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    public function getVersion() {
        return $this->version;
    }

    /**
     * Shows the status of this app (in human readable text)
     * Example:
     *
     * <code>
     * printf("This app is: %s\n",$p->getStatus());
     * </code>
     *
     * @return string The human readable text of the app status.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    public function getStatus() {
        return ($this->loaded == true?"Loaded":"Available");
    }

    /**
     * Tells the system whether or not the app is reloadable.
     * Example:
     *
     * <code>
     * if($p->canReload()) { //do this; }
     * </code>
     *
     * @return boolean True if you can reload the app without restarting the script, or false otherwise (a script restart is required.)
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    public function canReload() {
        return $this->canReload;
    }

    /**
     * Shows an error as defined by the $msg. (Usually used in the CLI)
     * Example:
     *
     * <code>
     * $p->showError($msg);
     * </code>
     *
     * @param mixed $msg Either a string message (in the case of a single error) or an array of text errors, which can all be displayed at once. 
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    
    public function showError($msg) {
        printf(str_pad('', 80,'*') . PHP_EOL);
        if(is_array($msg)) {
            foreach($msg as $m) {
                echo $m . PHP_EOL;
            }
        } else {
            printf("Error: $msg" . PHP_EOL);
        }
        printf(str_pad('', 80,'*') . PHP_EOL);
    }

    public function appAutoloader($class) {

        $baseDir = __DIR__;

        $candidate_files = array();
        
        /* If this is not a database abstraction, then it is located in the classes directory. Try that last. */
        $candidate_path = sprintf($baseDir.'/classes/%s.class.php',$class);
        array_push($candidate_files, $candidate_path);

        /* Loop through all candidate files, and attempt to load them all in the correct order (FIFO) */
        foreach($candidate_files as $dependency) {
            $this->log("Looking to load: ".$dependency,14);

            if(file_exists($dependency)) {
                if(is_readable($dependency)) {
                    if($this->verbosity > 9) {
                        $this->consoleLog("Including: %s", $dependency);
                    }
                    include($dependency);
                }
            }
        }
        return ['success' => true];
    }  

    public function consoleLog($message) {
        array_push($this->consoleMessages,$message);
    }

    public function dumpMessages() {
        printf("<script>");
        foreach($this->consoleMessages as $message) {
            if(strpos($message, PHP_EOL)) {
                $lines = explode(PHP_EOL, $message);
            } else {
                $lines = [$message];
            }

            foreach($lines as $line) {
                if(strlen($line) > 0) printf(PHP_EOL . "console.log('%s');" . PHP_EOL,addslashes($line));
            }
        }
        printf("</script>");
        return ['success' => true];
    }

    public function Log($message,$minimumVerbosity = 10) {
        if($this->verbosity >= $minimumVerbosity) echo $message . PHP_EOL;
    }
}
