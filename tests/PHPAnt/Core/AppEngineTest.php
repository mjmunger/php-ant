<?php
use PHPUnit\Framework\TestCase;

class AppEngineTest extends TestCase
{

	private function getMyConfigs() {
		//Setup test.
		$vars = getMockVars();
		$pdo = gimmiePDO();
		$C = new PHPAnt\Core\ConfigCLI($pdo, $vars);
		return $C;
	}
	private function getMyAppEngine($appPath = 'includes/apps/') {
		$C = $this->getMyConfigs();

		$PM = new PHPAnt\Core\PermissionManager();

		$options = ['safeMode' 		 => false
				   ,'permissionManager' => $PM
				   ,'verbosity'         => 0
				   ];

		$A = new PHPAnt\Core\AppEngine($C,$options,$appPath);
		return $A;		
	}

	function testConstructor() {
		$A = $this->getMyAppEngine();

		$this->assertInstanceOf('\PDO',$A->Configs->pdo);
		$this->assertInstanceOf('\PHPAnt\Core\ConfigCLI', $A->Configs);
	}

	/**
	 * @covers AppEngine::getAppMeta
	 **/
	function testAppParser() {
		
		$A = $this->getMyAppEngine();

		$appPath = 'tests/PHPAnt/Core/resources/Apps/TestApp/app.php';
		$name = $A->getAppMeta($appPath,'name');
		$this->assertSame('Test Ant App', $name);

		$description = $A->getAppMeta($appPath,'description');
		$this->assertSame('Provides the Test Ant App for commands in the CLI.',$description);

		$version = $A->getAppMeta($appPath,'version');
		$this->assertSame('1.0', $version);
	}

	function testAppEnableDisable() {
		$A = $this->getMyAppEngine();

		//Enable the test app from this test suite.
		$appPath = $A->Configs->document_root . '/tests/PHPAnt/Core/resources/Apps/TestApp/app.php';
		$name = $A->getAppMeta($appPath,'name');

		$result = $A->enableApp($name,$appPath);

		$this->assertTrue($result);

		//Make sure this now exists in the database.
		$query = "SELECT settings_value FROM mcdb2.settings where settings_key = ? LIMIT 1";
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
		$A = $this->getMyAppEngine();
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
		$C = $this->getMyConfigs();
		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/tests/PHPAnt/Core/resources/Apps/';
		$A = $this->getMyAppEngine($appRoot);
		
		//Make sure we set the app root to the test directories.
		$this->assertSame($A->appRoot,$appRoot);

		//Make sure appRoot exists.
		$this->assertFileExists($A->appRoot);

		//Make sure the test app was, indeed, discovered.
		$this->assertArrayHasKey('Test Ant App', $A->availableApps);

		//Make sure the file really exists.
		$this->assertFileExists($A->availableApps['Test Ant App']);

		//Enable the test app.
		$appPath = $appRoot . 'TestApp/app.php';
		$name = $A->getAppMeta($appPath,'name');
		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result);


	}
	/** 
	 * @depends testLoadApps
	 * @covers AppEngine::runActions
	 * @covers AppEngine::getAppsWithRequestedHook
	 * @covers AppEngine::activateApps
	 **/
	function testAppHooks() {
		//Get the configs by themselves.
		$C = $this->getMyConfigs();

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . '/tests/PHPAnt/Core/resources/Apps/';
		$A = $this->getMyAppEngine($appRoot);


		//Enable the test app.
		$appPath = $appRoot . 'TestApp/app.php';
		$name = $A->getAppMeta($appPath,'name');
		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result);

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
}