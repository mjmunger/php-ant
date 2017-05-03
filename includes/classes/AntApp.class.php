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
     * Defines whether or not this app will run just once and not run again.
     * @var boolean
     */
    public $runOnce         = false;

    /**
     * Defines whether or not this app has run already. Set to true when AntApp::runOnce is set to true AND after the app has been triggered.
     * @var boolean
     */
    public $hasRun           = false;

    /**
     * A list of actions that have run already. Can be used to find out if an particular action that should only run once has run.
     * @var array
     */
    public $actionsRun       = [];

    /**
     * An array of actions in this app that should run only once. configured in the app.json file.
     * @var array
     */

    public $actionRunLimit    = [];

    /**
    * @var array $consoleMessages An array of messages that will be printed to the console.
    **/

    public $consoleMessages = [];


    /**
    * @var string $appName The name of the app as it will be displayed to users and admins.
    **/

    public  $appName    = 'OverrideMe';

    /**
    * @var string $version The version number of this app.
    **/

    public  $version       = '1';

    /**
    * @var array $hooks The hooks upon which this app will operate.
    **/

    public  $hooks         = array();

    /**
    * @var boolean $loaded A flag denoting whether or not this app is loaded.
    **/

    public  $loaded        = false;


    /**
    * @var boolean $enabled A flag denoting whether or not this app should be allowed to fire.
    **/

    public  $enabled       = false;

    /**
    * @var integer $verbosity The degree to which this app will print debugging information. This is usually inherited from the CLI object itself.
    **/

    public  $verbosity     = 0;

    /**
     * Sets whether or not the action tags are shown on the interface so you can figure out what fires where.
     * @var boolean
     */

    public $visualTrace    = false;

    /**
    * @var array $errors Errors that are assocaited with this app for this session. This  should be cleared every time we restart or when we fix an error.
    **/

    public $errors         = array();


    /**
    * @var boolean $hasACL Reports to the app engine whether or not we should check an access control list before we allow anything in the app to fire.
    **/

    public $hasACL         = false;


    /**
    * @var array $features An array of features that submit themselves to access control.
    **/

    public $features       = array();


    /**
    * @var string path The full path to where this app is stored in the file system.
    **/

    public $path             = NULL;


    /**
    * @var array $uriRegistry An array of regular expressions, which when
    *      matched, allow this app to execute code on a hook / action.
    **/

    public $uriRegistry      = [];


    /**
    * @var array $routedActions An associative array of regular expressions and actions,
    *      which are compared to a given request URI, and when matched, the action
    *      is fired in the App Engine.
    **/

    public $routedActions   = [];

    /**
     * Holds an associative array of priorites for actions that fire for routes in this app.
     * @var array
     */

    public $routedActionPriorities = [];

    /**
    * @var array $getFilters An array of get request variables that must be a)
    *      present and b) set to a certain value before the app engine will
    *      trigger the actions contained in this app.
    **/

    public $getFilters      = [];

    /**
    * @var array $postFilters An array of POST variables that must be a)
    *      present and b) set to a certain value before the app engine will
    *      trigger the actions contained in this app.
    **/

    public $postFilters    = [];


    /**
    * @var array $actionWhitelist Holds a list of actions where this app will ALWAYS
    *      fire, regardless of filtering or the registry. Whitelisted actions must be
    *      EXACT MATCH. No regex is allowed. As a matter of good practice, you
    *      should limit the amount of whitelisting you do. This is really only to
    *      be used as a workaround to have an app fire in the first step of a
    *      sequence. Subsequent steps should use GET and POST variables to
    *      activate the getFilter and postFilter functionality.
    **/

    public $actionWhitelist  = [];

    /**
     * @var mixed $currentRoute The current route that is running for an app
     *                          when runRoutedActions() is executing. Set
     *                          from within runRoutedActions(). If a current
     *                          route is not valid / matching, false.
     **/

    var $currentRoute     = false;

    //These two properties are stubs for use with phpunit.
    public $testProperty      = NULL;
    public $testPropertyArray = [];

    function __construct() {
        $this->path = __DIR__;
    }

    /**
     * Registers a URI as a regular expression to this app.
     * Example:
     *
     * <code>
     * $myURIs = array ['#\/upload\/.*','\/'];
     * $myapp->registerUri($myURIs);
     * </code>
     *
     * @return boolean True upon successful addition of the URI. False otherwise.
     * @param array An array of regular expressions or direct URLs upon which this app will execute.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function registerURI($uriList) {
        $counter = 0;
        foreach($uriList as $URI) {
            $counter += array_push($this->uriRegistry, $URI);
        }

        return ($counter>0?true:false);
    }

    /**
     * Adds routes and actions to the AntApp::routedActions array. When these
     * routes are present, the AppEngine can respond to URIs and route the
     * requests to the actions designated in the app meta as appropriate.
     *
     * Example:
     *
     * <code>
     * $result = $app->registerAppRoutes($routes);
     * </code>
     *
     * @return boolean True if the routedActions array increases in size.
     * @param array $routes an associative array containing regular expression
     *        strings as the key, and the action that should fire when a URI matches
     *        that string as the value.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function registerAppRoutes($routes) {
        //echo "<pre>"; var_dump($routes); echo "</pre>";  echo __FILE__ . ":" . __LINE__;
        $startSize = count($this->routedActions);
        $this->routedActions = array_merge($this->routedActions,$routes);
        return (count($this->routedActions) > $startSize);
    }

    function registerAppRoutePriorities($priorities) {
        $startSize = count($this->routedActionPriorities);
        $this->routedActionPriorities = array_merge($this->routedActionPriorities,$priorities);
        return (count($this->routedActionPriorities) > $startSize);
    }
    /**
     * Examines a given URI and determines if this app should fire or not.
     * Example:
     *
     * <code>
     * $shouldFire = $app->fireOnURI($Engine->Configs->Request->uri);
     * </code>
     *
     * @return boolean True if the app should fire when the requested URI is present OR no URIs have been listed. False otherwise.
     * @param string The URI we are testing.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function fireOnURI($uri) {
        //If no URIs have been registered for this app, always fire.
        if(count($this->uriRegistry) == 0) return true;

        foreach($this->uriRegistry as $regex) {
            if(preg_match($regex, $uri)) {
                $this->currentRoute = $regex;
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves the action, which should be run for routed URIs
     * Example:
     *
     * <code>
     * $action = $myapp->getRoutedAction();
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getRoutedAction($uri) {
        foreach($this->routedActions as $regex => $action) {
            if(preg_match($regex,$uri)) return $action;
        }
        //no match
        return false;
    }

    /**
     * Returns the route that fires for a specified URI. If more than one
     * route match, it returns the first one. (If you have more than one 
     * matching route, tou should write better routes than that!).
     *
     * @return string The route that fires for the URI.
     * @param  string The uri for which we want a matching route.
     **/

    function getRoute($uri) {
        foreach($this->routedActions as $regex => $action) {
            if(preg_match($regex,$uri)) return $regex;
        }
        //no match
        return false;
    }

    /**
     * Hooks this app to a hook in the system.
     *
     * When an app is created, it can operate at many different areas inside the BFW Toolkit based web application. Each hook that is added in the various pages of the web app or functions of the CLI can have any number of hooks. If this app is to operate at the time that hook is fired, it should be "hooked" to that hook using this funciton. Usually, you'll see this being executed at the bottom of app.php after the app class has been extended and defined above it. See the standard apps for reference on structure. Each hook has a signature, which is generated from the hook, the callback function, and the priority. This signature is the key of the associative array that holds all apps.
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
        // short-circuit if this app should only run one time and has already won. Remember, first app wins, so control which actually fires by properly setting app priorities!

        if($this->runOnce && $this->hasRun) return ['success' => true ];

        // Mark this app as having run at least once.
        $this->hasRun = true;

        $return = array();
        if($this->verbosity > 14) {
            echo "Triggering hooks for app: " . $this->appName . PHP_EOL;
            echo "AVAILABLE HOOKS:" . PHP_EOL;
            $args['AE']->Configs->debug_print($this->hooks,$this->appName . " hooks",true);
        }

        foreach($this->hooks as $hook) {

            $args['AE']->log($this->appName,"Hook: " . print_r($hook,true),'AppEngine.log',14);

            //Check to see if the action has already reached a run limit, and if so, bail out.


            if($requested_hook == $hook['hook']) {

                $actionRunCount = (isset( $this->actionsRun[$requested_hook] ) ? $this->actionsRun[$requested_hook] : 0);

                if(isset($this->actionRunLimit[$requested_hook]) && $actionRunCount >= $this->actionRunLimit[$requested_hook]){
                    $args['AE']->log($this->appName,"$requested_hook has reached its run limit ($actionRunCount). Not running again.");
                    return [ 'success' => true ];
                 }
                try {

                    if($this->visualTrace) $args['AE']->Configs->pageEcho(sprintf('<span class="w3-tag w3-round w3-green" style="margin:0.25em;">%s:%s</span>',$this->appName,$requested_hook));

                    $result    = call_user_func(array($this,$hook['callback']),($args?$args:false));

                    //Increment the runcount for this action in this app.
                    $actionRunCount++;
                    $this->actionsRun[$requested_hook] = $actionRunCount;

                } catch (Exception $e) {
                    //Disable this app on next load, and log the exception.
                    if($args['AE']->visualTrace) $args['AE']->Configs->pageEcho(printf('<span class="w3-tag w3-round w3-red" style="margin:0.25em;">%s:%s</span>',$this->appName,$requested_hook));
                    $args['AE']->log($this->appName,$e->getMessage());
                    $args['AE']->log($this->appName,"***DISABLING THIS APP***");
                    $args['AE']->disableApp($this->appName,$args['AE']->availableApps[$this->appName]);
                    $args['AE']->log($this->appName,"Reloading app engine...");
                    $args['AE']->reload();
                }

                if($this->verbosity > 14) {
                    $args['AE']->Configs->debug_print($result,"RESULT");
                }
                //We always return an array.
                if(!is_array($result)) {
                    /*$this->showError(sprintf("Error! The app %s is not returning an array from the function %s. All app functions should return an array as a result: even if you are just returing array('success' => true) or array('success' => false) to indicate the success of your app acation." . PHP_EOL,$this->appName,$hook['callback']));*/
                    $error = sprintf("Error! The app %s is not returning an array from the function %s. All app functions should return an array as a result: even if you are just returing array('success' => true) or array('success' => false) to indicate the success of your app action." . PHP_EOL,$this->appName,$hook['callback']);
                    //throw new \Exception($error, 0, null);
                    $args['AE']->log($this->appName,$error);
                    $args['AE']->log($this->appName,"***DISABLING THIS APP***");
                    $args['AE']->disableApp($this->appName,$args['AE']->availableApps[$this->appName]);
                    $args['AE']->log($this->appName,"Reloading app engine...");
                    $args['AE']->reload();

                }
                $return = array_merge($result,$return);
            }
        }

        return $return;
    }

    /**
     * Returns app events as permission groups (arrays). This is a DENY, ALLOW feature. By
     * default, if you have AntApp::hasACL enabled, and nothing in this array,
     * access to everything will be denied.
     * 
     * Because absence of a definition of access will be construed as "access denied",
     * all actions should be defined in this ACL list. Failure to define an action
     * will result in all users being denied access to all users except sysadmins, which
     * are exempt from ACLs.
     * 
     * Your app should (must) override this method in order to do anything useful with 
     * AntApp::hasACL set to true.
     * 
     * @return array
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    
    function getACLGroups() {
        $acl = [];
    }

    function checkACL($feature,$args) {

        $AE = $args['AE'];

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
        if(!isset($AE->current_user)) {
            $AE->Configs->divAlert(sprintf("You are attempting to trigger a app via %s that has access control enabled without passing the current_user object as an argument. Either pass the current_user object for this hook and callback, or set hasACL to false",$feature),'danger');
            $AE->Configs->divAlert('System shutdown to protect security.','danger');
            die();
        }

        /* Administrators can do whatever they want. */

        if($AE->current_user->role->users_roles_role == 'A') {
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
            $this->log("Looking to load: ".$dependency,9);

            if(file_exists($dependency)) {
                if(is_readable($dependency)) {
                    $this->log('appAutoloader',"Including: $dependency",'AppEngine.log',9);
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

    /**
     * Sets the properties of this class with values from the $options array.
     * Example:
     *
     * @return void
     * @param array $options an associative array with the class properties and their initial values.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function init($options) {

        $this->getFilters  = [];
        $this->postFilters = [];

        if(isset($options->requestFilter)) $this->setRequestFilter($options->requestFilter);

        if(isset($options->alwaysRun))     $this->importActionWhitelist($options->alwaysRun);

        foreach($options as $key => $value) {

            $filters = ['requestFilter','alwaysRun'];
            if(in_array($key, $filters)) continue;

            switch(gettype($value)) {
                case 'array':
                    $this->$key = $value;
                    break;
                case 'object':
                    $this->$key = (array) $value;
                    break;
                default:
                    $this->$key = $value;
                    break;
            }
        }

    }

    function setRequestFilter($filters) {

        $validMethods = ['GET','POST'];

        foreach($validMethods as $method) {
            if(!isset($filters->$method)) continue;

            foreach($filters->$method as $var => $value) {
                if(isset($this->AE))
                    $this->AE->log($this->appName
                                  ,sprintf("For %s, parsing key => value: %s => %s",$method,$var,$value)
                                  ,'AppEngine.log'
                                  ,14);

                switch($method) {
                    case 'GET':
                        $this->getFilters[$var] = $value;
                        //array_push($this->getFilters, [$var => $value]);
                        break;
                    case 'POST':
                        $this->postFilters[$var] = $value;
                        //array_push($this->postFilters, [$var => $value]);
                        break;
                    default:
                        // pass
                        break;
                }
            }
        }
        //echo "Count of getFilters: "  . count($this->getFilters)  . PHP_EOL;
        //echo "Count of postFilters: " . count($this->postFilters) . PHP_EOL;
    }

    /**
     * Determine if an app should not run at all because of filtering requests.
     *
     * Request filtering is the action of restricting an app's execution. When
     * get or post filters are present in the app, it will ONLY execute if
     * those variables are set during a request. If not filters are set, the
     * app will always execute (URI Routing excluded).
     *
     * @return boolean True if the app should execute. False otherwise.
     * @param object $Server the server environment instance from the AppEngine instance.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function filterOnRequest(AppEngine $Engine) {

        $Server = $Engine->Configs->Server;
        //If there are no restrictions, then return true because there are no restrictions prohibiting execution
        if(count($this->getFilters) == 0 && count($this->postFilters) == 0) return true;

        //loop through the post vars, to ensure that at least one of them allows this app to operate.

        foreach($this->postFilters as $var => $value) {

            //Soft Fail if it should be set, but it's not.
            if(isset($Server->Request->post_vars[$var])) {
                //$Engine->log('AppEngine',sprintf("POST: %s = %s?" . PHP_EOL,$Server->Request->post_vars[$var], $value));
                if($Server->Request->post_vars[$var] == $value) return true;
            }

        }

        //loop through the post vars, to ensure that at least one of them allows this app to operate.
        foreach($this->getFilters as $var => $value) {
            if(isset($Server->Request->get_vars[$var])) {
                //$Engine->log('AppEngine',sprintf("GET: %s = %s?" . PHP_EOL,$Server->Request->get_vars[$var], $value));
                if($Server->Request->get_vars[$var] == $value) return true;
            }
        }


        //If we made it this far, there is no reason for this thing to execute.
        return false;
    }

    function shouldRun(AppEngine $Engine, $requested_hook) {
        //If this URI is on the always run whitelist, return true without further processing.
        if($this->alwaysRun($Engine, $requested_hook)) {
            $Engine->log($this->appName,"$this->appName will run for $requested_hook because it's whitelisted.");
            return true;
        }

        //If we are not allowed to run on this URI, return false
        if(!$this->fireOnURI($Engine->Configs->Server->Request->uri)) {
            $Engine->log($this->appName,"$this->appName will NOT run for $requested_hook because it's restricted to specific URIs.");
            return false;
        }

        //If there are filters in place to prevent this app from running, return false.
        if(!$this->filterOnRequest($Engine)) {
            $Engine->log($this->appName,"$this->appName will NOT run for $requested_hook because it's restricted by GET or POST filters.");
            return false;
        }

        //allow the app to run by default.
        return true;
    }

    function importActionWhitelist($list) {
        foreach($list as $value) {
            array_push($this->actionWhitelist, $value);
        }
    }

    function whitelistAction($action) {
        if(!in_array($action, $this->actionWhitelist)) array_push($this->actionWhitelist, $action);
        return count($this->actionWhitelist);
    }

    function alwaysRun(AppEngine $Engine, $action) {
        $run = in_array($action, $this->actionWhitelist);
        if($run) $Engine->log($this->appName,"$action was whitelisted for $this->appName",'AppEngine.log',9);
        return $run;
    }

    function getGitHash() {
        $cmd = 'git rev-parse --short HEAD';
        $output = trim(shell_exec($cmd));
        return $output;
    }

    /**
     * Fulfils a request with a JSON response and the appropriate response code.
     * @param array $data An associative array with at least a 'success' element, and either a message or error element.
     * @param integer $code (Optional) If you supply an integer response code here, it will override the one the method determines is appropriate.
     * @return void. This is a final method, it "dies" after execution to keep responses clean.
     * */

    function respond($data, $code = false) {

        switch($data['success']) {
            case true:
                $responseCode = 200;

                //Override on success if $code is set:
                if($code !== false) $responseCode = $code;
                
                break;

            case false:
                $responseCode = 400;
                break;
        }


        http_response_code($responseCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }

    /**
     * Recursively scans the app/css directory (and all sub-directories) for CSS files, and includes them in the header.
     * For use with the header-inject-css.
     * @param array $args the standard argument array passed from the app engine.
     * */

    function injectCSS($args) {

        $format = '<link rel="stylesheet" type="text/css" href="%s"/>' . PHP_EOL;

        $Directory = new \RecursiveDirectoryIterator($this->path . '/css/');
        $Iterator  = new \RecursiveIteratorIterator($Directory);
        $files     = new \RegexIterator($Iterator, '/^.+\.css$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach($files as $file) {
            $url = $args['AE']->Configs->getWebURI($file[0]);
            printf($format,$url);
        }

        $url = $args['AE']->Configs->getWebURI($file[0]);
        printf($format,$url);
        return [ 'success' => true ];
    }

    function injectFooterJS($args) {

        $format = '<script src="%s"></script>' . PHP_EOL;

        $targetPath = $this->path . '/js/footer/';
        if(!file_exists($targetPath)) return ['success' => true];
        
        $Directory = new \RecursiveDirectoryIterator($this->path . '/js/footer/');
        $Iterator  = new \RecursiveIteratorIterator($Directory);
        $files     = new \RegexIterator($Iterator, '/^.+\.js$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach($files as $file) {
            $url = $args['AE']->Configs->getWebURI($file[0]);
            printf($format,$url);
        }

        return [ 'success' => true ];
    }

    function injectHeaderJS($args) {

        $format = '<link rel="stylesheet" type="text/css" href="%s"/>' . PHP_EOL;
        $targetPath = $this->path . '/js/header/';

        if(!file_exists($targetPath)) return ['success' => true];

        $Directory = new \RecursiveDirectoryIterator($targetPath);
        $Iterator  = new \RecursiveIteratorIterator($Directory);
        $files     = new \RegexIterator($Iterator, '/^.+\.css$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach($files as $file) {
            $url = $args['AE']->Configs->getWebURI($file[0]);
            printf($format,$url);
        }

        $url = $args['AE']->Configs->getWebURI($file[0]);
        printf($format,$url);
        return [ 'success' => true ];
    }

    function rejectAuthenticityToken() {
            http_response_code(401);
            die("You have tried to submit a request without a valid authenticity token. Can't do that.");
    }

    /**
     * Checks the ACL table to see if this user has permissions to access a given event.
     * This is a DENY, ALLOW feature! Not defined = no access granted.
     * @return boolean True if user has specific access or is an Admin. False otherwise.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

   function userCanExecute($PDO, $action, $User) {

        //Admins (users_roles_id == 1) always have access to everything.
        if($User->users_roles_id == 1 ) return true;

        //Check to see if this user has access to this event.
        $ACL = new ACL($PDO, $action);
        return $ACL->userCanExecute($User->users_id);

        //Default to false (deny access), just in case something weird happens.
        return false;
    }

}
