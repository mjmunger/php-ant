<?php

namespace PHPAnt\Core;

use \PDO;

/**
 * Represents an email notification
 **/

 /**
 * This class represents an email notification, and is responsible for
 * procuring the email template from a given location, doing form substitution
 * on the fields, and instantiating a PHPMailer instance to send the final
 * email.
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     Email Utilities
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */

class Notif {

    /**
     * @var PDO $pdo An instance of PDO for database operations.
     * */

    public $pdo = NULL;

    /**
    * @var array findReplace An array that contains the values which will be found / replaced before the email is sent.
    **/

    public $findReplace = array();


    /**
    * @var string $templateDirectory The directory where the template for this email will be found.
    **/

    public $templateDirectory = NULL;


    /**
    * @var string $template The template of the email, which will become the body of the email.
    **/

    public $template = NULL;


    /**
    * @var string $subject The rendered subject that will be sent in the email.
    **/

    public $subject = NULL;

    /**
    * @var string $body The rendered body of the email.
    **/

    public $body = NULL;


    /**
    * @var string $to The "to" address for the email.
    **/

    public $to = NULL;

    /**
    * @var string $fromAddress Holds the from address of the sender.
    **/

    public $fromAddress = NULL;

    /**
    * @var string $fromName Holds the  from name of the sender.
    **/

    public $fromName = NULL;

    /**
    * @var string $replyToAddress Holds the  reply-to address (if different from sender address)
    **/

    public $replyToAddress = NULL;

    /**
    * @var string $replyToName Holds the reply-to name if different from the sender.
    **/

    public $replyToName = NULL;

    /**
    * @var array $cc Holds an array of addresses that should be included in the cc.
    **/

    public $cc = array();

    /**
    * @var array $bcc Holds an array of email addressses that should be included in the bcc
    **/

    public $bcc = array();


    /**
    * @var int $verbosity The level of debugging verbosity for this notif.
    **/

    public $verbosity = 0;

    /**
     * @var string subjectTemplate The template used for the email subject.
     * */

    public $subjectTemplate = NULL;

    /**
     * @var boolean useVERP Tells the notif whether or not to use VERP.
     * 
     * When VERP is enabled, a GUID is appeneded to the from address envelop
     * and this message is saved in the email log so that the send
     * and receive status can be tracked from inside your application.
     * Also, this enables the logging feature of PHPAnt. Loggin is not possible
     * without Verp.
     * */

    public $useVERP = true;

    /**
     * @var string verpId String that is used as the VERP id in the oubound mail.
     * */

    public $verpId = NULL;

    /**
     * @var string finalHeaders The final, rendered headers of an email that is sent.
     * */

    public $finalHeaders = NULL;

    /**
     * @var string returnPath The return path for the envelop of a message. Used with Verp.
     * */

    public $returnPath = NULL;

