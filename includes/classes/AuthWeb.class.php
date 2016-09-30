<?php

namespace PHPAnt\Core;

class WebAuth extends AntAuth
{
    public $isApi = false;

    function __construct(\PDO $pdo, \Logger $logger) {
        parent::__construct($pdo,$logger);
        $this->authorized = false;
        $this->logged_in  = false;
        $this->db         = $pdo;
        $this->authType   = AntAuth::WEB;
    }

    function redirect($Engine) {
        $this->isApi = (stripos($_SERVER['REQUEST_URI'], '/api/') !== false);

        //Authenticate and handle APIs differently. 
        if($this->isApi) {
            $validKey = $this->validateKey($_GET['key']);
            $shouldRedirect = false;
            $this->authorized = $validKey;
            return true;
        }
        
        /* Never redirect for any of these pages page */
        $noRedirect = ['/login.php'
                      ,'/reset.php'
                      ,'/activate.php'
                      ];

        /* Remove any variables because they break this check. We only want the page. */
        if(stripos($_SERVER['REQUEST_URI'],'?')) {
            $buffer = explode("\?", $_SERVER['REQUEST_URI']);
            $requestedPage = $buffer[0];
        } else {
            $requestedPage = $_SERVER['REQUEST_URI'];
        }

        //var_dump($_SERVER['REQUEST_URI']);
        //var_dump($isApi);
        //die(__FILE__  . ':' . __LINE__ );

        $shouldRedirect = (!in_array($requestedPage, $noRedirect) && !$this->isApi);

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

    function validAPIKey($key) {

        $sql  = "SELECT api_keys_id FROM timing.api_keys WHERE api_keys_key = ? and api_keys_enabled = ?";
        $stmt = $this->pdo->prepare($sql);
        $vars = [$key,'Y'];
        $stmt->execute($vars);

        return ($stmt->rowCount() > 0);
    }

    function validateKey($key) {
        if(!$this->validAPIKey($key)) {
            header('HTTP/1.0 403 Forbidden');
            die('Access Denied');
        } 

        return true;
    }
}