<?php

namespace PHPAnt\Core;

/**
 * Tests a command object to decide if it should invoke a callback.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/
 
class CommandInvoker
{
    public $criteria  = [];
    public $callback  = NULL;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    //Should eventually be made private, but left public for backwards compatibility.
    public function addCriteria($criteria) {
        array_push($this->criteria,$criteria);
    }

    public function is($commandString) {
        $criteria = ['is' => [$commandString => true]];
        $this->addCriteria($criteria);
    }

    public function startsWith($commandString) {
        $criteria = ['startsWith' => [$commandString => true]];
        $this->addCriteria($criteria);
    }

    public function shouldRunOn($Command) {

        foreach($this->criteria as $criteria) {
            $callback      = key($criteria);
            $testArray     = $criteria[$callback];
            $pattern       = key($testArray);
            $desiredResult = $testArray[$pattern];

            $result = ($Command->$callback($pattern) == $desiredResult);

            if($result == false) return false;
        }

        return true;
    }

}