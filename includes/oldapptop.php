<?php 

//Is the cookie there?
    if(isset($_COOKIE['current_user']) || isset($_COOKIE['mobile_user'])) {
        $logger->log("User attempting to log in with a cookie. Cookie monster happy!");
        $current_user = new user($logger);
        if($isMobile) {
            if(isset($_COOKIE['mobile_user'])) {
                $logger->log("Checking mobile cookie: ".print_r($_COOKIE['mobile_user'],true));
                $current_user->users_token = $_COOKIE['mobile_user'];
            }
        } else {
            if(isset($_COOKIE['current_user'])) {
                $logger->log("Checking desktop cookie: ".print_r($_COOKIE['current_user'],true));
                $current_user->users_token = $_COOKIE['current_user'];
            }
        }
        if($current_user->loadFromToken()) {
            $PE->runActions('user_loaded_from_token');
            $logger->log("User has been successfully loaded from token.");
            //Setup user logging.
            $ulogger = new logger('current_user',str_replace('@','.',$current_user->users_email).'.log');
            $current_user->logger = $ulogger;     
            $logged_in = true;      
        } else {
            $PE->runActions('user_loaded_from_token_failed');
            $logger->log("User could NOT be loaded from token.\n".print_r($current_user,true));
            //var_dump($_COOKIE);
            //Your cookie expired, or you logged in from somewhere else. Logging you out.
            header("location: " . getHostFQDN()."login-oops.php");
        }

    } else {
        //Did we just try to log in?
        if(!isset($_COOKIE['current_user']) && !isset($_COOKIE['mobile_user'])) {
            $logger->log("There is no cookie to speak of. Attempting to authenticate the user.");
            if(isset($_POST['user']) && isset($_POST['password'])) {
                $logger->log("We've got a user and a password. Proceeding with authentication.");
                //looks like we're trying to log in. Let's try this out.
                $u = new user($logger);
                $u->users_email= $_POST['user'];
                $u->loadFromEmail();
                //If the user eixsts, and the password matches, #WIN!
                if($u->authenticate($_POST['password'])) {
                    $logger->log("User authentication succeeded. Loading user based on email.");
                    $u->loadFromEmail();
                    
                    //Create a new token.
                    if($isMobile) {
                        $logger->log("User is logged in from a mobile device. Creating mobile cookie with mobile token");
                        $u->users_mobile_token = hash_hmac('crc32',time(),'095efed2');                    
                    } else {
                        $logger->log("User is logged in from a desktop device. Creating regular cookie with token");
                        $u->users_token = hash_hmac('crc32',time(),'095efed2');     
                    }
                    
                    //Save the token.
                    $u->users_last_login = time();
                    $logger->log("Last login recorded as: " . $u->users_last_login);
                    $u->update_me();
                    if($u->db->errno) {
                        $logger->log(sprintf("There was an error while attempting to update the user table of the database. Error (%s) %s",$u->db->errno,$u->db->error));
                    } else {
                        $logger->log("User update was successful. No errors.");
                    }

                    if($u->users_active == 'Y') {
                        $logged_in = true;
                        $PE->runActions('user_logged_in');
                        if(isset($_POST['remember'])) {
                            //Remember me for 30 days.
                            if($isMobile) {
                                setcookie("mobile_user",$u->users_mobile_token,time()+60*60*24*30,'','',true);
                            } else {
                                setcookie("current_user",$u->users_token,time()+60*60*24*30,'','',true);
                            }
                        } else {
                            if($isMobile) {
                                setcookie("mobile_user",$u->users_mobile_token,0,'','',true);
                            } else {
                                setcookie("current_user",$u->users_token,0,'','',true);
                            }
                        }           
                    } else {
                        divAlert("This account has been disabled. If you feel this was in error, please contact <a href=\"mailto:michael@highpoweredhelp.com\">support.</a>",'error');
                    }
                } else {
                    echo '<div class="alert alert-error" align="center">Authentication Failed</div>';
                }   
            }
        }
    }
    //echo "<pre>"; print_r($_COOKIE['current_user']); echo "</pre>";
    //Are we logged in?
    if($logged_in) {
        //Setup user logging.
        
        //Load the user from the cookie.
        if(isset($_GET['return'])) {
            header("location: " . $_GET['return']);
        } else {
            //header("location: " . getHostFQDN());
            //Are you allowed to view this page?
            /*verifyPagePrivleges($PE,$current_user);        */
        }
    } else {
        //No. Show login page.
        $returnto = $_SERVER['REQUEST_URI'];

        $format = getHostFQDN()."login.php?return=%s";
        $url = sprintf($format,$returnto);

        $noauth = [ '/login.php'
                  , '/activate.php'
                  , '/reset.php'
                  , '/login-oops.php'
                  , '/alerts.php'
                  ];

        $mustauth = true;

        foreach($noauth as $page) {
            if($_SERVER['PHP_SELF'] == $page) {
                $mustauth = false;
            }
        }

        if($mustauth) {
            header(sprintf("location: %s",$url));
        }
    }