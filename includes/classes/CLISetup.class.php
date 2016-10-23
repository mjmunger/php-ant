<?php

class CLISetup {

	private $pdo           = NULL;
	private $document_root = NULL;

	function __construct($document_root) {
		$this->document_root = $document_root;
	}

	function createConfig($http_host, $document_root, $sampleConfig = 'sample.config.php', $targetConfig = 'config.php') {
		
		//create web config.
		$buffer = file_get_contents($sampleConfig);
		$buffer = str_replace('%HTTPHOST%',$http_host,$buffer);
		$buffer = str_replace('%DOCUMENT_ROOT%',$document_root,$buffer);
		
		$fh = fopen($targetConfig,'w');
		fwrite($fh,$buffer);
		fclose($fh);

		//create cli-config

	}

	function setupConfigs($http_host, $document_root) {
		$configs = [ 'includes/sample.config.php'     => 'includes/config.php'
				   , 'includes/sample-cli-config.php' => 'includes/cli-config.php'
				   ];

		foreach($configs as $sample => $target) {
			$this->createConfig($http_host, $document_root, $sample, $target);
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

    	//Reconnect, and store the PDO connection in this class so we can use it later.
    	$this->pdo = $pdo;
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
			if(!file_exists($dir)) mkdir($dir);
		}
	}

