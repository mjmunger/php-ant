<?php

namespace PHPAnt\Core;

/**
 * Represents the command line interface for the BFW Toolkit.
 */

 /**
 *
 * This is the main CLI class that runs the command line interface for the BFW Toolkit.
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     CLI
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */     

class Cli {

    var $apikey            = NULL;

    /**
    * @var string $line The rame command line as it is entered from a user. 
    **/
    
    var $line              = '';

    /**
    * @var array $grammar An associative array of commands that can be executed in the CLI. 
    **/
    
    var $grammar           = '';

    /**
    * @var int $verbosity The master verbosity level of the CLI. This verbosity level should be in inherited by all plugins running in the CLI. 
    **/
    
    var $verbosity         = 0;

    /**
    * @var boolean $debugMode Flag indicating debug mode. This should not be set manually. You should use Cli::setDebug() instead. 
    **/
    
    var $debugMode         = false;

    /**
    * @var boolena $run When true, the CLI will continue to run. When false, it will shutdown and exit. 
    **/
    
    var $run               = true;

    /**
    * @var int $commandVerbosity Similar to CLI::verbosity, this is specific to the Command class that is executed when a command is run. It allows a deeper look into what is happening with a command as it is parsed and executed. Usually only used with super debug mode, which is a verbosity of 15 or greater. 
    **/
    
    var $commandVerbosity  = 0;

    /**
    * @var object $PE An instantiated class of the PluginEngine. 
    **/
    
    var $Engine                = NULL;

    /**
     * Instantiates a class of the CLI
     *
     * @package      BFW Toolkit
     * @subpackage   Core
     * @category     CLI
     * @author       Michael Munger <michael@highpoweredhelp.com>
     * @param        AppEngine $Engine. You must pass an instance of PluginEngine so the CLI can work with and act on plugins.
     * @tested       CLITest::testConstruct
     */ 
             

    function __construct(\PHPAnt\Core\AppEngine $Engine) {
        $this->Engine = $Engine;
        $this->grammar = [];
    }

    /**
     * Sets up the base grammar array and then executes the cli-load-grammar hooko to load grammar from all the available plugins.
     * Example:
     *
     * <code>
     * $c->loadGrammar();
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function loadGrammar() {
        $args = [ 'grammar'   => $this->grammar
                , 'verbosity' => $this->verbosity
                ];

        $this->grammar = $this->Engine->runActions('cli-load-grammar',$args);
    }

    /**
     * Shows the ASCII code of a given string. This is a private function that is used with super debug mode to visualize the data parsed from a command.
     * Example:
     *
     * <code>
     * showOrder('This is a test');
     * </code>
     *
     * @return void
     * @param string $buffer the string to show as raw ASCII numeric values.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    private function showOrder($buffer) {
        $x = str_split($buffer);
        foreach($x as $y) {
            printf("%s ",ord($y));
        }
        print "\n";    
    }

    /**
     * Prints two strings in columnar format.
     * Example:
     *
     * <code>
     * $c->columnPrint("Item 1", "Item 2");
     * </code>
     *
     * @return return value
     * @param string $col1 The string to be printed in column 1
     * @param string $col2 The string to be printed in column 2
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function columnPrint($col1, $col2) {
        $col1 = str_pad($col1, 20);
        printf("%s%s\n",$col1,$col2);
    }

    /**
     * Shows the current CLI Settings
     * Example:
     *
     * <code>
     * $c->showSettings();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function showSettings() {
        $settings = [];

        $this->columnPrint('Variable','Value');

        foreach($settings as $setting => $value) {
            $this->columnPrint($setting,$value);
        }

    }

    /**
     * Loads settings from the .settings.dat file for the CLI.
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return return value
     * @param  param
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @todo   Create some settings that might be useful if loaded.
     **/

