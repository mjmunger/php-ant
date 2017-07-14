<?php

namespace PHPAnt\Core;

use \Exception;

/**
 * Parses a SQL file and stores the SQL commands in an array for sequential execution.
 */

 /**
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     Utilities
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */

class SQLParser
{
    public $sourceFile = NULL;
    public $commands   = [];

    public function __construct($sourceFile) {

        if(file_exists($sourceFile) == false ) throw new Exception(sprintf("Exception: (%s:%s) Cannot load source file: %s",__FILE__,__LINE__, $sourceFile), 1);
        
        $this->sourceFile = $sourceFile;
        $this->commands = explode(";",file_get_contents($this->sourceFile));
        $this->parseSource();
    }

    public function removeComments() {
        $buffer = [];
        
        foreach($this->commands as $line) {
            $buffer[] = preg_replace('/#.*/', '', $line);
        }

        $this->commands = $buffer;
    }

    public function removeBlankLines() {
        $buffer = [];
        foreach($this->commands as $line) {
            if(is_null($line) === false) {
                if(strlen($line) > 0) $buffer[] = $line;
            }
        }

        $this->commands = $buffer;
    }

    public function minify() {
        $buffer = [];
        foreach($this->commands as $line) {
            $buffer[] = preg_replace('(\r\n|\r|\n)', ' ', $line);
        }
        $this->commands = $buffer;
    }

    public function parseSource() {
        $this->removeComments();
        $this->removeBlankLines();
        $this->minify();
    }

}