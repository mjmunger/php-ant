<?php

/* boundary(custom-namespace)=--fc1ead027aa75f4c0c9e49aa8d532dd4796dee41 */
/* --fc1ead027aa75f4c0c9e49aa8d532dd4796dee41 */


namespace PHPAnt\Core;


/* --fc1ead027aa75f4c0c9e49aa8d532dd4796dee41 */
/**
 * PHPAnt abstraction of table: `users`
 *
 * This class represents the database table users.
 * It contains methods and properties that are auto generated, and
 * which can be automatically updated using PHPAnt's database
 * abstraction tool. 
 *
 * MAKE SURE YOU EDIT THIS CLASS ONLY WITHIN THE BOUNDARIES!
 * 
 * A boundary looks like this:
 */
   /* boundary(custom-methods)=--9fac6baf3c7d28fded0287ec00a9c9274a930a11 */
   /* --9fac6baf3c7d28fded0287ec00a9c9274a930a11 */
   
   
        /* - YOUR CUSTOM FUNCTIONS GO HERE -

        function sayHello() {
            echo "hello World" . PHP_EOL;
        }

        */
   
   
   /* --9fac6baf3c7d28fded0287ec00a9c9274a930a11 */
 /*
 * Boundaries are marked by a boundary(custom-[method|properties]) marker.
 * They are then followed by a 42 character string that begins with --.
 *
 * As long as your edits are WITHIN a boundary, they will be retained when 
 * the class is auto-updated at a later time.
 *
 * And remember: ALWAYS backup your files!
 *
 * @package      Some Package
 * @subpackage   Some Subpackage
 * @category     Some Category
 * @author       Your Name <youremail@example.com>
 * @license      MIT
 *
 * Generated with PHPAnt. https://php-ant.org/
 * PHPAnt is copyright (c) 2015-2016 High Powered Help, Inc. All rights reserved.
 */ 
 
class Users
{

	/*<table columns>*/
	var $users_id;
	var $users_email;
	var $users_password;
	var $users_first;
	var $users_last;
	var $users_setup;
	var $users_nonce;
	var $users_token;
	var $users_active;
	var $users_last_login;
	var $users_mobile_token;
	var $users_public_key;
	var $users_owner_id;
	var $users_timezone;
	var $users_roles_id;
	/*</table columns>*/
	var $pdo;
	var $errors = [];
	var $fields = [];
	var $autoUpdateFields = [];
	var $insertDefaultFields = [];
	var $table = "users";
	var $primaryKey = false;
	var $logger = '';
	var $__oldlabel = '';


     /* boundary(custom-properties)=--81b47b8fe380c7a8d844501144e566f0e28b0ea8 */
     /* --81b47b8fe380c7a8d844501144e566f0e28b0ea8 */

    /**
    * @var boolean $users_new If the user is a new user, this is set to true; otherwise, it is false.
    **/
    var $users_new;

    /* Beneath this line, should be re-factored out. */

    const PBKDF2_HASH_ALGORITHM = "sha256";
    const PBKDF2_ITERATIONS = 1000;
    const PBKDF2_SALT_BYTE_SIZE = 24;
    const PBKDF2_HASH_BYTE_SIZE = 24;

    const HASH_SECTIONS = 4;
    const HASH_ALGORITHM_INDEX =  0;
    const HASH_ITERATION_INDEX = 1;
    const HASH_SALT_INDEX = 2;
    const HASH_PBKDF2_INDEX = 3;    


     /* --81b47b8fe380c7a8d844501144e566f0e28b0ea8 */

