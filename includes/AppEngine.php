<?php
namespace PHPAnt\Core;
/**
 * PHPAnt App Engine.
 *
 * This file contains the master app methods and the master apps array that provides
 * the hooks upon which apps can execute.
 *
 **/

class AppEngine {

    /**
     * Turns the flag on to trace actions visually in the interface.
     * @var boolean
     */
    var $visualTrace      = false;

    /**
    * @var int $verbosity The level of verbosity the App Engine is set to.
    **/

    var $verbosity        = 0;

    /**
    * @var array $apps Holds instantiated classes of apps.
    **/

    var $apps             = [];

    /**
    * @var array $enabledApps An associative array of apps that have been enabled. (name => path)
    **/

    var $enabledApps      = [];

    /**
    * @var array $availableApps An associative array of apps that are found (discovered) in the file system. (name => path)
    **/

    var $availableApps    = [];

    /**
    * @var object $PM An instance of the Permission Manager class.
    **/

    var $PM               = NULL;


    /**
    * @var object $Configs An instantiation of the a configs class. This is
    *      polymorphic and can either be a ConfigCLI class or a ConfigWeb class,
    *      which are both extended from ConfigBase.
    **/

    var $Configs          = NULL;

    var $current_user     = NULL;
    var $sortHook         = NULL;
    var $safeMode         = false;
    var $appRoot          = NULL;
    var $activatedApps    = [];
    var $disableApps      = false;
    var $AppBlacklist     = NULL;

    /**
     * Instantiates and sets up the App Engine.
     * Example:
     *
     * <code>
     * $AppEngine = new \PHPAnt\Core\AppEngine($configs,$options);
     * </code>
     *
     * @return return value
     * @param object $configs An instantiation of either COnfigCLI or ConfigWeb classes.
     * @param array  $options An associative array of various options for
     *        configuring the behavior of the AppEngine.
     * <code>
     *        $options = [ 'appRoot'     => (string)  The forced root where apps are stored / accessed. Optional. <br>
     *                   , 'disableApps' => (boolean) When set to true, prevents AppEngine from loading any apps upon initialization. <br>
     *                   ];
     * </code>
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct($configs, $options) {
        //Deal with defaults.
        if(!isset($options['appRoot']))     $options['appRoot']      = 'includes/apps/';
        if(!isset($options['disableApps'])) $options['disableApps']  = false;
//        if(!isset($options['verbosity']))   $options['verbosity']    = 0;

        $this->Configs      = $configs;

        $this->appRoot      = $options['appRoot'];
        $this->safeMode     = $options['safeMode'];
        $this->PM           = $options['permissionManager'];
        $this->AppBlacklist = $options['AppBlacklist'];
        $this->disableApps  = $options['disableApps'];

        $this->loadApps();

        //Setting $options['disableApps'] = true will prevent auto-loading and activation of apps.
        if(!$this->disableApps) {
            $this->getEnabledApps();
            $this->linkAppTests();
            $this->activateApps();
        }

        $this->setVerbosity($options['verbosity']);
        $visualTraceState = (isset($options['visualTrace']) ? $options['visualTrace'] : "off");

        $this->setVisualTrace($visualTraceState);
    }

    /**
     * Creates the app signature so it can be uniquely identified in the system.
     *
     * @return mixed an associative array with the name and signature of the app.
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

/*    function getappSignature($path) {
        $hash = md5($path);
        return $hash;
    }*/

