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

        $this->setVerbosity($options['verbosity']);

        $this->loadApps();

        //Setting $options['disableApps'] = true will prevent auto-loading and activation of apps.
        if(!$this->disableApps) {
            $this->getenabledApps();
            $this->linkAppTests();
            $this->activateApps();
        }
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
        $return  = [];
        $result  = [];
        $grammar = [];

        $args['requested_hook'] = $requested_hook;
        
        //Make sure we have an instance of AppEngine as $args['AE']
        if(!isset($args['AE'])) $args['AE'] = $this;

        $appsWithRequestedHook = $this->getAppsWithRequestedHook($requested_hook);

        $this->log('AppEngine'
                  ,sprintf("There are %s apps who respond to $requested_hook.",count($appsWithRequestedHook))
                  ,'AppEngine.log'
                  ,9
                  );

        $TL = new TableLog();
        $TL->setHeader(['Hook','App','Result']);

        foreach($appsWithRequestedHook as $app) {

            //Ignore apps that have a set of URIs registered when the current
            //URI does not match. OR that have request filters, and that filter
            //is not present. (Only when we have a web environment.)

            $this->log('AppEngine',"Working with $app->appName...",'AppEngine.log',9);

            if($this->Configs->environment == ConfigBase::WEB) {
                if(!$app->shouldRun($args['AE'],$requested_hook)) {
                    $row = [$requested_hook,$app->appName,'SKIPPED'];
                    $TL->addRow($row);
                    continue;
                }
            }

            $result['success'] = false;
            try {
                $this->log('AppEngine',"Triggering $app->appName...",'AppEngine.log',9);
                $result = $app->trigger($requested_hook,$args);
            } catch (Exception $e) {
                $this->log('EXCEPTION',$e->getMessage());
            }
            $row = [$requested_hook,$app->appName,($result['success']?"OK":"FAILED")];
            $TL->addRow($row);

            //Compile grammar if it is being returned.
            if(isset($result['grammar'])) $grammar = array_merge($grammar,$result['grammar']);

            //Merge other stuff.
            $return            = array_merge($return,$result);
            $return['success'] = $result['success'];
        }

      if(count($TL->rows) > 1) {
            $this->log('AppEngine'
              ,"Triggering actions for $requested_hook" . PHP_EOL . $TL->makeTable()
              ,'AppEngine.log'
              ,9 //Min verbosity
              );
      }

        unset($app);

        $return['grammar'] = $grammar;
        return $return;
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
     * Loads all the plugins from the plugins/ directory.
     *
     * A plugin must have the following two components:
     * # Its own directory
     * # A file named plugin.php within that directory.
     *
     * Example:
     *
     * <code>
     * $AE->loadApps();
     * </code>
     *
     * This function is called at the bottom of this file. 
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

                require_once($file->getRealPath());

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

                $this->log('AppEngine',sprintf("Activating app: %s", $name),'AppEngine.log',9,1);
                //Add this file to a black list in case it causes issues, we can skip it later.
                //$this->AppBlacklist->addToBlacklist($path);

                if($this->safeMode) {
                    printf("Load %s? [Y/n]",$name);
                    $answer = trim(fgets(STDIN));
                    if($answer == "n") continue;
                }

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
                              ,sprintf("App init file path: %s [%s]", $appInitPath, ($exists?"EXISTS":"Does not exist"))
                              ,'AppEngine.log'
                              ,14);

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
        $this->getenabledApps();
        $this->activateApps();
        $this->runActions('cli-load-grammar');
        /* Load any libraries that are in the includes/libs/ directory. */
        $this->runActions('lib-loader');
        /* Load any spl-autoloaders that are contained in Apps */
        $this->runActions('load_loaders');        
        $this->log('AppEngine','Reload complete.');
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

        $timestamp = date('M d H:i:s');
        $buffer = '';
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
}