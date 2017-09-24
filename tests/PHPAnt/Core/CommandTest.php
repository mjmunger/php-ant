<?php
require_once('tests/test_top.php');

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
	function testConstruct() {
		$line = "test app\n";
		$C = new PHPAnt\Core\Command($line);
		$this->assertSame(trim($line), $C->raw_command);
		$this->assertSame(strtolower(trim($line)), $C->full_command);
		$this->assertSame(strlen(trim($line)),$C->length);
		$this->assertCount(2, $C->tokens);
	}

	/**
	* @covers Command:startsWith
	**/		

	function testStartsWith() {
		$line = "test app\n";
		$C = new PHPAnt\Core\Command($line);
		$this->assertTrue($C->startsWith('test'));
		$this->assertFalse($C->startsWith('testx'));
	}

	/**
	* @covers Command:contains
	**/		

	function testContains() {
		$line = "test app\n";
		$C = new PHPAnt\Core\Command($line);
		$this->assertTrue($C->contains('app'));
		$this->assertTrue($C->contains('test'));
		$this->assertTrue($C->contains('test app'));
		$this->assertTrue($C->contains('st ap'));
		
		$this->assertFalse($C->contains('xapp'));
		$this->assertFalse($C->contains('xtest'));
		$this->assertFalse($C->contains('xtest app'));
		$this->assertFalse($C->contains('xst ap'));		
	}

	function testEndsWith() {
		$line = "test app\n";
		$C = new PHPAnt\Core\Command($line);
		$tests = ['j46G W9lu 8Qcw 9KNA S7kz Q139 9Rpa 0UR9 tqD1 1AWz 1SY5 H3jG F792 tE6M P4IB sOV2'
				 ,'Cfg4 6xYo yDy9 oB2n pOO1 4QgO QSY1 Cw2u bCu1 q1rZ uU81 UP41 R0kY Ne4p Hu5a c6Sz'
				 ,'2Ile ue8M 0Ma4 ztK0 B7Ep 0Oey DEX5 6V6g hoW7 bxE9 aYT0 0bCl 4X0B JT8B Qq9g B437'
				 ,'76PB Z5uR SJ1A 5wbG aLK9 BC2U 6uAF dFg5 B6QF JoI5 AXh3 1WyX 089X xH6V aTT9 io5X'
				 ,'FJ6A YkG0 Ux79 Pnn9 1CAS 53Vg B7vf 8ZJp CP3Q 75Ws 8XgJ qSK9 jI8c IWW9 63Pi 8Hog'
				 ,'6D7c Tzl4 2E23 7Lgn Ukf8 1eNE 58XK Uw67 DtW1 GV2G 34Fu O9ah 9tiZ 0Sy3 1VS9 w2XG'
				 ];

		foreach($tests as $test) {
			$r      = rand(4,strlen($test));
			$needle = substr($test, $r*-1);
			$C = new PHPAnt\Core\Command($test);
			$this->assertTrue($C->endsWith($needle));
		}
	}

	function testLastToken() {
		$line = "test app\n";
		$tests = ['j46G W9lu 8Qcw 9KNA S7kz Q139 9Rpa 0UR9 tqD1 1AWz 1SY5 H3jG F792 tE6M P4IB sOV2'
				 ,'Cfg4 6xYo yDy9 oB2n pOO1 4QgO QSY1 Cw2u bCu1 q1rZ uU81 UP41 R0kY Ne4p Hu5a c6Sz'
				 ,'2Ile ue8M 0Ma4 ztK0 B7Ep 0Oey DEX5 6V6g hoW7 bxE9 aYT0 0bCl 4X0B JT8B Qq9g B437'
				 ,'76PB Z5uR SJ1A 5wbG aLK9 BC2U 6uAF dFg5 B6QF JoI5 AXh3 1WyX 089X xH6V aTT9 io5X'
				 ,'FJ6A YkG0 Ux79 Pnn9 1CAS 53Vg B7vf 8ZJp CP3Q 75Ws 8XgJ qSK9 jI8c IWW9 63Pi 8Hog'
				 ,'6D7c Tzl4 2E23 7Lgn Ukf8 1eNE 58XK Uw67 DtW1 GV2G 34Fu O9ah 9tiZ 0Sy3 1VS9 w2XG'
				 ];
		foreach($tests as $test) {
			$C = new PHPAnt\Core\Command($test);
			$needle = substr($test,-4);
			$this->assertSame($needle,$C->getLastToken());
		}		
	}

	function testGetToken() {
		$tests = ['j46G W9lu 8Qcw 9KNA S7kz Q139 9Rpa 0UR9 tqD1 1AWz 1SY5 H3jG F792 tE6M P4IB sOV2'
				 ,'Cfg4 6xYo yDy9 oB2n pOO1 4QgO QSY1 Cw2u bCu1 q1rZ uU81 UP41 R0kY Ne4p Hu5a c6Sz'
				 ,'2Ile ue8M 0Ma4 ztK0 B7Ep 0Oey DEX5 6V6g hoW7 bxE9 aYT0 0bCl 4X0B JT8B Qq9g B437'
				 ,'76PB Z5uR SJ1A 5wbG aLK9 BC2U 6uAF dFg5 B6QF JoI5 AXh3 1WyX 089X xH6V aTT9 io5X'
				 ,'FJ6A YkG0 Ux79 Pnn9 1CAS 53Vg B7vf 8ZJp CP3Q 75Ws 8XgJ qSK9 jI8c IWW9 63Pi 8Hog'
				 ,'6D7c Tzl4 2E23 7Lgn Ukf8 1eNE 58XK Uw67 DtW1 GV2G 34Fu O9ah 9tiZ 0Sy3 1VS9 w2XG'
				 ];
		foreach($tests as $test) {
			$test = strtolower($test);
			$buffer = explode(' ', $test);
			$r = rand(0,count($buffer)-1);
			$C = new PHPAnt\Core\Command($test);
			$this->assertSame($C->getToken($r),$buffer[$r]);
		}		
	}

	function testCommandIs() {
		$line = "App Test 123\n";
		$C = new PHPAnt\Core\Command($line);
		$this->assertTrue($C->is('App Test 123'));
	}

	function testLeftStrip() {
		$tests = ['j46G W9lu 8Qcw 9KNA S7kz Q139 9Rpa 0UR9 tqD1 1AWz 1SY5 H3jG F792 tE6M P4IB sOV2'
		         ,'Cfg4 6xYo yDy9 oB2n pOO1 4QgO QSY1 Cw2u bCu1 q1rZ uU81 UP41 R0kY Ne4p Hu5a c6Sz'
		         ,'2Ile ue8M 0Ma4 ztK0 B7Ep 0Oey DEX5 6V6g hoW7 bxE9 aYT0 0bCl 4X0B JT8B Qq9g B437'
		         ,'76PB Z5uR SJ1A 5wbG aLK9 BC2U 6uAF dFg5 B6QF JoI5 AXh3 1WyX 089X xH6V aTT9 io5X'
		         ,'FJ6A YkG0 Ux79 Pnn9 1CAS 53Vg B7vf 8ZJp CP3Q 75Ws 8XgJ qSK9 jI8c IWW9 63Pi 8Hog'
		         ,'6D7c Tzl4 2E23 7Lgn Ukf8 1eNE 58XK Uw67 DtW1 GV2G 34Fu O9ah 9tiZ 0Sy3 1VS9 w2XG'
		         ];
		foreach($tests as $test) {
			$test = strtolower($test);
			$tokens = explode(' ', $test);
			$r = rand(0,count($tokens)-1);
			$buffer = [];
			for($x=0;$x<$r;$x++) {
				array_push($buffer, $tokens[$x]);
			}

			$stripstring = implode(' ', $buffer);

			$resultArray = array_diff($tokens, $buffer);

			$resultString = implode(' ', $resultArray);
			
			//Get a random number of beginning pieces.
			$C = new PHPAnt\Core\Command($test);
			$this->assertSame($resultString,$C->leftStrip($stripstring));
		}		
	}

	function testSplitOn() {
		$line     = "j46G W9lu 8Qcw 9KNA S7kz Q139 9Rpa 0UR9 tqD1 1AWz 1SY5 H3jG F792 tE6M P4IB sOV2";
		$line     = strtolower($line);

		$expected = "1AWz 1SY5 H3jG F792 tE6M P4IB sOV2";
		$expected = strtolower($expected);

		$piece    = "tqD1";
		$piece    = strtolower($piece);
		
		$C = new PHPAnt\Core\Command($line);
		$this->assertSame($expected,$C->splitOn($piece));
	}
}
