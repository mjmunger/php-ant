<?php

namespace PHPAnt\Core;

require_once('tests/test_top.php');
require('includes/classes/SQLParser.class.php');

use PHPUnit\Framework\TestCase;
use \Exception;

class SQLParserTest extends TestCase
{

    public function testRemoveComments() {
        $SQLParser = new SQLParser(__DIR__ . '/var/sql_script.sql');

        //newlines have been removed
        foreach($SQLParser->commands as $command) {
            $this->assertFalse(stristr($command,"#"));
        }
        var_dump($SQLParser->commands);
    }

    public function testRemoveBlankLines() {
        $SQLParser = new SQLParser(__DIR__ . '/var/sql_script.sql');
        //Blank lines have been removed.
        
        foreach($SQLParser->commands as $command) {
            $this->assertNotNull($command);
            $this->assertGreaterThan(0, strlen($command));
        }        
    }

    public function testMinify() {
        $SQLParser = new SQLParser(__DIR__ . '/var/sql_script.sql');

        //newlines have been removed
        foreach($SQLParser->commands as $command) {
            $this->assertFalse(stristr($command,"\n"));
            $this->assertFalse(stristr($command,"\r"));
        }
    }

    public function testParser() {
        $SQLParser = new SQLParser(__DIR__ . '/var/sql_script.sql');

        $this->assertCount(6, $SQLParser->commands);
    }

}