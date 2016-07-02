<?php

namespace PHPAnt\Core;

/**
* Interfaces used in the BFW Toolkit
*
* This file contains all the interfaces for the various classes defined in the BFW Toolkit.
*
* @package      BFW Toolkit
* @subpackage   Core
* @category     Interfaces
* @author       Michael Munger <michael@highpoweredhelp.com>
*/ 
     
/**
 * Declare the interface for BFWUser
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

interface BFWUser
{
    /**
    * Authenticates a user based on a given password.
    * 
    * Requires you set $this->users_email first. This function loads the user
    * based on the email address associated, and then creates a password hash from
    * the given password to compare for a match.   *
    * @param string $password The password we're checking to see if it's right.
    * @return boolean. If they match, it returns true. Otherwise, false.
    **/ 

    public function authenticate($password);
    
    /**
    * Creates or loads an email account based on the presence of the email address / user association.
    * 
    * Creates a new user if an account with the email address does not yet exist. 
    * @return void
    **/

    public function createIfNew();

    /** 
    * Loads a user based on email address
    * 
    * Requires you first set $this->users_email. Then it will load a user based on that unique email address.
    * @return boolean True if the user exists, false if the user does not.
    **/    

    public function loadFromEmail();

    /**
    * Loads a user based on the nonce associated with an activation.
    * @return boolean True if the nonce exists and a user is found, otherwise, false.
    **/

    public function loadFromActivation();
    
    /**
    * Validates a given password against the hash in the database
    *
    * @param string $password The password we are submitting for verification.
    * @param string $correct_hash The hash we should get if the password is correct.
    **/ 

    public function validatePassword($password, $correct_hash);

    /**
    * Loads a user based on the token in $_COOKIES
    *
    * @return boolean True if the user can be loaded, False otherwise.
    **/

    public function loadFromToken();

    /**
    * Concatenates the first and last name of a user into a single string.
    *
    * @return string
    **/

    public function getFullName();

    /**
    * Returns a human readable roll.
    *
    * @return string
    **/

    public function getRole();

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
    * @param object $notif An instantiated Notif object.
    * 
    * @return boolean True if the email could be sent, false otherwise.
    **/
    
    public function email(Notif $notif);
}

/**
 * Declare the interface for a Plugin
 * @author Michael Munger <michael@highpoweredhelp.com>
 **/

interface AppInterface {
    function addHook($hook, $priority, $callback, $arguments);
    function trigger($hook);
    function getName();
    function getVersion();
    function getStatus();
    function setVerbosity($int);
    function canReload();
}
?>