<?php
use PHPUnit\Framework\TestCase;

class AppEngineTest extends TestCase
{

	function testConstructor() {
		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$A = getMyAppEngine($options);

		$this->assertInstanceOf('\PDO',$A->Configs->pdo);
		$this->assertInstanceOf('\PHPAnt\Core\ConfigCLI', $A->Configs);
	}

	/**
	 * @covers AppEngine::getAppMeta
	 **/
	function testAppParser() {
		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$A = getMyAppEngine($options);

		$appPath = 'includes/apps/ant-app-test-app/app.php';
		$name = $A->getAppMeta($appPath,'name');
		$this->assertSame('Test Ant App', $name);

		$description = $A->getAppMeta($appPath,'description');
		$this->assertSame('Provides the Test Ant App for commands in the CLI.',$description);

		$version = $A->getAppMeta($appPath,'version');
		$this->assertSame('1.0', $version);
	}

	function testAppEnableDisable() {
		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$A = getMyAppEngine($options);

		//Enable the test app from this test suite.
		$appPath = $A->Configs->document_root . '/includes/apps/ant-app-test-app/app.php';
		$name = $A->getAppMeta($appPath,'name');

		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result['success']);

		//Make sure this now exists in the database.
		$query = "SELECT settings_value FROM settings where settings_key = ? LIMIT 1";
		$stmt = $A->Configs->pdo->prepare($query);
		$stmt->execute(['enabledAppsList']);
		$row = $stmt->fetchObject();
		$buffer = json_decode($row->settings_value);

		$this->assertTrue(array_key_exists($name,$buffer));
		$this->assertSame($buffer->$name,$appPath);

		//Get enabled apps.
		$A->getEnabledApps();
		$result = array_key_exists($name, $A->enabledApps);
		$this->assertTrue($result);

		//Disable the app
		$result = $A->disableApp($name,$appPath);
		$this->assertTrue($result);

		//Double check to make sure this no longer exists in the database.
		$stmt = $A->Configs->pdo->prepare($query);
		$stmt->execute(['enabledAppsList']);
		$row = $stmt->fetchObject();
		$buffer = json_decode($row->settings_value);

