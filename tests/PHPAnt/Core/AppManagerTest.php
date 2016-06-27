<?php
use PHPUnit\Framework\TestCase;

class AppManagerTest extends TestCase
{
	public function testConstructor() {
		$a = new PHPAnt\Core\AppManager();
		$this->assertSame('App Manager', $a->pluginName);
		$this->assertSame(false, $a->canReload);

	}

   	/**
     * @depends testConstructor
     */

	function testLoadAppManager() {
		$a = new PHPAnt\Core\AppManager();
		$path = '../includes/apps/ant-app-default/plugin.php';
	}
}