<?php

/**
 * An instance of the user class, which extends the parent giving additional functionality.
 **/

 /**
 * Contains Password hashing with PBKDF2 code from  havoc AT defuse.ca https://defuse.ca/php-pbkdf2.htm
 *
 * @package      BFW Toolkit 
 * @subpackage   Core
 * @category     Core Classes
 * @author       Michael Munger <michael@highpoweredhelp.com>
 * @author           Havoc <havoc@defuse.ca>
 **/ 
         
class User extends users implements BFWUser
{
    const PBKDF2_HASH_ALGORITHM = "sha256";
    const PBKDF2_ITERATIONS = 1000;
    const PBKDF2_SALT_BYTE_SIZE = 24;
    const PBKDF2_HASH_BYTE_SIZE = 24;

    const HASH_SECTIONS = 4;
    const HASH_ALGORITHM_INDEX =  0;
    const HASH_ITERATION_INDEX = 1;
    const HASH_SALT_INDEX = 2;
    const HASH_PBKDF2_INDEX = 3;

    /**
    * @var boolean $users_new If the user is a new user, this is set to true; otherwise, it is false.
    **/
    var $users_new;

    /**
    * @var object $logger Optional. A logger class from a "parent" or "calling" class. Used to ensure that all the messages for a given action are logged in the same file to make the logs readable.
    **/
    
    var $logger = '';
    
    /**
    * @var string $__oldlabel The previous label for the logger
    **/ 

    var $__oldlabel;    
    
    /**
    * Instantiates an object of the user class.
    * @param object $logger Optional. The logger class to be used with this.
    * @return
    **/
    
    function __construct(PDO $pdo, $logger=null)
    {

        parent::__construct($pdo,$logger);
        //Initialize logging class. 
        if($logger) {
            $this->logger = $logger;
            $this->__oldlabel = $logger->label;
            $this->logger->label = 'user';
        } else {
            $this->logger = new Logger('user');
        }

    }
    
    /**
    * Authenticates a user based on a given password.
    * 
    * Requires you set $this->users_email first. This function loads the user
    * based on the email address associated, and then creates a password hash from
    * the given password to compare for a match.   *
    * @param string $password The password we're checking to see if it's right.
    * @return boolean. If they match, it returns true. Otherwise, false.
    **/ 
    
