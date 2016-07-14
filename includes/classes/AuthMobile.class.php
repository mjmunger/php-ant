<?php

namespace PHPAnt\Core;

class MobileAuth extends AntAuth
{
    function __construct(\PDO $pdo, \Logger $logger) {
        parent::__construct();
        $this->authorized = false;
        $this->logged_in  = false;
        $this->authType   = BFWAuth::MOBILE;
        $this->db 		  = $pdo;

        if(isset($_COOKIE['current_user'])) {

        }
    }
}