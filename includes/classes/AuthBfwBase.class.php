<?php 

namespace PHPAnt\Core;

class AntAuth
{
    const CLI         = 0;
    const WEB         = 1;
    const MOBILE      = 2;

    var $authorized   = false;
    var $logged_in    = false;
    var $logger       = null;
    var $authType     = null;
    var $current_user = null;
    var $pdo          = null;

    function __construct(\PDO $pdo, \Logger $logger) {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    function checkCookies() {
        if(isset($_COOKIE['current_user'])) {
            $this->logger->log("Checking desktop cookie: ".print_r($_COOKIE['current_user'],true));
            $current_user = new Users($this->pdo,$this->logger);
            $current_user->users_token = $_COOKIE['current_user'];
            if($current_user->loadFromToken()) {
                $this->logger->log("User has been successfully loaded from token.");
                //Setup user logging.
                $ulogger = new \Logger('current_user',str_replace('@','.',$current_user->users_email).'.log');
                $current_user->logger = $ulogger;     
                $this->logged_in  = true;
                $this->authorized = true;
                $this->current_user = $current_user;
                // leave now while you have authorization!
                return true;
            } else {
                $this->logger->log("User could NOT be loaded from token.\n".print_r($current_user,true));
            }
        }

        $this->logged_in  = false;
        $this->authorized = false;
        return false;
    }

    function authorize(AppEngine $Engine) {
        //If we didn't try to authenticate, don't bother with the rest.
        if(!isset($_POST['user']) || !isset($_POST['password'])) {
            return false;
        }

        $u = new Users($this->pdo,$this->logger);
        $this->logger->log(sprintf("Authorizng user based on email address: %s",$_POST['user']));
        $u->users_email= $_POST['user'];
        if($u->loadFromEmail()) {
            $this->logger->log("User was successfully loaded from email.");
        } else {
            $this->logger->log("FAILURE: User could not be loaded from the given email address.");
        }


        /* Quit if they don't authenticate. */
        if(!$u->authenticate($_POST['password'])) {
            $Engine->Configs->divAlert("The password entered does not match our records. Please try again.",'warning');
            return false;
        }
        
        $u->users_token = hash_hmac('crc32',time(),'095efed2');
        $u->users_last_login = time();
        $u->update_me();

        if(count($u->errors)>0) {
            foreach($u->errors as $error) {
                $this->logger->log(print_r($error,true));
            }
        }

        if($u->users_active == 'N') {
            $Engine->Configs->divAlert("Your account is not active. Please contact your administrator.");
            die();
        }

        $this->logged_in  = true;
        $this->authorized = true;

        if(isset($_POST['remember'])) setcookie("current_user",$u->users_token,time()+60*60*24*30,'','',true);

        $Engine->log($u->getFullName(),$u->getFullName() . ' logged in successfully');
    }
}