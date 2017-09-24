<?php

namespace PHPAnt\Core;

class ConfigBase
{
    const WEB          = 0;
    const CLI          = 1;

    public $http_host     = '';
    public $document_root = '';
    public $environment   = '';
    public $db            = null;
    public $pdo           = null;
    public $verbosity     = 0;
    public $visualTrace   = false;

    function __construct(\PDO $pdo,$vars) {
        $this->pdo = $pdo;
        $this->http_host     = $vars['http_host'];
        $this->document_root = $vars['document_root'];
        $this->verbosity     = $vars['verbosity'];
    }

    function setVerbosity($int) {
        $this->verbosity = (int) $int;
        return $this->verbosity;
    }

    function setVisualTrace($state) {
        $this->visualTrace = $state;
        $this->setConfig('visualTrace', ($state ? 'on' : 'off'));
        return $this->visualTrace;
    }

    function getIncludesDir() {
        return $this->document_root . 'includes/';
    }

    function getLibsDir() {
        return $this->document_root . 'includes/libs/';
    }

    function getAppsDir() {
        return $this->document_root . 'includes/apps/';
    }

    function getAttachmentDir() {
        return $this->document_root . 'attachments/';
    }

    function getImagesDir() {
        return $this->document_root . 'images/';
    }

    function getLogDir() {
        return '/var/log/php-ant/';
    }

    function getRunDir() {
        return '/var/run/php-ant/';
    }

    function getDomain() {
        $buffer = str_replace('https://', '', $this->http_host);
        $buffer = str_replace('http://', '', $buffer);
        return $buffer;
    }

    /**
     * Autoloader for BFW Toolkit classes.
     *
     * This function receives the name of a class to load, and then looks in the following places for it:
     * includes/parents/
     * includes/children/
     * includes/classes
     *
     * It EXPECTS that a parent class have an 's' at the end of the name of the
     * class. For example, the database table 'users' should generate a a parent
     * class of 'users', which represents the fact that we are talking about "all
     * users", but the child class, which is used when we are manipulating an
     * individual user,  will be named 'user'. Since we NEVER instantiate a parent
     * class, but always instantiate child classes, an 's' is added to the name of
     * the class, which has been instantiated, when we attempt to load the class.
     *
     * Example:
     * when we call:
     * <code>
     * $u = new User();
     * </code>
     * The autoloader does the follwowing:
     * 1. Convert the class to all-lower-case: "User" -> "user"
     * 2. Add an "s", and look for the parent: does "includes/parents/users.class.parent.php" exist? If yes, load it.
     * 3. Look for the child class: does "includes/children/user.class.parent.php" exist? If yes, load it.
     * 4. Does this class exist as a utility class? (One that was not generated
     *    with db2class to manipulate database data - these are stored in
     *    includes/classes/). Ergo, does "includes/classes/user.class.php" exist? IF
     *    yes, load it.
     *
     * The exact function of this library is to generate candidate file paths,
     * which MIGHT exist, and load them into the $candidate_files array. Then,
     * finally, loop through that array and load all candidate files that exist in
     * FIFO order (parents before children before utility classes)
     *
     * @return void
     * @param string $class the name of the class to load
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function ant_autoloader($class) {
        if(class_exists($class,false)) return true;

        $buffer = explode('\\', $class);
        $class = end($buffer);
        $candidate_files = array();

        /* If this is not a database abstraction, then it is located in the classes directory. Try that last. */
        $candidate_path = sprintf('%s/includes/classes/%s.class.php',$this->document_root,$class);
        array_push($candidate_files, $candidate_path);