    /**
     * Enables a app by adding it to the enabledAppsList in the configs.
     *
     * @return boolean True on success, false otherwise.
     * @param array $key Associative array as returned from appEngine::getappSignature()
     * @author Michael Munger <michael@highpoweredhelp.com>
     * Tested with testAppEnableDisable()
     **/
    function enableApp($name,$path) {
        //If the path doesn't exist, don't try to enable it!
        if(!file_exists($path)) {
            $message = "Not going to try to enable $name because $path doesn't exist or is inaccessible!";
            $this->log($message);
            return ['success' => false, 'message' => $message ];
        }
        //Check to make sure it's not blacklisted.
        if($this->AppBlacklist->isBlacklisted($path)) {
            $this->log('AppEngine',"You cannot enable $name ($path) because it has been blacklisted. You must first remove it from the blacklist, and then try again.",'AppEngine.log',0,false,'warning');
        }

        $this->log('AppEngine',sprintf('Enabling %s (%s)',$name,$path));
        if($name === false) {
            $this->Configs->divAlert("Could not enable app! It doesn't have a name. That's bad. Imagine going through life with out a name. Everyone would be like: 'Hey you!' all the time. You should name this plugin so we can enable it later.",'alert');
            return false;
        }

        $manifestPath = dirname($path) . '/manifest.xml';
        if(!file_exists($manifestPath)) return ['success' => false,'message' => 'All apps must have a manifest file. Please generate a manifest file for this app before enabling it.'];

        //Make sure this app has a manifest file.

        $this->enabledApps[$name] = $path;
        $this->Configs->setConfig('enabledAppsList',json_encode($this->enabledApps));
        return ['success'=>true,'message'=>"App successfully enabled. Use apps reload to activate it"];
    }

    /**
     * Disables an app by removing it from the enabledAppsList in the Configs.
     *
     * @return Boolean. True on success, false otherwise.
     * @param $name string the name of the app to be enabled (must match the app name as defined in the app)
     * @param $path string the full path to the app to be disabled.
     * @author Michael Munger <michael@highpoweredhelp.com>
     * Tested with testAppEnableDisable()
     **/

    function disableApp($name, $path) {
        $this->log('AppEngine',sprintf('Disabling %s (%s)',$name,$path));
        unset($this->enabledApps[$name]);
        return $this->Configs->setConfig('enabledAppsList',json_encode($this->enabledApps));
    }

