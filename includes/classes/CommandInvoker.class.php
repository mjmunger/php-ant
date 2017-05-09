<?php

namespace PHPAnt\Core;

/**
 * Tests a command object to decide if it should invoke a callback.
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/
 
class CommandInvoker
{
    public $criteria  = [];
    public $Command   = NULL;
    public $callback  = NULL;

    public function __construct($Command, $callback) {
        $this->Command  = $Command;
        $this->callback = $callback;
    }

    public function addCriteria($criteria) {
        array_push($this->criteria,$criteria);
    }

    public function shouldRun() {

        foreach($this->criteria as $criteria) {
            $callback      = key($criteria);
            $testArray     = $criteria[$callback];
            $pattern       = key($testArray);
            $desiredResult = $testArray[$pattern];

            $result = ($this->Command->$callback($pattern) == $desiredResult);
            
            if($result == false) return false;
        }

        return true;
    }

}