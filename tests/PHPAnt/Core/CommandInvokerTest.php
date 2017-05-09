<?php

namespace PHPAnt\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

$dependencies = [ 'tests/test_top.php'
                , 'includes/classes/Command.class.php'
                , 'includes/classes/CommandInvoker.class.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

class CommandInvokerDBTest extends TestCase
{
    use TestCaseTrait;
    private $conn       = NULL;
    static private $pdo = NULL;

    public function getConnection() {

        //Get the schema so we can create it in memory to prepare for testing.

        if($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = gimmiePDO();
            }
        }

        $this->conn =  $this->createDefaultDBConnection(self::$pdo,':memory:');
        return $this->conn;

    }

    public function getDataSet() {
        return $this->createMySQLXMLDataSet( __DIR__ .'/authtest.xml');
    }

    public function testCommandInvokerConstructor() {
        $Command = new Command($line);
        $criteria = ['startsWith' => ['ABC' => true]];
        $callback = 'someFunction';

        $Invoker = new CommandInvoker($Command, $callback);
        $Invoker->addCriteria($criteria);

        $this->assertSame($Command  , $Invoker->Command     );
        $this->assertSame($callback , $callback             );
        $this->assertCount(1        , $Invoker->criteria    );
        $this->assertSame($criteria , $Invoker->criteria[0] );
    }

    /**
     * @dataProvider providerTestInvoker
     * */

    public function testInvoker($line,$criteria, $expected) {
        $Command = new Command($line);
        $callback = 'someFunction';
        $Invoker =  new CommandInvoker($Command, $callback);
        $Invoker->addCriteria($criteria);
        $this->assertSame($expected, $Invoker->shouldRun());
    }

    public function providerTestInvoker() {
                    //Line             //Criteria                           //Expected        
        return  [ [ 'apps test this' , ['is'         => ['apps test this' => true  ]], true  ]
                , [ 'apps test '     , ['is'         => ['apps test this' => true  ]], false ]
                , [ 'apps test this' , ['startsWith' => ['apps test'      => true  ]], true  ]
                , [ 'apps test this' , ['startsWith' => ['app test'       => true  ]], false ]
                ];
    }
}