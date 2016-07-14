<?php

namespace PHPAnt\Core;

class AuthEnvFactory
{
    static function getAuthenticator(\PDO $pdo, \Logger $logger) {

        switch (php_sapi_name()) {
            case 'cli':
                $auth = new CLIAuth($pdo, $logger);
                break;
            
            default:
                $auth = (AuthEnvFactory::isMobile()? new MobileAuth($pdo, $logger):new WebAuth($pdo, $logger));
                break;
        }
        return $auth;
    }

    static function isMobile() {
        /** DETERMINE MOBILE STATUS **/
        $isMobile = false;
        $mobileAgents = array('iPhone','Android');
        for($x=0;$x<sizeof($mobileAgents);$x++) {
            if(stripos($_SERVER['HTTP_USER_AGENT'],$mobileAgents[$x])>0)
                $isMobile = true;
        }

        return $isMobile;
    }

}