    /**
     * Instantiate a Notif object
     * Example:
     *
     * <code>
     * $n = new Notif() //system email message
     * //OR
     * $n = new Notif(__DIR__ . 'emails/'); //Use the plugin directory + emails/ as the template source.
     * </code>
     *
     * @return void
     * @param string $templateDir The patht to the directory, which contains the email template for the notification to be sent.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function __construct($templateDir, PDO $pdo) {

        $this->pdo = $pdo;
        $this->templateDirectory = $templateDir . '/emails/';
        $d = new \DateTime();
        $this->addFindReplace('THISYEAR',$d->format("Y"));
    }

    /**
     * Adds a find / replace pair to Notif::findReplace, which will be used to modify the template prior to being sent.
     * Example:
     *
     * Template substitution tags in the templae MUST be upper case and enclosed in '%'. This method automatically wraps 
     * precent signs (%) around the string provided for the $find parameter, and converts it to upper case. 
     * For example, name becomes: %NAME%.
     *
     * <code>
     * $n = new Notif();
     * $n->addFindReplace('%NAME%','Michael');
     * </code>
     *
     * @return void
     * @param string $find The string (not enclosed in %) we will search the email template for.
     * @param string $replace The string we will replace that string with.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function addFindReplace($find, $replace) {
        $find = "%" . strtoupper($find) . "%";
        $this->findReplace[$find] = $replace;
        if($this->verbosity > 9) {
            echo "Added:" . PHP_EOL;
            echo str_pad('Find', 10);
            echo $find;
            echo PHP_EOL;

            echo str_pad('Replace', 10);
            echo $replace;
            echo PHP_EOL;
        }
    }

    /**
     * Sets the template to be used.
     * Example:
     *
     * <code>
     * $n = new Notif();
     * $templateName = 'welcome-email';
     * $exists = $n->setTemplate($templateName);
     * if(!$exists) {
     *     echo "The template ($templateName) does not exist!";
     *     die();
     * }
     * </code>
     *
     * @return boolean True if the template exists, false otherwise.
     * @param string $template The name of the file that will be used as a template for the email notification.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function setEmailTemplate($templateName) {
        $errors = array();
        $templatePath = $this->templateDirectory . $templateName;
        $exists = file_exists($templatePath);
        if($exists) {
            $this->template = file_get_contents($templatePath);
            return false;
        } else {
            array_push($errors, "The template $templateName does not exist. I was looking here: $templatePath");

            /* Check to see if the template directory exists. */
            if(!is_dir($this->templateDirectory)) {
                array_push($errors, "The templates directory $this->templateDirectory does not exist either.");
            }
            return $errors;
        }
    }

    /**
     * Sets the subject template for the object.
     * Example:
     *
     * <code>
     * $n->setSubjectTemplate = "%FIRSTNAME%, you need to click this link!"
     * </code>
     *
     * @return void
     * @param string $templateString The subject of an email, which may contain find and replace tag definitions.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function setSubjectTemplate($templateString) {
        $this->subjectTemplate = $templateString;
    }

    /**
     * Sets the from address of the email.
     * Example:
     *
     * <code>
     * $n = new Notif();
     * $n->setFromAddress('michael@highpoweredhelp.com');
     * </code>
     *
     * @return mixed False if successful, array with errors otherwise.
     * @param string $address the well formed email address of the sender.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function setFromAddress($address) {
        $this->fromAddress = filter_var($address,FILTER_VALIDATE_EMAIL);
        if($this->fromAddress === false) {
            $errors = ['error' => "Could not validate $address as a valid email."];
            return $errors;
        } else {
            return false; //Everything was OK!
        }

        //Use VERP if defined.
    }

    /**
     * Sets the from name for the email, and filters / verifies the string to ensure it's safe / not blank.
     * Example:
     *
     * <code>
     * $e->setFromName("Michael Munger")
     * </code>
     *
     * @return mixed An array of errors if there is a problem with the from name, or false if setting it was successful.
     * @param string $name The name that should appear in the "From" field of the outbound email.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function setFromName($name) {
        if(strlen($name) == 0) {
            $errors = ['error' => "You must supply a \"From Name\" for an email."];
            return $errors;
        }


        $this->fromName = filter_var($name,FILTER_SANITIZE_STRING);

        if($this->fromName === false) {
            $errors = ['error' => 'Could not validate the from name as a legitimate string (name). You must supply a "From Name" for an email.'];
            return $errors;
        }

        return false; //Everything is OK!
    }

    /**
     * Finds any template tags in the template that are not defined in the notification (and would be sent as %SOMETHING% if not corrected). This is called by Notif::verifySubstitutions()
     *
     * @return array A list of all the template tags that need a definition before the Notif can be sent.
     * @param string $string The message to be checked. Normally, $this->template.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function findMissingDefinitions($string) {
        /* Get all the tags in this template */
        preg_match_all('#%[A-Z0-9]*%#', $string, $tags);
        $templateTags = $tags[0];

        /* Get the replace definitions so we can compare them to the tags present in the template. */
        $replaceDefinitions= array_keys($this->findReplace);

        /* Show me what's in the template, that's not in the definitions*/
        $missing = array_diff($templateTags, $replaceDefinitions);

        return $missing;
    }

    /**
     * Verifies all the substitution fields in the template have a matching
     * find and replace definition. This is also called by the send()
     * function, which will only send the email if this function returns true.
     *
     * Example:
     *
     * <code>
     * $n = new Notif();
     * $n->setTemplate('welcome-email.html');
     * $n->addFindReplace('%NAME%',$firstName);
     * $verificationResult = $n->verifySubstitutions();
     * if($verificationResult === true) {
     *     $n->send();
     * } else {
     *     foreach($verificationResult as $tag) {
     *         echo "You need to add a find and replace definition for $tag" . PHP_EOL;
     *      }
     * }
     * </code>
     *
     * @return mixed True if all substitutions have a matching find and
     *         replace definition. Otherwise, it will return an array of substitution
     *         tags that are not represented.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    public function verifySubstitutions() {

        $errors = array();
        $missing = $this->findMissingDefinitions($this->template);

        $missing = array_unique($missing);

        if(count($missing) > 0) {
            foreach($missing as $tag) {
                array_push($errors, "$tag is in the template, but has not been set. Please define a find and replace definition for $tag");
            }
            return $errors;
        }

        /* Do the same for the subject */
        $missing = $this->findMissingDefinitions($this->subjectTemplate);

        $missing = array_unique($missing);

        if(count($missing) > 0) {
            foreach($missing as $tag) {
                array_push($errors, "$tag is in the subject, but has not been set. Please define a find and replace definition for $tag");
            }
            return $errors;
        }

        return false;
    }

    /**
     * Renders the final body of the email, and stores it in Notif::body (and also returns it).
     *
     * This function will RECURSE if it continues to find unresolved tags. For
     * example, the %THISYEAR% default tag may or may not be resolved in a
     * template on the first go-round, if it remains at the end of the first
     * find and replace session, the function will recurse to ensure it is
     * replaced.
     *
     * Example:
     *
     * <code>
     * $body = $n->renderBody();
     * </code>
     *
     * @return string The final body of the email.
     * @param  string $body Use this to override the template of the notification for rendering the body. You should probably never use this.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    private function renderBody($body=false) {
        if($body) {
            $this->body = $body;
        } else {
            $this->body = $this->template;
        }

        foreach($this->findReplace as $find => $replace) {
            $this->body = str_replace($find, $replace, $this->body);
        }

        preg_match_all('#%[A-Z0-9]*%#', $this->body, $tags);
        $templateTags = $tags[0];
        if(count($tags[0]) > 0) {
            /* Recurse this function the case settings contain fields that are not outright replaced in the first execution of this function. */
            if($this->verbosity > 9) {
                echo "OUSTANDING TEMPLATE TAGS";
                echo "<pre>"; var_dump($tags[0]); echo "</pre>";
            }
            return $this->renderBody($this->body);
        }

        return $this->body;
    }

    /**
     * Renders the final subject of the email, and stores it in Notif::subject (and also returns it)
     * Example:
     *
     * <code>
     * $body = $n->renderSubject();
     * </code>
     *
     * @return string The final subject of the email.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    private function renderSubject() {
        $this->subject = $this->subjectTemplate;

        foreach($this->findReplace as $find => $replace) {
            $this->subject = str_replace($find, $replace, $this->subject);
        }

        return $this->subject;
    }

    /**
     * Ensures that all template tags have been properly assigned, and if not, returns an error. Otherwise, it will render the email body and subject in preparation for sending the email notification.
     * Example:
     *
     * <code>
     *  if($n->prepareSend()) {
     *       foreach($errors as $err) {
     *           echo $error;
     *       }
     *   } else {
     *       echo "Prepare successful!";
     *   }
     * </code>
     *
     * @return mixed an array of errors if there are any templating errors (or other errors). Otherwise, false upon success.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    final function prepareSend() {

        $errors = $this->verifySubstitutions();

        if($errors !== false) return  $errors;

        /* Prepare the body with substitutions */
        $this->renderBody();

        /* Prepare the subject with substitutions */
        $this->renderSubject();

        /* Return false on success so there are no "errors" being reported. */
        return false;
    }


    /**
     * Creates a unique ID for tracing emails
     * @return string the unique ID that can be appended to a from address.
     * */

    public function createVerpID() {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $dictionary = str_split($chars);

        $buffer = [];

        $counter = 0;
        while($counter < 32) {
            if(version_compare(phpversion(), '7.0.0','<')) {
                $buffer[$counter] = $dictionary[rand(0,61)];
            } else {
                $buffer[$counter] = $dictionary[random_int(0,61)];
            }
            $counter ++;
        }

        return implode("", $buffer);
    }

    public function setupVerp() {

        if($this->fromAddress === null) throw new Exception("You cannot setupVerp without first setting a fromAddress", 1);

        if($this->useVERP == false) {
            $this->returnPath = $this->fromAddress;
            return true;
        }

        $this->verpId = $this->createVerpID();
        $buffer = explode("@", $this->fromAddress);
        $buffer[0] = $buffer[0] .= "+=" . $this->verpId;
        $this->returnPath = implode('@', $buffer);
        
        return true;
    }

    public function renderHeaders() {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = "From: $this->fromName <$this->fromAddress>";
        $headers[] = "Return-Path: $this->returnPath";

        if(count($this->cc) > 0) $headers[] = "Cc: " . implode('; ', $this->cc);

        $this->finalHeaders = implode("\r\n", $headers);
    }

    public function logSend($name,$address) {

        $sql = <<<EOQ
INSERT INTO `bugreport`.`email_log`
(
`email_log_to`,
`email_log_from`,
`email_log_subject`,
`email_log_body`,
`email_log_headers`
)
VALUES
(
:email_log_to, 
:email_log_from, 
:email_log_subject, 
:email_log_body, 
:email_log_headers
);
EOQ;

        $stmt = $this->pdo->prepare($sql);
        $values = [ 'email_log_to'                   => $address
                  , 'email_log_from'                 => $this->fromAddress
                  , 'email_log_subject'              => $this->subject
                  , 'email_log_body'                 => $this->body
                  , 'email_log_headers'              => $this->finalHeaders
                  ];
        $result = $stmt->execute($values);

        if($result === false) return ['success' => false, 'error' => $stmt->errorInfo()[2] ];

        return ['success' => true, 'email_log_id' => $this->pdo->lastInsertId()];
    }

    /**
     * Magic function that executes prepareSend, which runs
     * verifySubstitutions, renderBody, and renderSubject, then checks for
     * errors, builds the sending headers, builds the cc list, renders and
     * sends the email.
     **/

    public function send($name,$address) {
        $errors = $this->prepareSend();

        if($errors !== false) return errors;

        $this->setupVerp();

        $this->renderHeaders();

        $params = ($this->useVERP ? "-f $this->returnPath" : '');

        $result = mail($address, $this->subject, $this->body, $this->finalHeaders, $params);

        if($this->useVERP) $this->logSend($name,$address);

        return ($result && $this->useVERP ? $this->verpId : $result);
    }
}