    /**
     * Gets a list (object) of all the enabled apps.
     * Example:
     *
     * Tesed with testAppEnableDisable()
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function getEnabledApps() {
        $enabled = $this->Configs->getConfigs(['enabledAppsList']);
        if(count($enabled) > 0) $this->enabledApps = json_decode($enabled['enabledAppsList'],true);

        foreach($this->availableApps as $name => $path) {
            /* If the app name starts with a "+" we need to add it to the list of auto-enabled plugins. */
            if($name[0] == "+" && !$this->disableApps && !in_array($path, $this->enabledApps)) {
                $status = 'Auto';
                if(!$this->enableApp($name,$path)) throw new Exception("Could not enable app $name in $path", 1);
            }
        }
    }

    /**
     * Sets the verbosity of the AppEngine and all apps in it.
     * Example:
     *
     * <code>
     * $AppEngine->setVerbosity(10);
     * </code>
     *
     * @param int $int desired Verbosity
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @test testVerbosity()
     **/

    function setVerbosity($int) {
        $this->verbosity = $int;

        $this->log('AppEngine',sprintf("AppEngine verbosity set to: %s",$this->verbosity),'AppEngine.log',1);

        $this->Configs->setVerbosity($int);

        foreach($this->apps as $app) {
            $app->verbosity = $this->verbosity;
        }
    }

    function setVisualTrace($state) {
        $this->visualTrace = $state;

        $this->log('AppEngine',sprintf("Visual trace set to: %s",($this->visualTrace ? "on" : "off")),'AppEngine.log',1);

        $this->Configs->setVisualTrace($this->visualTrace);

        foreach($this->apps as $app) {
            $app->visualTrace = $this->visualTrace;
        }

        return $this->visualTrace;
    }

    function showRoutedCodePath($uri) {
        $appsWhoFire = [];

        $uri = str_replace($this->Configs->http_host, '', $uri);

        foreach($this->apps as $app) {
            /*Skip non-enabled apps*/
            //if(!$app->enabled) continue;

            /* Add them to the requested hook array as a way of queing them to execute. */
            if($app->fireOnURI($uri)) array_push($appsWhoFire, $app);
        }

        $TL = new TableLog();
        foreach($appsWhoFire as $app) {
            $action = (strlen($app->getRoutedAction($uri)) > 0 ? $app->getRoutedAction($uri) : "All");
            if($action != 'All') {
                foreach($app->hooks as $sig => $meta) {
                    if($meta['hook'] == $action) {
                        $priority = $meta['priority'];
                        break;
                    }
                }
            } else {
                $priority = 'None';
            }
            $TL->setHeader(['App','App Path','Action','Priority']);
            $row = [ $app->appName
                   , $app->path
                   , $action
                   , $priority
                   ];
            $TL->addRow($row);
        }

        $TL->showTable();
    }

    function getAppsWithRequestedHook($requested_hook) {
        $TL = new TableLog();
        $TL->setHeader(['App','Priority']);
        /* This array holds the apps we are going to fire because they have the hook registered.*/
        $appsWithRequestedHook = [];

        /* Go through the apps and collect all the ones that will fire for this action. */
        foreach($this->apps as $app) {
            /*Skip non-enabled apps*/
            if(!$app->enabled) continue;

            /* Add them to the requested hook array as a way of queing them to execute. */
            if($app->usesHook($requested_hook)) array_push($appsWithRequestedHook, $app);
        }

        /* Store the requested hook in the class, so we can access it from the callback. */
        $this->sortHook = $requested_hook;

        /* Sort the apps based on the hooks priority so they execute in order */
        usort($appsWithRequestedHook, array('self','sortByHookPriority'));

        $this->log('AppEngine'
                  ,sprintf("For hook: %s, app firing order is:" . PHP_EOL, $this->sortHook)
                  ,'AppEngine.log'
                  ,14
                  ,true);

            foreach($appsWithRequestedHook as $app) {
                $hash = $this->getHookKey($app,$this->sortHook);
                $TL->addRow([$app->appName,$app->hooks[$hash]['priority']]);
            }


                $this->log('AppEngine'
                  ,$TL->makeTable()
                  ,'AppEngine.log'
                  ,14
                  ,true);

        return $appsWithRequestedHook;
    }

    /**
     * Run actions for a specific hook.
     *
     * This function runs all the actions for a hook in priority order.
     *
     * Example:
     *
     * <code>
     * runActions('init');
     * </code>
     *
     * @return return value
     * @param string $requested_hook The hook for which we want to run actions.
     * @param array  $args Optional. An associative array containing arguments that are passed from the action.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function runActions($requested_hook,$args=false) {
        $actionReturnValues  = [];
        $finalResult         = [];
        $grammar             = [];

        $args['requested_hook'] = $requested_hook;

        //Make sure we have an instance of AppEngine as $args['AE']
        if(!isset($args['AE'])) $args['AE'] = $this;

        $appsWithRequestedHook = $this->getAppsWithRequestedHook($requested_hook);

        $this->log('AppEngine',str_pad("=",80,"="),'AppEngine.log',9);
        $this->log('AppEngine',"RUNNING ACTION: $requested_hook",'AppEngine.log',9);
        $this->log('AppEngine',str_pad("=",80,"="),'AppEngine.log',9);
        $this->log('AppEngine'
                  ,sprintf("There are %s apps who respond to $requested_hook."
                    ,count($appsWithRequestedHook)
                  )
                  ,'AppEngine.log'
                  ,9
                  );

        $TL = new TableLog();
        $TL->offset = 41;
        $TL->setHeader(['Hook','App','Result']);

        foreach($appsWithRequestedHook as $app) {

            //Ignore apps that have a set of URIs registered when the current
            //URI does not match. OR that have request filters, and that filter
            //is not present. (Only when we have a web environment.)

            $this->log('AppEngine',"Working with $app->appName...",'AppEngine.log',9);

            if($this->Configs->environment == ConfigBase::WEB) {
                if(!$app->shouldRun($args['AE'],$requested_hook)) {
                    $row = [$requested_hook,$app->appName,'RESTRICTED'];
                    $TL->addRow($row);
                    continue;
                }
            }

            $result['success'] = false;
            try {
                $this->log('AppEngine',"Triggering $app->appName...",'AppEngine.log',9);
                $actionReturnValues = $app->trigger($requested_hook,$args);
            } catch (Exception $e) {
                $this->log('EXCEPTION',$e->getMessage());
            }
            if(!isset($actionReturnValues['success'])) echo "$app->appName not returning a success for $requested_hook!" . PHP_EOL;
            $row = [$requested_hook,$app->appName,($actionReturnValues['success']?"OK":"FAILED")];
            $TL->addRow($row);

            //Compile grammar if it is being returned.
            if(isset($actionReturnValues['grammar'])) $grammar = array_merge($grammar,$actionReturnValues['grammar']);

            //Merge other stuff.
            $finalResult            = array_merge($finalResult,$actionReturnValues);
            //$return['success'] = $result['success'];
        }

      if(count($TL->rows) > 1) {
            $this->log('AppEngine'
              ,"Triggering actions for $requested_hook" . PHP_EOL . $TL->makeTable()
              ,'AppEngine.log'
              ,9 //Min verbosity
              );
      }

        unset($app);

        $finalResult['grammar'] = $grammar;
        return $finalResult;
    }

    /**
     * Returns the hash for a given hook in an app.
     * Example:
     *
     * <code>
     * //TBD
     * </code>
     *
     * @return string The hash value for the hook.
     * @param object $app the app we are going to search the hooks of.
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @tested testAppHooks
     **/

    function getHookKey($app,$hook) {
        foreach($app->hooks as $key => $arr) {
            if($arr['hook'] == $this->sortHook) {
                return $key;
            }
        }
    }

    function sortByHookPriority($a, $b) {

        /* Put this in a single var, because lines 253 will get angry if I use $this...*/
        $hook  = $this->sortHook;

        /* Find the array element that corresponds to this hook. */
        $hash1 = $this->getHookKey($a,$this->sortHook);
        $hash2 = $this->getHookKey($b,$this->sortHook);

        if($a->hooks[$hash1]['priority'] == $b->hooks[$hash2]['priority']) return 0;

        return ($a->hooks[$hash1]['priority'] < $b->hooks[$hash2]['priority']) ? -1 : 1;
    }

    /**
     * Parses app information from the app.php file.
     * Example:
     *
     * <code>
     * $path = '/path/to/app.php';
     * $name = $AppEngine->getAppMeta($path,'name');
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @tested testAppParser
     **/

    function getAppMeta($path,$type,$regex=NULL) {
        switch ($type) {
            case 'name':
                $pattern = "#(App Name:)(.*)$#";
                break;
            case 'description':
                $pattern = "#(App Description:)(.*)$#";
                break;
            case 'version':
                $pattern = "#(App Version:)(.*)$#";
                break;
            case 'custom':
                $pattern = $regex;

            default:
                # code...
                break;
        }
        $buffer  = file($path);
        $matches = NULL;
        foreach($buffer as $line) {
            $line = trim($line);
            preg_match($pattern, $line,$matches);
            if(count($matches)) {
                return trim($matches[2]);
            }
        }
        return false;
    }

    /**
     * Retrieves app actions, hooks, and priorities from the app file.
     * Example:
     *
     * <code>
     * $actions = $AE->getAppActions('/path/to/app.php')
     * </code>
     *
     * @return mixed Associative array of actions, their callbacks, and priorities.
     * @param string $path the full path to the app to be parsed.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getAppActions($path) {
        $matches = NULL;
        $regex = '/(App Action:) (.*) -> (.*) @ ([0-9]{1,2})/';
        $buffer = file_get_contents($path);
        $results = [];

        preg_match_all($regex, $buffer, $matches, PREG_SET_ORDER);

        foreach($matches as $match) {
            $hook = trim($match[2]);
            $callback = trim($match[3]);
            $priority = trim($match[4]);
            $results[$hook] = [$callback => $priority];
        }

        return $results;
    }

    /**
     * Retrieves registered URIs from App description.
     * Example:
     *
     * <code>
     * $uriList = $AE->getAppURIs('/path/to/app.php')
     * </code>
     *
     * @return array an array containing the list of regular expressions used
     *         to match a URI for a given app.
     * @param string $path the full path to the app to be parsed.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getAppURIs($path) {
        $matches = NULL;
        $regex = '/(App URI:) *[\'"]{1}(.*)[\'"]{1}/';
        $buffer = file_get_contents($path);
        $results = [];

        preg_match_all($regex, $buffer, $matches, PREG_SET_ORDER);

        foreach($matches as $match) {
            array_push($results, trim($match[2]));
        }

        return $results;
    }

    /**
     * Parses and retrieves regular expressions from app meta to determine
     * app URIs and their associated actions which will be run when the
     * URI satisfies the regular expression.
     * Example:
     *
     * <code>
     * $routes = $Engine->getAppRoutes('/path/to/some/app');
     * </code>
     *
     * @return array An associative array of key value pairs in the form of $regex => $action
     * @param string $path The full path to the app to be parsed.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getAppRoutes($path) {
        $pattern    = '/(App URI:) *([\'"]{1}(.*)[\'"]{1}) *-> *([a-zA-Z-]*)/s';
        $pattern    = '/(App URI:) *([\'"]{1}(.*)[\'"]{1}) *-> *([a-zA-Z-]*)( *@ *([0-9]{1,2})){0,1}/s';
        $buffer     = file($path);
        $matches    = NULL;
        $routes     = [];
        $priorities = [];

        foreach($buffer as $line) {
            //Reset so we don't screw something up.
            $priority = false;
            $line = trim($line);
            preg_match($pattern, $line,$matches);
            if(count($matches) === 0) continue;

            $regex  = $matches[3];
            $action = $matches[4];

            $routes[$regex] = $action;

            //Capture optional routed action priority if it's there.
            if(count($matches) == 7) $priority = (int) $matches[6];

            //If it's not, default to 50 for backwards compatibility.
            if(!$priority) $priority = 50;

            $priorities[$action] = $priority;
        }

        return [ 'routes'     => $routes
               , 'priorities' => $priorities
               ];
    }

    /**
     * Loads all the apps from the apps/ directory.
     *
     * A app must have the following two components:
     * # Its own directory
     * # A file named app.php within that directory.
     *
     * Example:
     *
     * <code>
     * $AE->loadApps();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @tested testLoadApps
     **/

    function loadApps() {

        $counter = 0;
        $iterator = new \RecursiveDirectoryIterator($this->appRoot,\RecursiveDirectoryIterator::SKIP_DOTS);
        $TL = new TableLog();
        $TL->setHeader(['Plugin','Status','Path']);
        foreach( new \RecursiveIteratorIterator($iterator) as $file)
        {
            $counter++;
            if($file->getBasename() == 'app.php') {

                $name = $this->getAppMeta($file->getRealPath(),'name');
                $this->availableApps[$name] = $file->getRealPath();

                //Check the blacklist to see if this failed last time.
                $path = $file->getRealPath();

                if($this->AppBlacklist->isBlacklisted($path) && !$this->AppBlacklist->disabled) {
                    $this->log('AppEngine',sprintf("Not loading %s because it has been blacklisted. It will be disabled because it had problems before.",$path));
                    //disable the app
                    $this->log('AppEngine',sprintf("Disabling the app since it was blacklisted.",$path));
                    $this->disableApp($name,$path);
                    //remove it from the blacklist.
                    $this->AppBlacklist->removeFromBlacklist($path);
                    $this->log('AppEngine',sprintf("App removed from blacklist because it was disabled.",$path));
                    $this->reload();
                    continue;
                };


                //Add this file to a black list in case it causes issues, we can skip it later.
                //$this->AppBlacklist->addToBlacklist($path);

                //require_once($file->getRealPath());

                //Remove the file from the blacklist because there was not a fatal error.
                //$this->AppBlacklist->removeFromBlacklist($path);


                if($name === false) throw new \Exception(sprintf('You have an app without a name, so it is not available. Consider removing / fixing it to make this warning go away. (%s)',$file->getRealPath()), 1);

                $status = ($name?'Available':'Missing');


                $TL->addRow([$name,$status,$file->getRealPath()]);
            }
        }
        $this->log("AppEngine",$TL->makeTable(),'AppEngine.log',9,1);
    }

    /**
     * Include()'s app files that have been enabled.'
     * Example:
     *
     * <code>
     * $AE->activateApps();
     * </code>
     *
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @tested testAppHooks
     **/
    function activateApps() {

        //Reset apps
        $this->apps = [];

        //Reset activated apps.
        $this->activatedApps = [];

        /* We need this list of paths to determine if we should load things (easily) */
        $paths = array_values($this->enabledApps);

        /* Cycle through available plugins to decide if each plugin's path is
           in the $paths array and the availableApps list. */

        foreach($this->enabledApps as $name => $path) {

            if(in_array($path, $paths)) {

            //If the path doesn't exist, don't try to enable it!
            if(!file_exists($path)) {
                $message = "Not going to try to activate $name because $path doesn't exist or is inaccessible!";
                $this->log("AppEngine",$message);
                continue;
            }

                $this->log('AppEngine',sprintf("Activating app: %s", $name),'AppEngine.log',9,1);
                //Add this file to a black list in case it causes issues, we can skip it later.
                //$this->AppBlacklist->addToBlacklist($path);

                if($this->safeMode) {
                    printf("Load %s? [Y/n]",$name);
                    $answer = trim(fgets(STDIN));
                    if($answer == "n") continue;
                }

                require_once($path);

                $manifestPath = dirname($path) . '/manifest.xml';
                if(!file_exists($manifestPath)) {
                    $this->disableApp($name,$path);
                    throw new \Exception("All apps must have a manifest file! I couldn't find one here: $manifestPath. This app has been disabled.", 1);
                }

                //Read the XML file in that app directory so we know what hooks to create.
                $xml = simplexml_load_file($manifestPath);
                $className = $xml['name'];
                $nameSpace = $xml['namespace'];
                if(!$nameSpace || $nameSpace == 'PHPAnt\\Core') $nameSpace = '\\PHPAnt\\Core';

                $appClass = $nameSpace . '\\' . $className;

                //Instantiate a new class of the app.
                try {

                    $app = new $appClass($this);

                    //Remove the file from the blacklist because there was not a fatal error.
                    $this->AppBlacklist->removeFromBlacklist($path);

                    $this->log( "AppEngine"
                              , "Created an instance of " . get_class($app)
                              ,'AppEngine.log'
                              ,14
                              );

                    $appInitPath = dirname($path) . '/app.json';
                    $exists = file_exists($appInitPath);

                    $this->log("AppEngine"
                              ,sprintf("App init file path: %s [%s]", $appInitPath, ($exists?"EXISTS":"Does not exist, and that's OK. It's optional."))
                              ,'AppEngine.log'
                              ,9);

                    //Load init vars from the json init file if it exists.
                    if($exists) {
                        $options = json_decode(file_get_contents($appInitPath));

                        $app->init($options,true);

                        //Verbose message.
                        $this->log('AppEngine',sprintf('Init ran for app: %s', $name),'AppEngine.log',9);

                        //Debugging message.
                        $this->log('AppEngine'
                                  ,sprintf('Options from json file:' . PHP_EOL . print_r($options,true))
                                  ,'AppEngine.log'
                                  ,14);

                        //Debugging message.
                        $this->log('AppEngine'
                                  ,sprintf('getFilters:' . PHP_EOL . print_r($app->getFilters,true))
                                  ,'AppEngine.log'
                                  ,14);

                        //Debugging message.
                        $this->log('AppEngine'
                                  ,sprintf('postFilters:' . PHP_EOL . print_r($app->postFilters,true))
                                  ,'AppEngine.log'
                                  ,14);
                    }

                } catch (Exception $e) {
                    echo "Tried to instantiate a new class of $appClass". PHP_EOL;
                    echo $e->getMessage();
                }

                if(count($xml->action) === 0) $this->log('WARNING',"The app $name ($path) has NO actions. It will not do anything.");

                //Create all the hooks referenced in the manifest file.
                foreach($xml->action as $action){
                    $hook             = (string)$action->hook;
                    $callbackFunction = (string)$action->function;
                    $app->addHook($hook,$callbackFunction);
                }

                //Enable it in the app itself!
                $app->enabled = true;

                //Register the declared URIs for the app.
                $uriList = $this->getAppURIs($path);
                $app->registerURI($uriList);

                foreach($uriList as $uri) {
                    $this->log( 'AppEngine'
                              , sprintf("Registered URI %s to %s" , $uri, $app->appName)
                              , 'AppEngine.log'
                              , 9
                              );
                }

                //Register routes for this app.
                $meta = $this->getAppRoutes($path);
                $app->registerAppRoutes($meta['routes']);
                $app->registerAppRoutePriorities($meta['priorities']);

                array_push($this->apps,$app);
                $this->activatedApps[$path]= $name;

            }
        }
    }

    /**
     * Recurses through available apps, and symlinks their test directories to document_root/tests
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function linkAppTests() {
        $regex = '#(namespace) (.*);#';
        foreach($this->availableApps as $name => $path) {
            //Determine the namespace, and convert to a path under document_root/tests/
            $namespace = $this->getAppMeta($path,'custom',$regex);
            //invert the slashes
            $namespace = str_replace('\\', '/', $namespace);

            $dirParts = explode('/',dirname($path));
            $appDirName = end($dirParts);

            $targetPath = $this->Configs->document_root . '/tests/' . $namespace;
            if(!file_exists($targetPath)) mkdir($targetPath,0744,true);

            $link = $targetPath . '/' . $appDirName;

            //Build the target for the symlink.
            //1. Determine the directory for the app.
            $appDir = dirname($path);

            //2. If the tests directory does not exist, create it.
            $testsDir = $appDir . '/tests';

            if(!file_exists($testsDir)) throw new \Exception("All apps must have unit tests. The current app $name does not have a tests/ directory in the app directory $appDir", 1);

            //Determine where the tests directory is for the app.
            //Link the 'tests' directory as the target for the dirname if the symlink does not already exist.
            if(!file_exists($link)) {
                if(!symlink($testsDir, $link)) echo "Symlink $testsDir -> $link failed";
            }
        }
    }

    function reload() {
        $this->log('AppEngine','Reloading');
        //Reload and reactivate the apps.
        $this->getEnabledApps();
        $this->activateApps();

        //Re-load the grammar for the CLI.
        $this->runActions('cli-load-grammar');

        /* Load any libraries that are in the includes/libs/ directory. */
        $this->runActions('lib-loader');

        /* Load any spl-autoloaders that are contained in Apps */
        $this->runActions('load_loaders');

        $this->log('AppEngine','Reload complete.');
    }

    function truncateLog($file = 'AppEngine.log') {
        $logPath = $this->Configs->getLogDir() . $file;
        $fp = fopen($logPath,'w');
        fwrite($fp,'');
        fclose($fp);
    }

    /**
     * Logs a message to the specified log file.
     * Example:
     *
     * <code>
     * $Engine->log("this is a message!");
     * </code>
     *
     * @param string $component The app or component that we are logging about.
     * @param string $message The message to be added to the log file.
     * @param string $file Optional. The log file to be created (or appended)
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function log($component,$message,$file = 'AppEngine.log',$minimumVerbosity = 0, $debugPrint = false, $divAlert= false) {
        if($this->verbosity < $minimumVerbosity) return false;

        //if($debugPrint) $this->Configs->debug_print($message);

        //if($divAlert) $this->Configs->divalert($message,$divAlert);

        if(!file_exists($this->Configs->getLogDir())) mkdir($this->Configs->getLogDir());

        $logPath = $this->Configs->getLogDir() . $file;

        //$remoteIp = $this->Configs->Request->ip;
        $timestamp = date('M d H:i:s');
        $buffer = '';
        //$buffer .= str_pad($remoteIp, 18);
        $buffer .= str_pad($timestamp, 16);
        $buffer .= str_pad($component, 25);
        $buffer .= $message;
        $buffer .= PHP_EOL;

        $fp = fopen($logPath,'a+');
        fwrite($fp,$buffer);
        fclose($fp);
    }
    /**
     * Error handler for the AppEngine
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function handleError($errno , $errstr, $errfile, $errline, $context ) {
        $message = sprintf("ERROR (%s): %s triggered in %s:%s"
                          ,$errno
                          ,$errstr
                          ,$errfile
                          ,$errline);

        $this->log('ERROR',$message);

        //$message = sprintf("Error Context: %s", print_r($context,true));
        //$this->log('ERROR',$message);
    }

    /**
     * Executes events based on declared routes.
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return mixed Array of results from fired actions.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function runRoutedActions() {
        $results = [];
        $actions = [];
        $args    = [];

        foreach($this->apps as $app) {
            //Find apps that respond to the current URI.
            if($app->fireOnURI($this->Configs->Server->Request->uri)) {
                //Loop through events that fire for this app on this URI.
                $action = $app->getRoutedAction($this->Configs->Server->Request->uri);
                //$this->runActions($action);

                if($action) {
                    //Check ACL for this action here. If fail, continue.
                    if($app->hasACL && ($app->shouldRun($this,$action) == false)) {
                        echo '<div class="w3-panel w3-red">';
                        echo '  <h3>Danger!</h3>';
                        echo '  <p>You do not have permissions to perform the requested action.</p>';
                        echo '</div> ';
                        continue;
                    }

                    //Get priority.
                    $priority = $app->routedActionPriorities[$action];

                    //Add this app to that action list.
                    //$actions[$action] = [ $priority => & $app ];
                    $data = [$priority, $action, $app];
                    array_push($actions, $data);
                }
            }
        }

        // Now, we have a list of actions, and the apps that should run (along with the priorities there), so:
        // 1. Let's loop through the actions, and
        // 2. let's sort each of those arrays by priority.
        // 3. Run the actions in that order.


        //Loop through all our actions.
        array_multisort($actions);

        /**
         * At this point, we have a multidimensional array like this:
         *
         * array(2) {
         *   [0]=>
         *   array(3) {
         *     [0]=>
         *     int(40)
         *     [1]=>
         *     string(24) "include-admin-navigation"
         *     [2]=>
         *     string(5) "&$app"
         *   }
         *   [1]=>
         *   array(3) {
         *     [0]=>
         *     int(50)
         *     [1]=>
         *     string(15) "manage-projects"
         *     [2]=>
         *     string(5) "&$app"
         *   }
         * }
         *
         * So, next, we need to loop through this array, and break out the list
         * into individual action lists. First, we need to build a unique list
         * of actions that will be run.
         */

         $buffer = [];
         foreach($actions as $executionSet) {
             array_push($buffer,$executionSet[1]);
         }

        $uniqueActions = array_unique($buffer);

        /**
         * Next, we need to loop through the actions and execute the requested
         * hooks in order. When a given hook gives us 'exit' => true, we need to
         * continue rather than fully exit.
         */

         foreach($uniqueActions as $action) {
             foreach($actions as $executionSet) {
                if($action == $executionSet[1]){
                    $args['requested_hook'] = $action;
                    $args['AE'] = & $this;
                    $return = $executionSet[2]->trigger($action,$args);
                    $results = array_merge($results,$return);
                    // if(isset($return['exit']) && $return['exit']) continue;
                }
             }
        }

        $results['success'] = true;
        // $results['exit']    = true;
        return $results;
    }

    /**
     * Convenience fascade function that gives access to PDO object in a sane manner.
     * 
     * @return object A PDO Prepared statement object.
     * */

    public function prepare($sql) {
        return $this->Configs->pdo->prepare($sql);
    }

    /**
     * Convenience fascade function that returns the App Engine's PDO instance.
     * @return object the PDO object of the App Engine.
     * */

    public function getPDO() {
        return $this->Configs->pdo;
    }

    /**
     * Convenience fascade function that returns the logged in user.
     * @return object The current logged in user.
     * */

    public function getCurrentUser() {
        return $this->current_user;
    }
}
