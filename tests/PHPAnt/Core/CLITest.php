<?php
require_once('tests/test_top.php');

use PHPUnit\Framework\TestCase;

class CLITest extends TestCase
{
	private function getMyCLI() {
		$C = getMyConfigs();
		$appRoot = $C->document_root;
		$A = getMyAppEngine($appRoot);
		
		$this->assertInstanceOf('PHPAnt\Core\AppEngine', $A);

		//Enable the test app from this test suite.
		$appPath = $A->Configs->document_root . '/tests/PHPAnt/Core/resources/Apps/TestApp/app.php';
		$name = $A->getAppMeta($appPath,'name');

		$result = $A->enableApp($name,$appPath);
		$this->assertTrue($result);

		$A->activateApps();

		$CLI = new PHPAnt\Core\Cli($A);
		return $CLI;		
	}
	/**
	 * @covers \PHPAnt\Core\Cli::__construct()
	 **/

	function testConstruct() {
		$CLI = $this->getMyCLI();

		$this->assertInstanceOf('\PHPAnt\Core\AppEngine', $CLI->Engine);

		//Make sure the grammar array starts empty.
		$this->assertCount(0, $CLI->grammar);
	}

	/**
	* @covers Cli:setDebugMode
	* @covers Cli:unsetDebugMode
	**/		

	function testDebugMode() {
		$C = $this->getMyCLI();
		$C->setDebugMode();
		$this->assertTrue($C->debugMode);
		$this->assertSame($C->verbosity, 10);
		$this->assertGreaterThan(0, count($C->Engine->availableApps));

		foreach($C->Engine->apps as $app) {
			$this->assertSame($app->verbosity, 10);
		}

		$C->unsetDebugMode();
		$this->assertFalse($C->debugMode);
		$this->assertSame($C->verbosity, 0);

		foreach($C->Engine->apps as $app) {
			$this->assertSame($app->verbosity, 0);
		}		
	}

	/**
	* @covers Cli:setVerbosity
	**/

	function testVerbosity() {
		$C = $this->getMyCLI();
		$C->setVerbosity(7);
		$this->assertSame($C->verbosity, 7);
		$this->assertSame($C->Engine->verbosity,7);
	}

	function testSetAPIKey() {
		$key = 'asd0fuq9038jsdfv98u';
		$C = $this->getMyCLI();
		$C->setApiKey($key);
		$this->assertSame($C->apikey,$key);
	}
}