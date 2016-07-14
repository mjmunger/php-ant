<?php

namespace PHPAnt\Core;

class CLIAuth extends AntAuth
{
    function __construct(\PDO $pdo, \Logger $logger) {
        parent::__construct($pdo,$logger);
        $this->authorized = true;
        $this->logged_in  = true;
        $this->authType   = AntAuth::CLI;
        $this->db 		  = $pdo;
    }

}