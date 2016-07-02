<?php
include('tests/test_top.php');

use PHPUnit\Framework\TestCase;

class AppManagerTest extends TestCase
{
	public function testConstructor() {
		$a = new PHPAnt\Core\AppManager();
		$this->assertSame('App Manager', $a->pluginName);
		$this->assertSame(false, $a->canReload);

	}
}