    function loadSettings($settingsFile = '.settings.dat') {
        if($this->verbosity > 9) printf("Loading settings from .settings.dat...\n");

        if(!file_exists('.settings.dat')) {
            //printf(".settings.dat is missing. Use 'save settings' to save your current settings before trying to load them in the future.");
            return false;
        }

        $buffer = trim(file_get_contents($settingsFile));
        $settings = json_decode($buffer);

        foreach($settings as $setting => $value) {

            if($this->verbosity > 9) printf("Setting %s = %s\n",$setting,$value);

        }

        if($this->verbosity > 9) {
            printf("Settings loaded.\n");
        }
    }

    /**
     * Saves CLI Settings to settings.dat file. (This file should NEVER be web accessible! .htaccess prevents it by default. Be careful when modifying the .htaccess file!)
     * Example:
     *
     * <code>
     * $c->saveSettings();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @todo   Create some settings that might be useful if saved.
     **/

    function saveSettings() {
        $fp = fopen('.settings.dat','w');

        /* Create a JSON dataset for values we care about. */
        $settings = [];

        $buffer = json_encode($settings);

        if($this->verbosity > 9) printf("Settings to be written to file:\n");

        fwrite($fp,$buffer);
        fclose($fp);
        printf("Settings saved.\n");
    }

