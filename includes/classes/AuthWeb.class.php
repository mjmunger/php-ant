<?php

namespace PHPAnt\Core;

class WebAuth extends AntAuth
{
    function __construct(\PDO $pdo, \Logger $logger) {
        parent::__construct($pdo,$logger);
        $this->authorized = false;
        $this->logged_in  = false;
        $this->db         = $pdo;
        $this->authType   = AntAuth::WEB;
    }

    function redirect($Engine) {
        /* Never redirect for any of these pages page */
        $noRedirect = ['/login.php'
                      ,'/reset.php'
                      ,'/activate.php'
                      ];

        /* Remove any variables because they break this check. We only want the page. */
        if(stripos($_SERVER['REQUEST_URI'],'?')) {
            $buffer = split("\?", $_SERVER['REQUEST_URI']);
            $requestedPage = $buffer[0];
        } else {
            $requestedPage = $_SERVER['REQUEST_URI'];
        }

        $shouldRedirect = !in_array($requestedPage, $noRedirect);

        switch ($this->authorized) {
            case true:
                if(!$shouldRedirect) {
                    $url = (isset($_GET['return'])?$_GET['return']:'/');
                } else {
                    return true;
                }
                break;
            case false:
                /* Short circuit if we are on a page that does not require authentication */
                if(!$shouldRedirect) return true;
                $url = $Engine->Configs->getHostFQDN()."login.php";
                break;
        }

        header("location: " . $url);
    }    


}