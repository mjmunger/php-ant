<?php
require_once('tests/test_top.php');

include_once('includes/classes/AppBlacklist.class.php');

use PHPUnit\Framework\TestCase;

class AppBlacklistTest extends TestCase
{
	function testClearInitial() {
		$BL = new PHPAnt\Core\AppBlacklist();
		$BL->clear();
		$this->assertCount(0, $BL->blacklist);
		
	}

	/**
	 * @depends testClearInitial
	 **/
	
	function testAddToBlackList() {
		$path = tempnam('/tmp/', "test_");

		$BL = new PHPAnt\Core\AppBlacklist();
		$BL->addToBlacklist($path);

		$this->assertTrue($BL->isBlacklisted($path));

		$BL->removeFromBlacklist($path);

		$this->assertFalse($BL->isBlacklisted($path));
	}

	/**
	 * @depends testAddToBlackList
	 **/

	function testUnban() {
		$BL = new PHPAnt\Core\AppBlacklist();

		$path1 = tempnam('/tmp/', "test_");
		$path2 = tempnam('/tmp/', "test_");
		$path3 = tempnam('/tmp/', "test_");

		$BL->addToBlacklist($path1);
		$BL->addToBlacklist($path2);
		$BL->addToBlacklist($path3);

		$this->assertCount(3, $BL->blacklist);

		$BL->unban(1);

		$this->assertCount(2, $BL->blacklist);
		$this->assertFalse($BL->isBlacklisted($path2));
	}

	/**
	 * @depends testUnban
	 **/

	function testClear() {
		$BL = new PHPAnt\Core\AppBlacklist();
		$this->assertCount(2, $BL->blacklist);
		$BL->clear();
		$this->assertCount(0, $BL->blacklist);
	}
}