	function getDefaultApps() {
		chdir('includes/apps');

		$defaultRepos = [ 'https://github.com/mjmunger/ant-app-test-app.git'
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

	function createAdminUser($setupInfo) {

		$sql = "INSERT INTO `users`
				(`users_email`,
				`users_password`,
				`users_first`,
				`users_last`,
				`users_roles_id`)
				VALUES
				( ?
				, ?
				, ?
				, ?
				, ?
				)";
		
		$this->pdo->beginTransaction();
		$stmt = $this->pdo->prepare($sql);
		
		$vars = [$setupInfo->adminuser->username, password_hash($setupInfo->adminuser->password, PASSWORD_DEFAULT), $setupInfo->adminuser->first, $setupInfo->adminuser->last, 1];
		$result = $stmt->execute($vars);
		$this->pdo->commit();

		$username = $setupInfo->adminuser->username;
		$password = $setupInfo->adminuser->password;

		echo ($result ? "Adminsitrative user set to $username with a password of $password" . PHP_EOL : "Could not create administrative user!");
	}

	function interactiveSetup() {
		$setupInfo = [];

		//Configure the config.php file.
		print "What is your http host? (Example: http://www.yoursite.com)" . PHP_EOL;
		$http_host = trim(fgets(STDIN));

		$setupInfo['http_host'] = $http_host;

		print "Enter the path for your document root for this installation? (Default: $this->document_root)". PHP_EOL;
		$document_root = trim(fgets(STDIN));
		if(strlen($document_root) == 0) $document_root = $this->document_root;

		$setupInfo['document_root'] = $document_root;

		//Setup a database.
		print "Now, let's configure your database connection. Do you have an exsiting database to connect to [y/N]" . PHP_EOL;

		$choice = strtolower(trim(fgets(STDIN)));
		if($choice !== "y") {
			//By default, we create a database!

			print "Enter the server IP or FQDN we are going to connect to. (Default: localhost)" . PHP_EOL;
			$server = trim(fgets(STDIN));
			if(strlen($server) == 0) $server = 'localhost';

			$setupInfo['db']['server'] = $server;

			print "Enter the name of the database we are going to create. (Default: phpant)" . PHP_EOL;
			$database = trim(fgets(STDIN));
			if(strlen($database) == 0) $database = 'phpant';

			$setupInfo['db']['database'] = $database;
			
			print "Enter the administrative user for this database server. (Default: root)" . PHP_EOL;
			$root = trim(fgets(STDIN));
			if(strlen($root) == 0) $root = 'root';

			$setupInfo['db']['rootuser'] = $root;

			print "Enter the password for this administrative user" . PHP_EOL;
			$rootpass = trim(fgets(STDIN));

			$setupInfo['db']['rootpass'] = $rootpass;

			print "Now, we need to create a database user your application will use to store information. Enter this new username (Default: antuser)". PHP_EOL;
			
			$username = trim(fgets(STDIN));
			if(strlen($username) == 0) $username = 'antuser';

			$setupInfo['db']['username'] = $username;
			
			$pass1 = 'foo';
			$pass2 = 'bar';

			while(!(strcmp($pass1, $pass2) === 0)) {
				print "Please enter a password for this new user" . PHP_EOL;
				$pass1 = trim(fgets(STDIN));
	
				print "Please confirm your password." . PHP_EOL;
				$pass2 = trim(fgets(STDIN));

				if(!(strcmp($pass1, $pass2) === 0)) print "These passwords do not match. Please re-enter them so we can be sure there are not mistakes." . PHP_EOL;
			}

			$setupInfo['db']['userpass'] = $pass1;

		} else {
			$setupInfo['db']['createDB'] = false;

			print "What's the server IP or FQDN we are going to connect to?" . PHP_EOL;
			$server = trim(fgets(STDIN));
			$setupInfo['db']['server'] = $server;

			print "What the name of the database we are going to use?" . PHP_EOL;
			$database = trim(fgets(STDIN));
			$setupInfo['db']['database'] = $database;

			print "What's the user name for your database?". PHP_EOL;
			$username = trim(fgets(STDIN));
			
			$setupInfo['db']['username'] = $username;

			$pass1 = 'foo';
			$pass2 = 'bar';

			while(!(strcmp($pass1, $pass2) === 0)) {
				print "What's the password for this database user?" . PHP_EOL;
				$pass1 = trim(fgets(STDIN));
	
				print "Please confirm that password." . PHP_EOL;
				$pass2 = trim(fgets(STDIN));

				if(!(strcmp($pass1, $pass2) === 0)) print "These passwords do not match. Please re-enter them so we can be sure there are not mistakes." . PHP_EOL;
			}
			$setupInfo['db']['userpass'] = $pass1;
		}

		print "Enter your email address. (This will become the administrator account!" . PHP_EOL;
		$email = trim(fgets(STDIN));
		$setupInfo['adminuser']['username'] = $email;

		print "Enter your first name" . PHP_EOL;
		$first = trim(fgets(STDIN));
		$setupInfo['adminuser']['first'] = $first;

		print "Enter your last name" . PHP_EOL;
		$last  = trim(fgets(STDIN));
		$setupInfo['adminuser']['last'] = $last;

		$passwordsMatch = false;

		while(!$passwordsMatch) {
			print "Create a default administrator password:" . PHP_EOL;
			$pass1 = trim(fgets(STDIN));
	
			print "Confirm that password, please" . PHP_EOL;
			$pass2 = trim(fgets(STDIN));

			$passwordsMatch = (strcmp($pass1, $pass2) === 0 ? true : false);

			if(!($passwordsMatch)) print "Passwords do not match! Please re-enter." . PHP_EOL;
		}

		$setupInfo['adminuser']['password'] = $pass1;

		return $setupInfo;
	}

	/**
	 * Runs the interactive part of the setup, and is responsible for getting user responses.
	 * Example:
	 *
	 * @return void
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	function run() {

		if(file_exists('settings.json')) {
			$buffer = file_get_contents('settings.json');
			$setupInfo = json_decode($buffer);
		} else {
			$setupInfo = $this->interactiveSetup();
		}

		$this->setupConfigs($setupInfo->http_host,$setupInfo->document_root);
		print_r($setupInfo);

		if($setupInfo->db->createDB) $this->createDB( $setupInfo->db->server
					   								, $setupInfo->db->rootuser
					   								, $setupInfo->db->rootpass
					   								, $setupInfo->db->database
					   								, $setupInfo->db->username
					   								, $setupInfo->db->userpass
					   								);

		$this->saveDBConnectionInfo( $setupInfo->db->username
								   , $setupInfo->db->userpass
								   , $setupInfo->db->database
								   , $setupInfo->db->server
								   );

		$this->createDirs();

		$this->getDefaultApps();

		$this->createAdminUser($setupInfo);

		//We're done!
		print  "Setup complete. Use `php cli.php` to enter the CLI." . PHP_EOL;
	}
}