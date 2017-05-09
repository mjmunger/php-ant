<?php

namespace PHPAnt\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

$dependencies = [ 'tests/test_top.php'
                , 'includes/classes/Command.class.php'
                , 'includes/classes/CommandInvoker.class.php'
                , 'includes/classes/CommandList.class.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

class CommandListTest extends TestCase
{

    public function testRenderGrammar() {

        $CommandList = new CommandList();

        //Add new users.
        $callback = 'userNew';
        $criteria = ['is' => ['users add' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $CommandList->add($Invoker);

        $expectedGrammar = ['users' => ['add' => NULL]];

        $this->assertSame($expectedGrammar, $CommandList->getGrammar());

        //Query / show user.
        $callback = 'userShow';
        $criteria = ['startsWith' => ['users show' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $CommandList->add($Invoker);
        
        $expectedGrammar = ['users' => [ 'add'  => NULL
                                       , 'show' => NULL
                                       ]
                           ];

        $this->assertSame($expectedGrammar, $CommandList->getGrammar());

        //Update user password
        $callback = 'userPasswordReset';
        $criteria = ['startsWith' => ['users password reset' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $CommandList->add($Invoker);

        $expectedGrammar = ['users' => [ 'add'  => NULL
                                       , 'show' => NULL
                                       , 'password' => ['reset' => NULL]
                                       ]
                           ];

        $this->assertSame($expectedGrammar, $CommandList->getGrammar());

        //Delete user.
        $callback = 'userDelete';
        $criteria = ['startsWith' => ['users delete' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $CommandList->add($Invoker);

        $expectedGrammar =  [ 'users' => [ 'add'      => NULL
                                         , 'show'     => NULL
                                         , 'password' => ['reset' => NULL]
                                         , 'delete'   => NULL
                                         ]
                            ];

        $this->assertSame($expectedGrammar, $CommandList->getGrammar());

    }
}