<?php

namespace PHPAnt\Setup;

class BaseSetupConfigs
{
    public $configs = null;
    public $baseDir = null;

    public function __construct($baseDir) {
    	$this->baseDir = $baseDir;
    }
}