    /**
     * Sets the CLI verbosity, and sets the verbosity of all plugins to teh same level, recursively.
     * Example:
     *
     * <code>
     * $c->setVerbosity(10);
     * </code>
     *
     * @return void
     * @param integer $level The verbosity level you want. 0 is off. 1-5 is info. 5-9 is notice. 10 is debug mode. >14 is super debug mode, which may not be usable. It floods the screen with information.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function setVerbosity($level) {
        if($level > 14) {
            printf("VERBOSITY > 14! SUPER DEBUG MODE!");
            printf("Unless you're doing high level stuff, you probably just want to start the CLI with the -d option.");
        }
        $this->verbosity = $level;
        $this->Engine->setVerbosity($this->verbosity);
    }

    /**
     * Short cut to set debug mode on and verbosity to 10. If you're debugging something under normal circumstances, this is what you want. (Also sets plugin verbosity to the same level, recursively)
     * Example:
     *
     * <code>
     * $c->setDebugMode()
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function setDebugMode() {
        $this->debugMode = true;
        $this->setVerbosity(10);
    }

    /**
     * Turns off debug mode, and sets the verbosity of the CLI and all plugins to 0.
     * Example:
     *
     * <code>
     * $c->unsetDebugMode();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function unsetDebugMode() {
        $this->debugMode = false;
        $this->setVerbosity(0);
    }

    /**
     * Sets the CLI API key for Bugzy to a given value.
     * Example:
     *
     * <code>
     * $c->setApiKey('d9f0a0389f0s9j3kd098120kf');
     * </code>
     *
     * @return void
     * @param string $key the API key to use.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function setApiKey($key) {
        $this->apikey = $key;
        if($this->verbosity > 9) printf("API Key set to: %s\n", $this->apikey);
    }

    /**
     * Sets the server URI
     * Example:
     *
     * <code>
     * $c->setServerURI("https://testbfw.com");
     * </code>
     *
     * @return void
     * @param string $uri The URI of the server.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function setServerURI($uri) {
        $this->server_uri = $uri;
        printf("Server URI set to: %s\n",$this->server_uri);
    }

    /**
     * Checks to see if a value exists as a key in the tree. If it does, it returns everything at that same level of the tree as choices.
     * Example:
     *
     * @return string The choices as indicated by the grammar tree.
     * @param string $recurseFor The key we are looking for in the tree.
     * @param array $tree The associative array representing the grammar tree for the CLI.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function recurse_tokens($recurseFor, $tree) {
        /* If the token ($recurseFor) is a space, get the last word from the buffer */
        if($recurseFor = ' ' || $recurseFor = '') {
            $rl_info = readline_info();

            // Figure out what the entire input is
            $full_input = substr($rl_info['line_buffer'], 0, $rl_info['end']);

            /* Get the line buffer without the curent space at the end */
            $full_input = trim($full_input);

            /* Turn it into an array so we can get the most recent word*/
            $buffer = explode(' ', $full_input);

            /* Get teh most recent word */
            $recurseFor = $buffer[sizeof($buffer)-1];

        }
        if($this->verbosity > 14) {
            echo "Looking for $recurseFor in:\n";
            print_r($tree);
        }
        $buffer = array();
        if(array_key_exists($recurseFor, $tree)) {
            $node = $tree[$recurseFor];
            foreach($node as $choice) {
                if(is_array($choice)) {
                    array_push($buffer,key($node));
                } else {
                    array_push($buffer, $choice);
                }
            }
        }
        if($this->verbosity > 14) {
            printf("Found (and returning):\n");
            print_r($buffer);
        }
        return $buffer;
    }

    /**
     * Reads from STDIN, and traverses the grammar tree looking for matches. 
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return array The choices a user has for the given grammar pattern.
     * @param string $input Not sure why this is here. It may be left over, and may need to be removed.
     * @param string $index Not sure why this is here. It may be left over, and may need to be removed.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function traverse_tree($input,$index) {
        $rl_info = readline_info();

        $full_input = substr($rl_info['line_buffer'], 0, $rl_info['end']);
        $buffer = $full_input;

        $tabcount = 0;
        $tree = $this->grammar;
        if($this->verbosity > 14) {
            printf("\nTokens used to traverse tree: %s\n",print_r($buffer,true));
            printf("<current tree>\n");
            print_r($tree);
            printf("</current tree>\n");
            print "Character codes for the current buffer:\n";
            $this->showOrder($buffer);
        }

        if(stripos($buffer, ' ')>0) {
            if($this->verbosity > 14) {
                print "The buffer contains a space (more than one word) Checking for a grammar pattern.\n";
            }
            $tokens = explode(' ', $buffer);
            foreach($tokens as $token) {
                if($this->verbosity > 14) {
                    echo "Checking token: $token\n";
                    if($this->verbosity > 14) {
                        $this->showOrder($token);
                    }
                }
                if(!is_null($tree)) {
                    if($this->verbosity > 14) {
                        printf("Looking for the key %s in the array.",$token);
                    }
                    if(array_key_exists($token, $tree)) {
                        $tree = $tree[$token];
                        if($this->verbosity > 14) {
                            echo "Tree is now:\n";
                            var_dump($tree);
                        }
                        if(is_null($tree)) {
                            //There are no more options! Set the return values to nothing.
                            $return_values = array();
                        } else {
                            /* Only other options must be an array */
                            $key = key($tree);
                            if($this->verbosity > 14) {
                                for($t=0; $t<$tabcount; $t++) {
                                    printf("\t");
                                }
                                printf("%s found at level %s with key %s. Setting tree to its value:\n",$token,$tabcount,$key);
                                print_r($tree);
                                $tabcount++;
                            }
                            $return_values = array_keys($tree);
                            $return_values[0] = $key;
                        }
                    }
                } else {
                    if($this->verbosity > 14) {
                        printf("Apparently... we've gotten to a point where the array key doesn't exist?\n");
                    }
                    /* array key apparently doesn't exist. Not sure if we do anyting about that... or if it can even happen... */
                }
            } /* End of for loop*/
        } else {
            $return_values = array_keys($tree);
        }
        if(!isset($return_values)) {
            $return_values = array();
        }
        return $return_values;
    }

    /**
     * Callback function that readline uses to deal with CLI commands.
     * Example:
     *
     * @return array The choices a user has at the CLI.
     * @param string $input Not sure if this is needed anymore. May need to be removed in a future release.
     * @param string $index Not sure if this is needed anymore. May need to be removed in a future release.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function callback_parse_command($input, $index) {

        if($this->verbosity > 9) {
            printf("Input: %s\n",$input);
            printf("Index: %s\n", $index);
        }

        $rl_info = readline_info();

        $buffer = array();

        // Figure out what the entire input is
        $full_input = substr($rl_info['line_buffer'], 0, $rl_info['end']);
        if($this->verbosity > 9) {
            printf("\nCurrent Line Buffer: %s\n",$full_input);
            readline_redisplay();
        }

        if(stripos($full_input, ' ')) {
            $tokens = explode(' ', $full_input);
            $tree = $this->grammar;
            $buffer = '';

            foreach($tokens as $token) {
                $choices = $this->recurse_tokens($token,$tree);
                $buffer = $choices;
            }
            if($this->verbosity > 9) {
                debug_print($buffer);
            }

        } else {
            /* Send all key valus in an array */
            $buffer = array_keys($this->grammar);
        }

        return $buffer;
    }

    /**
     * Show a database error for $object->db.
     * Example:
     *
     * @return void
     * @param object $object the object we want to check for a DB error.
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @deprecated
     **/

