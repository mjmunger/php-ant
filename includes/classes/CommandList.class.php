<?php

namespace PHPAnt\Core;

use \Exception;
use \Iterator;
use \Countable;
class CommandList implements Iterator, Countable
{
    private $position     = 0;
    private $invokerArray = [];
    private $grammar      = [];

    public function __construct() {
        $this->rewind();
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->invokerArray[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function previous() {
        $this->position--;
    }

    public function valid() {
        return isset($this->invokerArray[$this->position]);
    }

    public function add(CommandInvoker $Invoker) {
        array_push($this->invokerArray, $Invoker);
        $this->parseGrammar($Invoker);
    }

    public function count() {
        return count($this->invokerArray);
    }

    /**
     * Pass an array of words that represent a path in the array for which we are looking for children.
     **/

    public function getGrammar() {
        return $this->grammar;
    }

    //parse an array into pairs and make them nested arrays for commands.
    private function onionize($buffer) {
        //pop off the last two to make the seed array.
        $value = array_pop($buffer);
        $key = array_pop($buffer);

        $seed = [$key => $value];

        //Wrap the rest outward.

        for($x = count($buffer) -1; $x >= 0; $x--) {
            $key = array_pop($buffer);
            $tmp = [$key => $seed];
            $seed = $tmp;
        }

        return $seed;
    }

    public function parseGrammar($Invoker) {
        $commandArrays = [];

        //Get all the criteria
        foreach($Invoker->criteria as $criteria) {
            $callback      = key($criteria);
            $testArray     = $criteria[$callback];
            $pattern       = key($testArray);
            $desiredResult = $testArray[$pattern];

            //We are only interested in the pattern.
            $buffer = explode(" ", $pattern);

            //terminate it with a NULL
            array_push($buffer, NULL);
            array_push($commandArrays, $buffer);

            $tempGrammar = $this->onionize($buffer);
            $this->grammar = array_merge_recursive($this->grammar,$tempGrammar);
        }
    }
}