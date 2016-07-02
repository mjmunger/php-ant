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

    function __construct($configs, $options) {
        //Deal with defaults.
        if(!isset($options['appRoot']))     $options['appRoot']      = 'includes/apps/';
        if(!isset($options['disableApps'])) $options['disableApps'] = false;

        $this->Configs     = $configs;

        $this->appRoot     = $options['appRoot'];
        $this->safeMode    = $options['safeMode'];
        $this->PM          = $options['permissionManager'];
        $this->disableApps = $options['disableApps'];

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
        if($name === false) {
            divAlert("Could not enable app! It doesn't have a name. That's bad. Imagine going through life with out a name. Everyone would be like: 'Hey you!' all the time. You should name this plugin so we can enable it later.",'alert');
            return false;
        }

        if(!file_exists($path)) return false;

        $this->enabledApps[$name] = $path;
        $this->Configs->setConfig('enabledAppsList',json_encode($this->enabledApps));
        return true;
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
        
        //$this->Configs->Log(sprintf("Plugin Engine verbosity set to: %s " . PHP_EOL, $int));

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
            /* Add them to the requested hook array as a way of queing them to execute. */
            if($app->usesHook($requested_hook)) array_push($appsWithRequestedHook, $app);
        }

        /* Store the requested hook in the class, so we can access it from the callback. */
        $this->sortHook = $requested_hook;

        /* Sort the apps based on the hooks priority so they execute in order */
        usort($appsWithRequestedHook, array('self','sortByHookPriority'));

        if($this->verbosity > 9) {
            print PHP_EOL;
            printf("For hook: %s, app firing order is:" . PHP_EOL, $this->sortHook);
            print PHP_EOL;

            foreach($appsWithRequestedHook as $app) {
                $hash = $this->getHookKey($app,$this->sortHook);
                $TL->addRow([$app->appName,$app->hooks[$hash]['priority']]);
            }

        $TL->showTable();

        }
        
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
        $return = [];

        //Make sure we have an instance of AppEngine as $args['AE']
        if(!isset($args['AE'])) $args['AE'] = $this;

        $appsWithRequestedHook = $this->getAppsWithRequestedHook($requested_hook);

        foreach($appsWithRequestedHook as $app) {
            try {
                if($this->verbosity > 14) {
                    echo str_pad("Requested Hook: ",20);
                    echo $requested_hook;
                    echo PHP_EOL;

                    echo str_pad("Triggering app:",20);
                    echo $app->getName();
                    echo PHP_EOL;
                }                
                $result = $app->trigger($requested_hook,$args);
            } catch (Exception $e) {
                $this->Configs->divAlert($e->getMessage(),'danger');
                $error = "<pre>" . $e->getTraceAsString() . "</pre>";
                echo "<pre>" . $error . "</pre>";
            }
            $return = array_merge($return,$result);
        }
        unset($app);
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

        if($this->verbosity > 11) {


            print "A:" . PHP_EOL;
            /*var_dump($a);*/

            print str_pad("Hash", 15);
            print $hash1 . PHP_EOL;

            print str_pad("Hook",15);
            print $hook . PHP_EOL;

            print str_pad("Priority",15);
            print $a->hooks[$hash1]['priority'] . PHP_EOL;
            

            print "B:" . PHP_EOL;
            /*var_dump($b);*/

            print str_pad("Hash", 15);
            print $hash2 . PHP_EOL;

            print str_pad("Hook",15);
            print $hook . PHP_EOL;            

            print str_pad("Priority",15);
            print $b->hooks[$hash2]['priority'] . PHP_EOL;
        }

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
                require_once($file->getRealPath());
                $name = $this->getAppMeta($file->getRealPath(),'name');

                if($name === false) throw new Exception(sprintf('You have an app without a name, so it is not available. Consider removing / fixing it to make this warning go away. (%s)',$file->getRealPath()), 1);

                $status = ($name?'Available':'Missing');

                $this->availableApps[$name] = $file->getRealPath();

                /* If the app name starts with a "+" we need to add it to the list of auto-enabled plugins. */
                if($name[0] == "+" && !$this->disableApps) {
                    $status = 'Auto';
                    $this->enableApp($name,$file->getRealPath());
                }
                $TL->addRow([$name,$status,$file->getRealPath()]);
            }
        }
        if($this->verbosity > 9) $TL->showTable();
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
        //Reset activated apps.
        $this->activatedApps = [];

        /* We need this list of paths to determine if we should load things (easily) */
        $paths = array_values($this->enabledApps);

        /* Cycle through available plugins to decide if each plugin's path is in the $paths array and the availableApps list. */
        foreach($this->availableApps as $name => $path) {

            if(in_array($path, $paths)) {

                if($this->verbosity > 9) printf("Activating plugin: %s" . PHP_EOL, $name);

                $manifestPath = dirname($path) . '/manifest.xml';
                if(!file_exists($manifestPath)) {
                    throw new \Exception("All apps must have a manifest file! I couldn't find one here: $manifestPath.", 1);
                    continue;
                }

                //Read the XML file in that app directory so we know what hooks to create.
                $xml = simplexml_load_file($manifestPath);
                $className = $xml['name'];
                $nameSpace = $xml['namespace'];
                $appClass = $nameSpace . $className;

                //Instantiate a new class of the app.
                $app = new $appClass();

                //Create all the hooks referenced in the manifest file.
                foreach($xml->action as $action){
                    $hook             = (string)$action->hook;
                    $callbackFunction = (string)$action->callback;
                    $app->addHook($hook,$callbackFunction);
                }

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
            if(!file_exists($targetPath)) mkdir($targetPath,0700,true);

            $link = $targetPath . '/' . $appDirName;

            //Build the target for the symlink.
            //1. Determine the directory for the app.
            $appDir = dirname($path);

            //2. If the tests directory does not exist, create it.
            $testsDir = $appDir . '/tests';

            if(!file_exists($testsDir)) throw new \Exception("All apps must have unit tests. The current app $name does not have a tests/ directory in the app directory $appDir", 1);

            //Determine where the tests directory is for the app.
            //Link the 'tests' directory as the target for the dirname if the symlink does not already exist.
            if(!file_exists($link)) symlink($testsDir, $link);
        }
    }
}