		$this->assertFalse(array_key_exists($name,$buffer));
	}

	/**
	 * @covers AppEngine::setVerbosity
	 **/

	function testVerbosity() {
		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$A = getMyAppEngine($options);
		$A->setVerbosity(10);

		$this->assertSame($A->verbosity,10);

		foreach($A->apps as $app) {
			$this->assertSame($app->verbosity,10);
		}
	}

	/**
	 * @depends testAppEnableDisable
	 * @covers AppEngine::loadApps
	 **/
	
	function testLoadApps() {
		$C = getMyConfigs();
		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/includes/apps/';

		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);
		
		//Make sure we set the app root to the test directories.
		$this->assertSame($A->appRoot,$appRoot);

		//Make sure appRoot exists.
		$this->assertFileExists($A->appRoot);

		//Make sure the test app was, indeed, discovered.
		$this->assertArrayHasKey('Test Ant App', $A->availableApps);

		//Make sure the file really exists.
		$this->assertFileExists($A->availableApps['Test Ant App']);

		//Enable the test app.
		$appPath = $appRoot . 'ant-app-test-app/app.php';
		$name = $A->getAppMeta($appPath,'name');
		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result['success']);


	}
	/** 
	 * @depends testLoadApps
	 * @covers AppEngine::runActions
	 * @covers AppEngine::getAppsWithRequestedHook
	 * @covers AppEngine::activateApps
	 **/
	function testAppHooks() {
		//Get the configs by themselves.
		$C = getMyConfigs();

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/includes/apps/';

		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);		

		//Enable the test app.
		$appPath = $appRoot . 'ant-app-test-app/app.php';
		$this->assertFileExists($appPath);
		$name = $A->getAppMeta($appPath,'name');
		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result['success']);

		$A->activateApps();
		$this->assertArrayHasKey($appPath, $A->activatedApps);

		//Test AppEngine::getAppsWithRequestedHook to find the unique hook 'app-hook-test'

		$hook = 'app-hook-test';
		$buffer = $A->getAppsWithRequestedHook($hook);
		
		//Loop through these, to find the one with the correct app path.
		foreach($buffer as $app) {
			if($app->path == $appPath) {
				//Do the test
				$this->assertSame($appPath, $app->path);
			}
		}

		//Test runActions
		$result = $A->runActions($hook);
		$this->assertSame($result['test-value'], 7);
		$key = '14f35998841bcf6af92f24b49ea5050b';

		$this->assertSame($key,$A->getHookKey($app,$hook));

		$result = $A->disableApp($name,$appPath);
		$this->assertTrue($result);
	}

	function testTestLinker() {
		//Get the configs by themselves.
		$C = getMyConfigs();
		$testsDir = $C->document_root . '/tests/';

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/includes/apps/';
		
		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);
		
		//Enable the test app.
		$appPath = $appRoot . 'ant-app-test-app/app.php';
		$name = $A->getAppMeta($appPath,'name');
		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result['success']);

		$A->linkAppTests();

		//Parse the namespace of the test file.
		$regex = '#(namespace) (.*);#';
		$namespace = $A->getAppMeta($appPath,'custom',$regex);

		$this->assertSame('PHPAnt\Apps', $namespace);

		$buffer = explode('\\', $namespace);

		//Make sure that the directory structure corresponding to the name space exists under document_root/tests.
		$targetPath = $testsDir;
		
		foreach($buffer as $directory) {
			$targetPath .= $directory;
			$targetPath .= '/';
			$this->assertFileExists($targetPath);
		}

		//Make sure there is a symbolic link to the tests folder under that directory structure.
		//0. Parse the app directory name.
		$appDirName = dirname($appPath);
		//1. Determine where the linked directory should be (as $appTestDirLink)
		$appTestDir = $appDirName . '/tests';
		$this->assertFileExists($appTestDir);

		//2. Create a test file in that directory
		$testFilePath = $appTestDir . '/testfile.txt';
		$fh = fopen($testFilePath,'w');
		fwrite($fh,'Test file');
		fclose($fh);

		$this->assertFileExists($testFilePath);

		//3. Assert the file exists over the LINKED directory.
		//3.a. Parse the name of the directory we should be using from the path
		$buffer = explode('/',dirname($appPath));
		$appNakedDirName = end($buffer);
		$targetFileViaSymlink = $targetPath . '/' . $appNakedDirName . '/testfile.txt';
		//$this->assertFileExists($targetFileViaSymlink);

		//$result = unlink($targetFileViaSymlink);
		//$this->assertTrue($result);
	}

	function testGetAppActions() {
		//Get the configs by themselves.
		$C = getMyConfigs();

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/includes/apps/';

		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);		


		//Enable the test app.
		$appPath = $appRoot . 'ant-app-test-app/app.php';
		$results = $A->getAppActions($appPath);

		$this->assertCount(5, $results);
	}

	function testGetAppURIs() {
		//Get the configs by themselves.
		$C = getMyConfigs();

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/includes/apps/';

		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);		


		//Enable the test app.
		$appPath = $appRoot . 'ant-app-test-app/app.php';

		//Test App URI parsing.

		$results = $A->getAppURIs($appPath);

		$this->assertCount(3, $results);

 		$uris = ['#^\/uploader\/.*#'
 				,'#^\/history\/.*#'
 				,'#^\/test\/asdf\/.*#'
 				];

 		for($x=0;$x<count($uris); $x++) {
 			$this->assertSame($uris[$x], $results[$x]);
 		}

 		//Test App Route parsing
 		$routes = $A->getAppRoutes($appPath);
 		$this->assertCount(2,$routes);

	}

	/**
	 * @covers AppEngine::enableApp
	 * @depends testAppEnableDisable
	 */
	
	public function testDisallowAppWithoutManifest() {

		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$A = getMyAppEngine($options);

		//Enable the test app from this test suite.
		$appPath            = $A->Configs->document_root . '/includes/apps/ant-app-test-app/app.php';
		$manifestPath       = $A->Configs->document_root . '/includes/apps/ant-app-test-app/manifest.xml';
		$manifestPathBackup = $A->Configs->document_root . '/includes/apps/ant-app-test-app/manifest.xml.bak';

		if(file_exists($manifestPath)) rename($manifestPath, $manifestPathBackup);
		$this->assertFileNotExists($manifestPath);

		$name = $A->getAppMeta($appPath,'name');

		$result = $A->enableApp($name,$appPath);

		$this->assertFalse($result['success']);
	
	    //put the manifest file back
		if(file_exists($manifestPathBackup)) rename($manifestPathBackup, $manifestPath);
		$this->assertFileExists($manifestPath);
	}

	/**
	 * @covers AppEngine::log
	 */
	
	public function testTestLog() {
		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$A = getMyAppEngine($options);

		$logfile = $A->Configs->getLogDir() . 'testlog.log';
		$this->assertFileNotExists($logfile);

		$A->log('test','test log message','testlog.log');
		$this->assertFileExists($logfile);

		$regex = '/[a-zA-Z]{3} [0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2} [a-zA-Z_-]{0,} .*/';
		$buffer = file_get_contents($logfile);
		$this->assertRegExp($regex, $buffer);

		unlink($logfile);

		$this->assertFileNotExists($logfile);
	}

	public function testBlacklist()	{

		$BL = new PHPAnt\Core\AppBlacklist();
		$options = getDefaultOptions();
		$options['AppBlacklist'] = $BL;

		$A = getMyAppEngine($options);

		$path = tempnam('/tmp/', "test_");
		
		$A->AppBlacklist->addToBlacklist($path);

		$this->assertTrue($A->AppBlacklist->isBlacklisted($path));

		$A->AppBlacklist->removeFromBlacklist($path);

		$this->assertFalse($A->AppBlacklist->isBlacklisted($path));
	}

	/**
	 * Tests routed actions against a list of URIs.
	 *
	 * @depends testGetAppURIs
	 * @dataProvider providerAppRoutes
	 * @covers AppEngine::RunRoutedActions
	 * @return void
	 */

	public function testAppEngineRunRoutedActions($uri,$expected) {
		//Get the configs by themselves.
		$C = getWebConfigs();

		//Set the URI
		$C->Server->Request->uri = $uri;

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . 'includes/apps/';


		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);	
		$A->Configs = $C;
		$A->Configs->Server->Request->uri = $uri;


		//Enable the test app.
		$appPath = $appRoot . '/ant-app-test-app/app.php';
		$result = $A->enableApp('Test Ant App', $appPath);
		$this->assertTrue($result['success']);

		$A->reload();

		//Check to make sure the actions are registered for the Test app.
		$foundTheApp = false;
		foreach($A->apps as $app) {
			if($app->appName == 'Test Ant App') {
				//disable access control. It's not needed here.
				$app->hasACL = false;
				
				$foundTheApp = true;
				$this->assertGreaterThan(0, count($app->routedActions));
				$isPresent = false;
				foreach($app->routedActions as $regex => $action) {
					if($action == $expected) $isPresent = true;
				}
			}
		}

		$this->assertTrue($foundTheApp);

		$this->assertTrue($isPresent);

		//Run the action
		$this->assertInstanceOf('PHPAnt\Core\AppEngine', $A);
		$result = $A->runRoutedActions();
		$this->assertArrayHasKey('test-value', $result);

		$this->assertSame($expected, $result['test-value']);

	}
	
	/**
	 * Data Provider for testAppEngineRunRoutedActions
	 *
	 * @return array
	 */
	public function providerAppRoutes() {
	    return array(['/uploader/'       , 'uploader-uri-test']
					,['/history/1234'    , 'history-uri-test' ]
					,['/test/asdf/1234'  , 'testasdf-uri-test']
	    			);
	}

	public function testAppHasACL() {
		//1. Instantiate app engine
		//Get the configs by themselves.
		$C = getWebConfigs();

		//Set the URI
		$C->Server->Request->uri = $uri;

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . 'includes/apps/';


		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);	
		$A->Configs = $C;
		$A->Configs->Server->Request->uri = $uri;


		//2. Enable the test app.
		$appPath = $appRoot . '/ant-app-test-app/app.php';
		$result = $A->enableApp('Test Ant App', $appPath);
		$this->assertTrue($result['success']);

		$A->reload();

		//Check to make sure the actions are registered for the Test app.
		$foundTheApp = false;
		foreach($A->apps as $app) {
			if($app->appName == 'Test Ant App') break;
		}

		//3. Verify the app supports ACLs.
		$this->assertTrue($app->hasACL);
	}	

	public function testGetAppACL() {
		//1. Instantiate app engine
		//Get the configs by themselves.
		$C = getWebConfigs();

		//Set the URI
		$C->Server->Request->uri = $uri;

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . 'includes/apps/';


		$options = getDefaultOptions();
		$BL = new PHPAnt\Core\AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);	
		$A->Configs = $C;
		$A->Configs->Server->Request->uri = $uri;


		//2. Enable the test app.
		$appPath = $appRoot . '/ant-app-test-app/app.php';
		$result = $A->enableApp('Test Ant App', $appPath);
		$this->assertTrue($result['success']);

		$A->reload();

		//Check to make sure the actions are registered for the Test app.
		$foundTheApp = false;
		foreach($A->apps as $app) {
			if($app->appName == 'Test Ant App') break;
		}

		$aclGroups = $app->getACLGroups();

		$this->assertSame('CLI Commands', $aclGroups['CLI']['name']);
		$this->assertCount(1, $aclGroups['CLI']['group']);
		$this->assertSame('cli-load-grammar', $aclGroups['CLI']['group'][0]);


		$this->assertSame('App Test Events', $aclGroups['tests']['name']);
		$expected = [ 'app-hook-test'    
                    , 'uploader-uri-test'
                    , 'history-uri-test' 
                    , 'testasdf-uri-test'
                    ];

        foreach($expected as $event) {
        	$this->assertContains($event, $aclGroups['tests']['group']);
        }
	}
}