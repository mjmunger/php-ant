<?php

class CLISetup {

	function createConfig($http_host, $sampleConfig = 'sample.config.php', $targetConfig = 'config.php') {
		
		//create web config.
		$buffer = file_get_contents($sampleConfig);
		$buffer = str_replace('%HTTPHOST%',$http_host,$buffer);
		
		$fh = fopen($targetConfig,'w');
		fwrite($fh,$buffer);
		fclose($fh);

		//create cli-config

	}

	function setupConfigs($http_host) {
		$configs = [ 'includes/sample.config.php' => 'includes/config.php'
				   , 'includes/sample-cli-config.php' => 'includes/cli-config.php'
				   ];

		foreach($configs as $sample => $target) {
			$this->createConfig($http_host,$sample,$target);
		}
	}

	/**
	 * Creates a database, but requires root to do so.
	 * Example:
	 *
	 * <code>
	 * Example Code
	 * </code>
	 *
	 * @return return value
	 * @param $rootUser     The root username of the local database server.
	 * @param $rootPassword The root password of the local database server.
	 * @param $database     The name of the database we're going to create.
	 * @param $server 		The server we are connecting to.
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/

	function createDB($server,$root, $rootpass, $database, $username, $pass1, $skel = 'setup/skel.sql') {
    	$dsn = "mysql:host=$server";
    	$pdo = null;

	    try {
	        $pdo = new PDO($dsn, $root, $rootpass);
	    } catch (Exception $e) {
	    	echo $e->getMessage();
	    	return false;
	    }

	    $sql = "DROP DATABASE IF EXISTS $database";
	    $stmt = $pdo->prepare($sql);
	    if(!$stmt->execute()) return false;

	    $sql = "CREATE DATABASE `$database`";
	    $stmt = $pdo->prepare($sql);
	    if(!$stmt->execute()) return false;

	    //Reconnect using this database.

    	$dsn = "mysql:dbname=$database;host=$server";

		try {
	        $pdo = new PDO($dsn, $root, $rootpass);
	    } catch (Exception $e) {
	    	echo $e->getMessage();
	    	return false;
	    }    	
	    $stmt = null;

	    //Import the skeleton.
	    $buffer = file_get_contents($skel);

	    $statements = explode(';', $buffer);
	    foreach($statements as $statement) {
	    	$statement = trim($statement);
	    	if(strlen($statement) == 0 ) continue;

	    	$stmt = null;
	    	$stmt = $pdo->prepare($statement);
	    	if(!$stmt->execute()) {
	    		var_dump($stmt->errorInfo());
	    		return false;
	    	}
	    }

	    //Grant god-like powers.

	    $sql = "GRANT ALL ON $database.* TO $username IDENTIFIED BY '$pass1'";
	    $stmt = $pdo->prepare($sql);
	    $vars = [$database,$username,$pass1];
    	if(!$stmt->execute()) {
    		var_dump($stmt->errorInfo());
    		return false;
    	}

	    return true;

	}

	function saveDBConnectionInfo($username,$password,$database,$server, $sampleConfig = 'includes/sample-mysql-credentials.php', $targetConfig = 'includes/mysql-credentials.php') {

		$fields = [ '%USER%'     => $username
				  , '%PASSWORD%' => $password
				  , '%DATABASE%' => $database
				  , '%HOST%'     => $server
				  ];


		$buffer = file_get_contents($sampleConfig);

		foreach($fields as $field => $value) {
			$buffer = str_replace($field, $value, $buffer);
		}
		
		$fh = fopen($targetConfig,'w');
		fwrite($fh,$buffer);
		fclose($fh);
	}

	function createDirs() {
		$dirs = ['includes/apps'
				,'includes/libs'
				,'attachments'
				];
		foreach($dirs as $dir) {
			mkdir($dir);
		}
	}

	function getDefaultApps() {
		chdir('includes/apps');

		$defaultRepos = [ 'https://github.com/mjmunger/php-ant-test-app.git'
					    , 'git@git.highpoweredhelp.com:michael/ant-app-default.git'
					    , 'git@git.highpoweredhelp.com:michael/ant-app-configs.git'
					    , 'git@git.highpoweredhelp.com:michael/ant-app-plugin-manager.git'
					    ];

		foreach($defaultRepos as $repo) {
			$cmd = sprintf("git clone %s",$repo);
			print "Running: $cmd" . PHP_EOL;
			passthru($cmd);
		}
	}

	/**
	 * Runs the interactive part of the setup, and is responsible for getting user responses.
	 * Example:
	 *
	 * @return void
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	function run() {
		//Configure the config.php file.
		print "What is your http host? (Example: http://www.yoursite.com)" . PHP_EOL;
		$http_host = trim(fgets(STDIN));

		$this->setupConfigs($http_host);

		//Setup a database.
		print "Now, let's configure your database connection. Do you have an exsiting database to connect to [y/N]" . PHP_EOL;

		$choice = strtolower(trim(fgets(STDIN)));
		if($choice !== "y") {
			//By default, we create a database!

			print "Enter the server IP or FQDN we are going to connect to. (Default: localhost)" . PHP_EOL;
			$server = trim(fgets(STDIN));
			if(strlen($server) == 0) $server = 'localhost';

			print "Enter the name of the database we are going to create. (Default: phpant)" . PHP_EOL;
			$database = trim(fgets(STDIN));
			if(strlen($database) == 0) $database = 'phpant';

			print "Enter the administrative user for this database server. (Default: root)" . PHP_EOL;
			$root = trim(fgets(STDIN));
			if(strlen($root) == 0) $root = 'root';

			print "Enter the password for this administrative user" . PHP_EOL;
			$rootpass = trim(fgets(STDIN));

			print "Now, we need to create a database user your application will use to store information. Enter this new username (Default: antuser)". PHP_EOL;
			$username = trim(fgets(STDIN));
			if(strlen($username) == 0) $username = 'antuser';

			$pass1 = 'foo';
			$pass2 = 'bar';

			while(!(strcmp($pass1, $pass2) === 0)) {
				print "Please enter a password for this new user" . PHP_EOL;
				$pass1 = trim(fgets(STDIN));
	
				print "Please confirm your password." . PHP_EOL;
				$pass2 = trim(fgets(STDIN));

				if(!(strcmp($pass1, $pass2) === 0)) print "These passwords do not match. Please re-enter them so we can be sure there are not mistakes." . PHP_EOL;
			}

			$this->createDB($server,$root, $rootpass, $database, $username, $pass1);
			$this->saveDBConnectionInfo($username,$pass1,$database,$server);

		} else {

			print "What's the server IP or FQDN we are going to connect to?" . PHP_EOL;
			$server = trim(fgets(STDIN));

			print "What the name of the database we are going to use?" . PHP_EOL;
			$database = trim(fgets(STDIN));

			print "What's the user name for your database?". PHP_EOL;
			$username = trim(fgets(STDIN));

			$pass1 = 'foo';
			$pass2 = 'bar';

			while(!(strcmp($pass1, $pass2) === 0)) {
				print "What's the password for this database user?" . PHP_EOL;
				$pass1 = trim(fgets(STDIN));
	
				print "Please confirm that password." . PHP_EOL;
				$pass2 = trim(fgets(STDIN));

				if(!(strcmp($pass1, $pass2) === 0)) print "These passwords do not match. Please re-enter them so we can be sure there are not mistakes." . PHP_EOL;
			}

			$this->saveDBConnectionInfo($username,$pass1,$database,$server);
		}

		$this->createDirs();

		$this->getDefaultApps();

		//We're done!
		print  "Setup complete. Use `php cli.php` to enter the CLI." . PHP_EOL;
	}
}