    function __construct(\PDO $pdo, $logger='') {
        $this->pdo = $pdo;

        //Get the field list, and find the primary key field.
        $sql = "DESCRIBE users;";
        $stmt = $this->pdo->prepare($sql);

        if(!$stmt->execute()) var_dump($stmt->errorInfo());

        if($stmt->rowCount() == 0) {
         //this should never happen.
         $error = sprintf("The table %s has zero columns. Nothing to do",$table);
         throw new Exception($error, 1);
         return false;
        }

        $props = array();

        while ($row = $stmt->fetchObject()) {
         array_push($props,$row->Field);
         //Grab ONLY the first primary key. We assume that the primary key (id) is always the first column that ia primary key.
         if(!$this->primaryKey && $row->Key = "PRI") $this->primaryKey = $row->Field;

         //Check to see if this field should be included in an insert
         //statement by looking at the default. If it contains any of the
         //default keywords, it is an automatic field, and should not be
         //included in insert statements.

         $defaultKeywords = ['CURRENT_TIMESTAMP'];
         if(in_array($row->Default, $defaultKeywords)) array_push($this->insertDefaultFields, $row->Field);

         //Check to see if this field should be included in update statements
         //by checking to see if keywords appear in Extra. If those keywords
         //are present, then it should be added to the
         //$this->autoUpdateFields, which means we will NOT set a value on
         //update. We'll let MySQL do that for us.


         $updateKeywords = ['on update CURRENT_TIMESTAMP'];
         if(in_array($row->Extra, $updateKeywords)) array_push($this->autoUpdateFields,$row->Field);

        }

        $this->fields = $props;

        if($logger) {
            $this->logger = $logger;
            $this->__oldlabel = $logger->label;
            $this->logger->label = 'company';
        } else {
            //TBA
        }
        
    }

    function __destruct() {
        if($this->logger)
        $this->logger->label = $this->__oldlabel;
    }

    function load_me() {
        $sql        = "SELECT * FROM `users` WHERE `users_id` = ? LIMIT 1";
        $stmt       = $this->pdo->prepare($sql);
        $values     = [$this->users_id];

        if(!$stmt->execute($values)) {
            $info = $stmt->errorInfo();
            throw new Exception(sprintf("Database error generating the parent class for $table. Database error (%s) %s",$info[1],$info[2]), 1);
        } 

        $row = $stmt->fetchObject();

        foreach($this->fields as $column) {
            $this->$column = $row->$column;
        }
    }

    function commit_suicide() {

        $table = $this->table;
        $primaryKey = $this->primaryKey;
        $errors = [];
        
        $sql = sprintf("DELETE FROM `%s` WHERE `%s`= ?",$table,$primaryKey);
        $stmt = $this->pdo->prepare($sql);
        $values = [$this->$primaryKey];
        $result = $stmt->execute($values);

        if(!$result) {
            array_push($errors, $stmt->errorInfo);
            array_push($errors, $stmt);
            array_push($errors, $values);
            array_push($this->errors, $errors);
        }
        return $result;
    }

    function update_me() {
        $buffer = [];
        $errors = [];
        $values = [];
        $primaryKey = $this->primaryKey;
        
        //Prepare the SQL parts
        
        $updateSQL = "UPDATE `%s` SET " . PHP_EOL;
        $update = sprintf($updateSQL,$this->table);

        //Prepare the bound fields
        foreach($this->fields as $f) {
            //Skip the primary key.
            if($f == $this->primaryKey) continue;

            //Skip this if MySQL wants to use an update (Extra) value (on update CURRENT_TIMESTAMP)
            if(in_array($f, $this->autoUpdateFields)) continue;

            $boundField = " %s = :%s ";
            array_push($buffer,sprintf($boundField,$f,$f));    
        }

        $boundFields = implode(', ' . PHP_EOL, $buffer);

        //Prepare the where
        $whereSQL = sprintf(" WHERE `%s`= :whereVal LIMIT 1; ", $this->primaryKey);

        //Concatenate them for the final statement.
        $finalSQL = $update . $boundFields . $whereSQL;

        //Prepare the statment
        $stmt = $this->pdo->prepare($finalSQL);

        //Generate the fields.
        foreach($this->fields as $f) {
            if($f == $this->primaryKey) continue;
            $values[$f] = $this->$f;
        }

        $values['whereVal'] = $this->$primaryKey;

        $result = $stmt->execute($values);

        if(!$result) {
            array_push($errors, $stmt->errorInfo());
            array_push($errors, $stmt);
            array_push($errors, $values);
            array_push($this->errors, $errors);
        }
        
        return $result;
    } 

