<?php

class CLIAuth extends BFWAuth
{
    function __construct(PDO $pdo, Logger $logger) {
        parent::__construct($pdo,$logger);
        $this->authorized = true;
        $this->logged_in  = true;
        $this->authType   = BFWAuth::CLI;
        $this->db 		  = $pdo;
    }

}