<?php
use PHPUnit\Framework\TestCase;

class AppEngineTest extends TestCase
{
	function testConstructor() {
		include('config.php');
		$a = new PHPAnt\Core\AppEngine($configs,$options);
	}
}