/*    function showDBError($object) {
        try {
            if($o->db->errno) {
                printf("DATABASE ERROR (%s): %s\n",$o->db->errno,$o->db->error);
            }
        } catch (Exception $e) {
            //Do nothing when it fails.
        }
    }/*

    /**
     * Processes a command entered at the CLI.
     *
     * This contains all the logic for processing the base commands in the CLI. Notice that NEAT indentations and code comments that correspond to the grammar tree so you can see / read what decisions are being made where. This must be kept neat! Or it will quickly become unreadable / unmaintainable.
     * Example:
     *
     * @return array An associative array declaring the status / success of the operation OR some other array of information. But an array, regardless.
     * @param object $cmd An instantiated class of Command that was created from the user input at the CLI.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function processCommand(Command $cmd) {

        /*Don't do anything if the command ($cmd) is blank. */
        if($cmd->length == 0) {
            return ['success' => false];
        }

        /* save teh command to the history. */
        readline_add_history($cmd->full_command);

        /* exit */
            /* Exit the program */
            if($cmd->is('exit')) {
                printf("Exiting CLI...\n");
                $this->run = false;
                return array('success' => true);
            }

        /* load */
            /* settings */
                if($cmd->is('load settings')) {
                    $this->loadSettings();
                    return array('success' => true);
                }
        /* plugins */
            /* engine */
                /* show */
                    /* verbosity */
                        if($cmd->is('plugins engine show verbosity')) {
                            printf("AppEngine Verbosity Level: %s\n",$this->Engine->verbosity);
                            return array('success' => true);
                        }
            /* Reload */
                if($cmd->is('plugins reload')) {
                    $this->Engine->plugins = NULL;
                    $this->Engine->loadPlugins();
                    return array('success' => true);
                }
            /* show */
                if($cmd->is('plugins show')) {
                    echo str_pad("Plugin", 30);
                    echo str_pad("Status",10);
                    echo PHP_EOL;
                    foreach($this->Engine->plugins as $plugin) {
                        echo str_pad($plugin->getname(), 30);
                        echo str_pad($plugin->getStatus(),10);
                        echo PHP_EOL;
                    }
                    return array('success' => true);
                }

        /* set */
            if($cmd->startsWith('set commandVerbosity')) {
                $this->commandVerbosity=$cmd->getLastToken();
                printf("Command Parser Verbosity set to %s\n",$this->commandVerbosity);
                return array('success' => true);
            }
            /* debug */
                if($cmd->startsWith('set debug')) {
                    switch($cmd->getLastToken()) {
                        case 'on':
                            $this->setDebugMode();
                            printf("Debug Mode ON\n");
                            break;
                        case 'off':
                            $this->unsetDebugMode();
                            printf("Debug Mode OFF\n");
                            break;
                        default:
                            printf("Invalid command. Try: set debug [on|off]\n");
                            break;
                    }
                    return array('success' => true);
                }

            /* save */
                /* settings */
                    if($cmd->is('save settings')) {
                        $this->saveSettings();
                        return array('success' => true);
                    }
            /* verbosity */
                /* Set CLI Verbosity */
                if($cmd->startsWith('set verbosity')) {
                    $this->verbosity = $cmd->getLastToken();
                    printf("CLI verbosity set to %s\n",$this->verbosity);
                    return array('success' => true);
                }
        /*show */
            /* debug */
                /* environment */
                    /* dump */
                        /* grammar */
                            if($cmd->is('show debug environment dump grammar')) {
                                var_dump($this->grammar);
                                return array('success' => true);
                            }
            /* settings */
                if($cmd->is('show settings')) {
                    $this->showSettings();
                    return array('success' => true);
                }
            /* verbosity */
                if($cmd->is('show verbosity')) {
                    printf("Current verbosity level: %s\n",$this->verbosity);
                    return array('success' => true);
                }
            /* warranty */
                if($cmd->is('show warranty')) {
?>
                                  
                                  NO WARRANTY

By virtue of your ssh/console access to this system, you are deemed to have (and
be worthy of) administrator rights.

Changes and updates (including deletions) made via this CLI will probably void
your warranty because they are done without any sanity checks. You have supreme
power when you use this command line interface. Damage done to your database
or files as a result of your actions here may be permanent, unfixable, and will
most likely incur a charge from a professional to fix.

ALWAYS BACKUP YOUR FILES AND DATABASE BEFORE YOU MAKE ANY CHANGES VIA THIS CLI

Caveat Emptor. Proceed with caution. Use at your own risk. You have been warned.
<?php
                    return array('success' => true);
                }

        /* Allow plugins to process commands */
        return $this->Engine->runActions('cli-command',array('command'=>$cmd));

        /* Something should have returned a value by now. If we get here, the command is invalid. */
        return ['success' => false];
    }

    /**
     * Runs the CLI
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    
    function run() {
?>
===============================================================================
PHP-Ant CLI v1.0

Created by High Powered Help, Inc.

For technical support, email michael@highpoweredhelp.com
Help and documentation is located at https://www.highpoweredhelp.com/codex/
Use of this CLI may void your warranty. Type: 'show warranty' for details.
===============================================================================
<?php
        if($this->debugMode) {
            printf("DEBUG MODE ENABLED\n");

            try {
                $pdo = gimmiePDO();
            } catch (Exception $e) {
                print "PDO Connection failed: " . $e->getMessage() . PHP_EOL;
            }

            if($pdo) {
                print str_pad('', 80,'=');
                print PHP_EOL;
                print "MySQL PDO Cnnection Information";
                print PHP_EOL;
                print str_pad('', 80,'=');
                print PHP_EOL;

                printf("Database Connection: OK" . PHP_EOL);
                
                print str_pad('Server Information:', 20);
                print $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . PHP_EOL;
                
                print str_pad('Connection Status:', 20);
                print $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . PHP_EOL;
                
                print str_pad('PDO Client Version:', 20);
                print $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . PHP_EOL;

                print str_pad('PDO Driver Name:', 20);
                print $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
                print PHP_EOL;
                
            } else {
                //
            }
        }
        if($this->verbosity > 0) {
            print "Verbosity: $this->verbosity\n";
        }

        if(file_exists('.settings.dat')) {
                $this->loadSettings();
        }

        $this->loadGrammar();
        /*print_r($plugins);*/

        $this->Engine->runActions('cli-init', array('verbosity' => $this->verbosity));

        print "Ready.\n";
        print "";

        readline_completion_function(['\PHPAnt\Core\Cli',"traverse_tree"]);

        while($this->run) {
            $FNinput = readline("Ant*CLI> ");
            $cmd = new Command($FNinput);
            $cmd->verbosity = $this->commandVerbosity;

            if($this->verbosity > 5) {
                printf("Running command: %s\n",$cmd->full_command);
            }
            $result = $this->processCommand($cmd);

            if(isset($result['cli-command'])) {
                switch ($result['cli-command']) {
                    case 'reload-grammar':
                        $this->loadGrammar();
                        break;
                    
                    default:
                        // code...
                        break;
                }
            }
        }
        if($this->debugMode) {
            print_r(readline_info());
        }
        printf("Done.\n");
    }
}
?>