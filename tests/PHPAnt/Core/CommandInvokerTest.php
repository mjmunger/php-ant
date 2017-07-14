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

class CommandInvokerTest extends TestCase
{

    public function testCommandInvokerConstructor() {
        $criteria = ['startsWith' => ['ABC' => true]];
        $callback = 'someFunction';

        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);

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
        $Invoker =  new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->assertSame($expected, $Invoker->shouldRunOn($Command));
    }

    public function providerTestInvoker() {
                    //Line             //Criteria                                                 //Expected
                                       //Callback       //pattern            //desired result.
        return  [ [ 'apps test this' , ['is'         => ['apps test this' => true             ]], true  ]
                , [ 'apps test '     , ['is'         => ['apps test this' => true             ]], false ]
                , [ 'apps test this' , ['startsWith' => ['apps test'      => true             ]], true  ]
                , [ 'apps test this' , ['startsWith' => ['app test'       => true             ]], false ]
                ];
    }
}