    function authenticate($password)
    {
        if($this->loadFromEmail()) {
            if($this->validatePassword($password,$this->users_password)) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
    * Creates or loads an email account based on the presence of the email address / user association.
    * 
    * Creates a new user if an account with the email address does not yet exist. 
    * @return void
    **/
    
    function createIfNew() {
        //If this email already has an account, load it. Otherwise, create a new one.
        $sql = "SELECT * FROM `users` WHERE `users_email`= ?";
        $stmt = $this->pdo->prepare($sql);

        if(!$stmt->execute()) {
            var_dump($stmt->errorInfo());
            var_dump($stmt);
            var_dump($sql);
        }

        if($stmt->rowCount() > 0) {
            //user exists. Load the user elsewhere.
        } else {
            //user does not exist. Create it.
            $this->insert_me();
            $this->users_new = true;
        }
    }
    
    /** 
    * Loads a user based on email address
    * 
    * Requires you first set $this->users_email. Then it will load a user based on that unique email address.
    * @return boolean True if the user exists, false if the user does not.
    **/
    
    function loadFromEmail()
    {
        $sql = sprintf("SELECT * FROM `users` WHERE `users_email`= ?");
        $stmt = $this->pdo->prepare($sql);
        $values = [$this->users_email];

        if($this->logger) $this->logger->log("Running query: $sql");
        
        if(!$stmt->execute($values)) {
            $errors = $stmt->errorInfo();

            var_dump($errors);
            var_dump($squery);
            var_dump($stmt);
            $this->logger->log(sprintf("Database Error(%s) %s",$errors[1],$errors[2]));
        } else {
            $this->logger->log(sprintf("Query returned %s rows",$stmt->rowCount()));
        }

        if($stmt->rowCount() > 0) {
            //user exists. Load the user
            $row = $stmt->fetchObject();
            $this->logger->log(print_r($row,true));
            $this->users_id = $row->users_id;
            $this->load_me();
            $this->logger->log(print_r($this,true));
            return true;
        } else {
            //user does not exist. Create it.
            return false;
        }       
    }
    
    /**
    * Loads a user based on the nonce associated with an activation.
    * @return boolean True if the nonce exists and a user is found, otherwise, false.
    **/
    function loadFromActivation()
    {
        $sql = "SELECT * FROM `users` WHERE `users_nonce`= ? ";
        $stmt = $this->pdo->prepare($sql);
        $values = [$this->users_nonce];

        if(!$stmt->execute($values)) {
            var_dump($stmt->errorInfo());
            var_dump($squery);
            var_dump($stmt);
        }
        
        if($stmt->rowCount() > 0) {
            //user exists. Load the user
            $row = $stmt->fetchObject();
            $this->users_id = $row->users_id;
            $this->load_me();
            return true;
        } else {
            //user does not exist. Create it.
            return false;
        }       
    }   

    /**
    * Creates a salted hash from a given password.
    * @param string $password The password we are generating the hash from.
    * @return void
    **/
    
    function createHash($password) {
            // format: algorithm:iterations:salt:hash
            $salt = base64_encode(mcrypt_create_iv(self::PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
            $this->users_password =  self::PBKDF2_HASH_ALGORITHM . ":" . self::PBKDF2_ITERATIONS . ":" .  $salt . ":" . 
        base64_encode($this->pbkdf2(
                self::PBKDF2_HASH_ALGORITHM,
                $password,
                $salt,
                self::PBKDF2_ITERATIONS,
                self::PBKDF2_HASH_BYTE_SIZE,
                true
        ));
    }

    /**
    * Validates a given password against the hash in the database
    *
    * @param string $password The password we are submitting for verification.
    * @param string $correct_hash The hash we should get if the password is correct.
    **/ 
    
    function validatePassword($password, $correct_hash) {
        $params = explode(":", $correct_hash);
        if(count($params) < self::HASH_SECTIONS) {
             return false; 
        }

        $pbkdf2 = base64_decode($params[self::HASH_PBKDF2_INDEX]);
        
        return $this->slowEquals(
            $pbkdf2,
            $this->pbkdf2(
                $params[self::HASH_ALGORITHM_INDEX],
                $password,
                $params[self::HASH_SALT_INDEX],
                (int)$params[self::HASH_ITERATION_INDEX],
                strlen($pbkdf2),
                true
                )
            );
    }

    /**
    * Compares two strings $a and $b in length-constant time.
    * @param string @a First argument to compare.
    * @param string @b Second argument to compare.
    * @return boolean 
    **/
    
    function slowEquals($a, $b) {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0; 
    }

    /**
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt. This implementation of PBKDF2 was originally created by https://defuse.ca. With improvements by http://www.variations-of-shadow.com
     * 
     * @param const $algorithm The hash algorithm to use. Recommended: SHA256
     * @param string $password The password.
     * @param string $salt A salt that is unique to the password.
     * @param integer $count Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * @param double $key_length The length of the derived key in bytes.
     * @param boolean $raw_output If true, the key is returned in raw binary format. Hex encoded otherwise.
     *
     * @return string A $key_length-byte key derived from the password and salt.
     **/

    function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
        $algorithm = strtolower($algorithm);
    
        if(!in_array($algorithm, hash_algos(), true)) {
            die('PBKDF2 ERROR: Invalid hash algorithm.');
        }
    
        if($count <= 0 || $key_length <= 0) {
            die('PBKDF2 ERROR: Invalid parameters.');
        }

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);

        $output = "";
        for($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if($raw_output) {
            return substr($output, 0, $key_length);
        } else {
            return bin2hex(substr($output, 0, $key_length));
        }
    }

    /**
    * Loads a user based on the token in $_COOKIES
    *
    * @return boolean True if the user can be loaded, False otherwise.
    **/
    
    function loadFromToken()
    {
        $sql = "SELECT `users_id` FROM `users` WHERE `users_token` = ? || `users_mobile_token` = ? LIMIT 1";
        $stmt=$this->pdo->prepare($sql);
        $values = [$this->users_token,$this->users_token];

        if(!$stmt->execute($values)) {
            $error = $stmt->errorInfo();
            $this->logger->log("Database error (%s) %s",$error[1],$error[2]);
            var_dump($stmt->errorInfo());
            var_dump($sql);
            var_dump($stmt);
        } else {
            $this->logger->log(sprintf("Query returned %s rows.",$stmt->rowCount()));
            $this->logger->log(print_r($stmt,true));
            $this->logger->log(print_r($values,true));
        }

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetchObject();
            $this->users_id = $row->users_id;
            $this->load_me();
            return true;
        } else {
            return false;
        }
    }
    
    /**
    * Concatenates the first and last name of a user into a single string.
    *
    * @return string
    **/
    
    function getFullName() {
        return $this->users_first . ' ' . $this->users_last;
    }
    
    /**
    * Returns a human readable roll.
    *
    * @return string
    **/
    
    function load_me() {
        parent::load_me();

        $myRole = new UserRole($this->pdo);
        $myRole->users_roles_id = $this->users_roles_id;
        $myRole->load_me();
        $this->role = $myRole;
    }

    /**
     * Returns the human readable role for a user.
     * Example:
     *
     * <code>
     * echo $u->getRole();      
     * </code>
     *
     * @return string
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function getRole() {
        return $this->role->users_roles_title;
    }

    /**
    * Emails a user
    *
    * This method reads an email from the /emails/ directory off the document
    * root and does find and replace actions to fill in relevant information
    * for each field.
    *
    * Additionally, it uses PHPMailer, but PHPMailer should be configured to
    * use Postfix to send mail out, and NOT use SMTP() directly.
    *
    * Example: Sending an email to a user who is logged in ($current_user),
    * where we are filling in some data in the email: two variables: [FOO] and
    * [BAR]:
    *
    * <code>
    * $data = array(
    *    '[FOO]' => $someVariable,
    *    '[BAR]' => $someOtherVariable
    *    );
    *
    *    $subject = "Test email subject";
    *
    *    $fromAddress = "no-reply@yourdomain.com";
    *    $fromName    = "Mr. NoReply"
    *
    *    $cc = array(
    *        'client1@yourdomain.com',
    *        'client2@yourdomain.com'
    *        );
    *
    *    $bcc = array(
    *        'testing@ourdomain.com',
    *        'qualityassurance@ourdomain.com'
    *        );
    *
    *    $result = $current_user->email(
    *        $subject,       // The subject of the email as defined above.
    *        $message,       // The HTML message, which was loaded from $_SERVER['DOCUMENT_ROOT']./emails/[$message].html 
    *        $data,          // The find and replace fields that will be substituted in the body of the message. 
    *        $fromAddress,   // Setting the from Address. 
    *        $fromName,      // Setting the from name. 
    *        '',             // I don't want to set the replyTo address. 
    *        '',             // I don't want to set the replyTo name either, but I have to pass it something so I can pass $cc and $bcc 
    *        $cc,            // Array of people I want to be visibly copied on the email. 
    *        $bcc,           // Array of people I want to be blind carbon copied on the email. 
    *        );
    *    
    *    if(!$result) {
    *        echo "I couldn't email!";
    *    } else {
    *        echo "Email sent successfully.";
    *    }
    * 
    * </code>
    * 
    * This 
    *
    * @param object $notif A Notif class that has been properly instantiated and had all its properties set for the current message to be mailed out.
    * 
    * @return mixed false if there were no errors. Otherwise, an array of errors.
    **/

    function email(Notif $notif) {
        //Replace standard fields notif:
        $notif->addFindReplace('HEADERDATE',date('M-d-Y'));
        $notif->addFindReplace('FIRSTNAME',$this->users_first);
        $notif->addFindReplace('LASTNAME',$this->users_last);
        $notif->addFindReplace('NONCE',$this->users_nonce);
        $notif->addFindReplace('FULLNAME',$this->getFullName());
        $notif->addFindReplace('USERNAME',$this->users_first);
        $notif->to = $this->users_email;

        $errors = $notif->prepareSend();
        if($errors) {
            return $errors;
        }

        $mail = new PHPMailer();
        $mail->setFrom($notif->fromAddress, $notif->fromName);
        if($notif->replyToName && $notif->replyToAddress) {
            $mail->addReplyTo($notif->replyToAddress,$notif->replyToName);
        }
        $mail->addAddress($this->users_email,$this->getFullName());
        $mail->Subject = $notif->subject;
        $mail->msgHTML($notif->body);

        if(!$mail->send()) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
            return $error;
        } else {
            return false;
        }       
    }

    
    /**
    * Destroys this object, but first... sets the logger label back to the calling class' label (if this was set).
    **/
    
    function __destruct() {
        //pass
    }

    /**
     * Dumps this class' data to the CLI in a nice format.
     * Example:
     *
     * <code>
     * $u->CLIPrintMe()
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function CLIPrintMe() {
        echo str_pad("User ID:", 25);
        echo $this->users_id;
        echo PHP_EOL;
        echo str_pad("User Email:", 25);
        echo $this->users_email;
        echo PHP_EOL;
        echo str_pad("First name:", 25);
        echo $this->users_first;
        echo PHP_EOL;
        echo str_pad("Last name:", 25);
        echo $this->users_last;
        echo PHP_EOL;
        echo str_pad("User is setup:", 25);
        echo $this->users_setup;
        echo PHP_EOL;
        echo str_pad("Current nonce:", 25);
        echo $this->users_nonce;
        echo PHP_EOL;
        echo str_pad("Current token:", 25);
        echo $this->users_token;
        echo PHP_EOL;
        echo str_pad("User is active:", 25);
        echo $this->users_active;
        echo PHP_EOL;
        echo str_pad("Last login:", 25);
        echo $this->users_last_login;
        echo PHP_EOL;
        echo str_pad("Current mobile token:", 25);
        echo $this->users_mobile_token;
        echo PHP_EOL;
        echo str_pad("Public Key:", 25);
        echo $this->users_public_key;
        echo PHP_EOL;
        echo str_pad("Owner ID:", 25);
        echo $this->users_owner_id;
        echo PHP_EOL;
        echo str_pad("Timezone:", 25);
        echo $this->users_timezone;
        echo PHP_EOL;
    }

    /**
     * Prints a summary line for this user to the CLI (STDOUT)
     * Example:
     *
     * <code>
     * $u->CLIPrintSummaryLine()
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function CLIPrintSummaryLine() {
        echo str_pad($this->users_id, 5);
        echo str_pad($this->users_first, 25);
        echo str_pad($this->users_last, 25);
        echo str_pad($this->users_email, 25);
        echo PHP_EOL;
    }

    /**
     * Gets the last database error this class encountered, and formats it for display in the CLI or on a page..
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return string The well formatted database error message.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function getDBError() {
        /*if($this->db->errno) {
            return sprintf("Database error (%s) %s\n",$this->db->errno,$this->db->error);
        } else {
            return false;
        }*/
    }
}