    function insert_me() {
        $buffer = [];
        $errors = [];
        $primaryKey = $this->primaryKey;
        
        //Prepare the SQL parts
        $baseSql = "INSERT INTO `%s` ( %s ) VALUES ( %s )";

        $fbuffer = [];
        $vbuffer = [];

        foreach($this->fields as $field) {
            if($field == $this->primaryKey) continue;

            //Skip this if MySQL wants to use an default value (CURRENT_TIMESTAMP)
            if(in_array($field, $this->autoUpdateFields)) continue;

            array_push($fbuffer,$field);
            array_push($vbuffer,":". $field);
        }

        $fields = implode(', ', $fbuffer);
        $values = implode(', ', $vbuffer);

        $sql = sprintf($baseSql,$this->table,$fields,$values);

        $stmt = $this->pdo->prepare($sql);

        //Generate the fields.
        foreach($fbuffer as $f) {
            $buffer[$f] = $this->$f;
        }

        $result = $stmt->execute($buffer);

        if(!$result) {
            array_push($errors, $stmt->errorInfo());
            array_push($errors, $stmt);
            array_push($errors, $values);
            array_push($this->errors, $errors);
        } else {
            $this->$primaryKey = $this->pdo->lastInsertId();
        }

        return $result;
    }

    function threw_db_error() {
        return (count($this->errors) > 0)?true:false;
    }
     /* boundary(custom-methods)=--a19d1e7399aec0b42db5a508c49a968680ff20a8 */
     /* --a19d1e7399aec0b42db5a508c49a968680ff20a8 */


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
        if(!$this->loadFromEmail()) return false;

        return $this->validatePassword($password,$this->users_password);
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
        $sql = "SELECT users_id FROM `users` WHERE `users_email`= ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $values = [$this->users_email];

        $result = $stmt->execute($values);

        //Return false if no such email exists.
        if($stmt->rowCount() == 0) return false;

        //user exists. Load the user
        $row = $stmt->fetchObject();
        $this->users_id = $row->users_id;
        $this->load();
        return true;
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
        
        if($stmt->rowCount() == 0) return false;

        //user exists. Load the user
        $row = $stmt->fetchObject();
        $this->users_id = $row->users_id;
        $this->load_me();
        return true;
    }   


    /**
    * Loads a user based on the token in $_COOKIES
    *
    * @return boolean True if the user can be loaded, False otherwise.
    **/
    
    function loadFromToken() {
        $sql = "SELECT `users_id` FROM `users` WHERE `users_token` = ? || `users_mobile_token` = ? LIMIT 1";
        $stmt=$this->pdo->prepare($sql);
        $values = [$this->users_token,$this->users_token];

        if(!$stmt->execute($values)) {
            $error = $stmt->errorInfo();
            var_dump($stmt->errorInfo());
            var_dump($sql);
            var_dump($stmt);
        }

        if($stmt->rowCount() == 0) return false;

        $row = $stmt->fetchObject();
        $this->users_id = $row->users_id;
        $this->load();
        return true;
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
    
    function load() {
        $this->load_me();

        $myRole = new UsersRoles($this->pdo);
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

        $mail = new \PHPMailer();
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


    /* Beneath this line should be refactored out. */

    /**
    * Creates a salted hash from a given password.
    * @param string $password The password we are generating the hash from.
    * @return void
    **/
    
    public function createHash($password) {
            // format: algorithm:iterations:salt:hash
            $salt = base64_encode(mcrypt_create_iv(self::PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
            //This line should RETURN the password after being refactored.
            $this->users_password= self::PBKDF2_HASH_ALGORITHM . ":" . self::PBKDF2_ITERATIONS . ":" .  $salt . ":" . 
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
    
    private function validatePassword($password, $correct_hash) {
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
    
    private function slowEquals($a, $b) {
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

    private function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
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

     /* --a19d1e7399aec0b42db5a508c49a968680ff20a8 */
}