        /* Loop through all candidate files, and attempt to load them all in the correct order (FIFO) */
        foreach($candidate_files as $dependency) {
            //if($this->verbosity > 9) echo "Looking for: $dependency" . PHP_EOL;
            if(file_exists($dependency)) {
                if(is_readable($dependency)) {
                    //if($this->verbosity > 9) echo "Found: $dependency" . PHP_EOL . PHP_EOL;
                    return require_once($dependency);
                }
            }
        }
    }

    /**
     * Converts a file system path to a web accessible URI.
     * Example:
     *
     * <code>
     * $url = getWebURI('/home/user/www/includes/libs/library/resources/thing.png');
     * </code>
     *
     * @return string The web accessible URI to the file or directory (resource)
     * @param string $filesystemPath The full file system path to the resource.
     * @author Michael Munger <michael@highpoweredhelp.com>
     *
     * TEST: AntConfigTest::testBaseConfig
     **/

    function getWebURI($filesystemPath) {
        $buffer = str_replace($this->document_root, '', $filesystemPath);
        //$tmp = explode('/', $buffer);
        //array_pop($tmp);
        //$buffer = implode('/', $tmp);
        return $this->getHostFQDN() . $buffer;
    }

    /**
     * Placeholder for getSpecialValues in testing.
     * PHPUnit fails to test the ConfigBase getConfigs() unless we have this function here.
     * This function should ALWAYS be overriden.
     *
     * @return array An empty array.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getSpecialValues() {
        return ['%YEAR%' => date('Y')];
    }

    /**
     * Substitutes any magic strings for settings values
     *
     * This function will take a database saved string, like %SERVER%/local/img/logo.png and substitute a real value for that %VARIABLE%.
     *
     * @author  Michael Munger <michael@highpoweredhelp.com>
     * @param   array $settings The key => value pairs in a current settings array.
     * @return  array The current configs with the special substitutions made.
     */

    function subSpecial($settings) {

        $specialValues = $this->getSpecialValues();

        foreach($specialValues as $find => $replace) {
            foreach($settings as $key => $value) {
                /*print "FIND: $find, REPLACE: $replace IN: $value";*/
                $newValue = str_replace($find, $replace, $value);
                /*print " RESULT: $newValue" . PHP_EOL;*/
                $settings[$key] = $newValue;
            }
        }
        return $settings;
    }

    /**
     * Returns an associative array with key value pairs for each of the requested settings.
     * Example:
     *
     * <code>
     * $request = ['siteName','siteURL','adminEmail'];
     * $settings = getConfigs($request);
     * echo "Admin Email: " . $settings['adminEmail'];
     * echo "Site Name:   " . $settings['siteName'];
     * echo "Site URL:    " . $settings['siteURL'];
     * </code>
     *
     * @param  array $keys An array containing the requested settings we need from the DB.
     * @return array The current configs for the requested settings, which are used by the application and all the plugins.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getConfigs($keys) {
        /* Transform array elements to be encapsulated in single quotes. */
        //$criteria = "'" . implode("', '", $keys) . "'";
        $buffer = [];
        for($x=0;$x<count($keys); $x++) {
            array_push($buffer, "?");
        }
        $criteria = implode(', ', $buffer);

        $sql = sprintf("SELECT settings_key, settings_value FROM settings WHERE settings_key IN (%s)",$criteria);
        $stmt = $this->pdo->prepare($sql);
        if(!$stmt->execute($keys)){
            $this->debug_print($stmt->errorInfo());
            $this->debug_print($keys);
            $this->debug_print($stmt);

            return false;
        }

        $return = array();

        while($row = $stmt->fetchObject()) {
            $return[$row->settings_key] = $row->settings_value;
        }

        unset($m);
        unset($result);
        // Substitute special values
        $return = $this->subSpecial($return);
        return $return;
    }

    /**
     * Attempts to create a key-value pair as a setting. If the key already
     * exists, it will update the existing value for that key. If it does not
     * exist, it will create the key-value pair in the settings table.
     *
     * All values are stored as TEXT. You may store more complicated things
     * as JSON values. You may also pre-encrypt values prior to storage.
     *
     * Example:
     *
     * <code>
     * $result = createSetting('siteName','www.mysite.com');
     * </code>
     *
     * @return boolean. True on success, false otherwise.
     * @param string $key The key for the key value pair (name of the setting)
     * @param string $value The value for the key value pair.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function setConfig($key,$value) {
        /* First, does the key exist? */
        $query = "SELECT settings_id FROM settings WHERE settings_key = ?";
        $stmt = $this->pdo->prepare($query);

        if(!$stmt->execute([$key])) {
            $this->debug_print($stmt->errorInfo());
            $this->debug_print($squery);
            $this->debug_print($stmt);
        }

        if($stmt->rowCount() > 0) {
            /* This key already exists. Update it. */
            $row    = $stmt->fetchObject();
            $values = [$value,$row->settings_id];

            $sql    = "UPDATE settings SET `settings_value` = ? WHERE settings_id = ?";
            $stmt   = $this->pdo->prepare($sql);

            if(!$stmt->execute($values)) {
                $this->debug_print($stmt->errorInfo());
                $this->debug_print($query);
                $this->debug_print($stmt);
            }

        } else {

            /* This is a new value. Create it. */
            $sql    = "INSERT INTO settings (`settings_key`, `settings_value`) VALUES (?, ?)";
            $values = [$key,$value];
            $stmt   = $this->pdo->prepare($sql);

            if(!$stmt->execute($values)) {
                $this->debug_print($stmt->errorInfo());
                $this->debug_print($squery);
                $this->debug_print($stmt);
            }
        }

        /* If we made it this far, it was probably OK. */
        return true;
    }

    /**
     * Deletes a single key / value pair in the settings.
     * Example:
     *
     * <code>
     * delConfig('siteurl')
     * </code>
     *
     * @return boolean. True if successful, false otherwise.
     * @param string $key The key to the key value pair that will be deleted.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function delConfig($key) {

        $query = "DELETE FROM settings WHERE settings_key = ? LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $values = [$key];
        return $stmt->execute($values);
    }

    /**
     * Gets the fully qualified domain name of the site including the protocol (http vs. https).
     * Example:
     *
     * <code>
     * $site = getHostFQDN();
     * </code>
     *
     * @return string The fully qualified domain name of the host.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getHostFQDN() {
        return $this->http_host . '/';
    }

    function pageEcho($message, $comment = false) {
        //pass - don't print in the CLI.
    }
}
