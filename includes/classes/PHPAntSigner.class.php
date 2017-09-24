<?php

namespace PHPAnt\Core;

/**
 * Represents a class of PHPAntSigner
 *
 * Generates (and verifies) PHP-Ant apps to ensure their legitimacy
 *
 * @package      PHP-Ant
 * @subpackage   Core
 * @category     Utilities
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

class PHPAntSigner
{

    /**
    * @var string $app The app name. (The directory name under includes/apps) for the app to verify. 
    **/

    var $app          = NULL;
    var $appPath      = NULL;
    var $appRoot      = NULL;
    var $appName      = NULL;
    var $manifestPath = NULL;
    var $files        = [];
    var $AE           = NULL;
    
    function __construct($options) {
        if(!isset($options['AE'])) throw new \Exception("You're trying to use the PHPAntSigner without passing an instance of the AppEngine as an option. See: https://php-ant.org/index.php/Class::PHPAntSigner", 1);
        
        //Set a default.
        if(!isset($options['appRoot'])) $options['appRoot'] = $options['AE']->Configs->getAppsDir();


        //Implement required options
        $this->AE      = $options['AE'];
        $this->appRoot = $options['appRoot'];
    }

    function setApp($appName) {
        $this->appName      = $appName;
        $this->appPath      = $this->appRoot . $this->appName;
        $this->manifestPath = $this->appPath .'/manifest.xml';
        $filePath           = $this->appPath .'/app.php';

        if(!file_exists($this->appPath)) {
            throw new \Exception("Could not find $this->appPath! Requested app ($appName) does not exist in $this->appRoot", 1);
            return false;
        }

        if(!file_exists($filePath)) {
            throw new \Exception("The requested app ($appName) does not have an app.php file as is required for a properly structured app. Failed to find $filePath.", 1);
            return false;
            
        }
        $this->app = $appName;

        return true;    
    }

    /**
     * Helper function to skip .git files with directory recursion.
     * Example:
     *
     * <code>
     * Example Code
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function manifestFilter($current, $key, $iterator) {
        // Allow recursion
        if ($iterator->hasChildren()) {
            return TRUE;
        }
        $blacklist = [ '.git'
                     , '.svn'
                     , 'manifest.xml'
                     , 'manifest.sig'
                     , 'private.key'
                     ];

        foreach($blacklist as $b) {
            if(stripos($current, $b)) return false;
        }

        return TRUE;
    }


    /**
     * Parses app information from the app.php file.
     * Example:
     *
     * <code>
     * $path = '/path/to/app.php';
     * $name = $AppEngine->getAppMeta($path,'name');
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     * @tested testAppParser
     **/

    function getAppMeta($path,$type,$regex=NULL) {
        switch ($type) {
            case 'name':
                $pattern = "#(App Name:)(.*)$#";
                break;
            case 'description':
                $pattern = "#(App Description:)(.*)$#";
                break;
            case 'version':
                $pattern = "#(App Version:)(.*)$#";
                break;
            case 'custom':
                $pattern = $regex;
                                    
            default:
                # code...
                break;
        }
        $buffer  = file($path);
        $matches = NULL;
        foreach($buffer as $line) {
            $line = trim($line);
            preg_match($pattern, $line,$matches);
            if(count($matches)) {
                return trim($matches[2]);
            }
        }
        return false;        
    }

    /**
     * Recurses into an app directory, counts the files, hashes them, and creates the manifest.xml file.
     * Example:
     *
     * <code>
     * $S = new PHPantSigner();
     * $S->setApp('some-app');
     * $S->generateManfiest();
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function generateManifestFile() {
        $filecount = 0;

        $iterator = new \RecursiveDirectoryIterator($this->appPath,\RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveCallbackFilterIterator($iterator, [$this,'manifestFilter']);

        //Get the files we need to work with.

        foreach( $files as $file) {
            //if ($file->getFilename()[0] === '.') continue;
            
            //hash the file
            $F = new PHPAntSignerFile($file);
            array_push($this->files, $F);
        }

        //Now that we have a list of files that are include with this app, let's generate the manifest.xml file.

        $name      = $this->getAppMeta($this->appPath . '/app.php','custom','/(class) ([a-zA-Z0-9_]*) {0,}(extends){0,1} [a-zA-Z0-9_-].*/');
        if(is_null($name)) throw new Exception("Could not parse class name from file $this->appPath to determine app name for manifest file.", 1);
        
        $namespace = $this->getAppMeta($this->appPath . '/app.php','custom','/(namespace) (.*);/');
        if(is_null($namespace)) throw new Exception("Could not parse namespace from file $this->appPath to determine app namespace!", 1);
        $app = new \SimpleXMLElement('<app/>');
        
        $app->addAttribute('name',$name);
        $app->addAttribute('namespace',$namespace);

        //Loop through the files, and add each one to the manifest file.
        foreach($this->files as $file) {
            $child = $app->addChild('file');
            $name = $child->addChild('name',$file);
            $hash = $child->addChild('hash',$file->gethash());
        }

        $manifestPath = $this->appPath . '/manifest.xml';

        //Format XML to save indented tree rather than one line
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($app->asXML());
 
        $dom->save($manifestPath);

        return $manifestPath;

    }

    function derivePublicKey($pathToPrivateKey) {
        $secretKey = base64_decode(file_get_contents($pathToPrivateKey));
        $publicKey = \Sodium\crypto_sign_publickey_from_secretkey($secretKey);
        return $publicKey;
    }

    function registerHook($hook,$function,$priority = 50) {
        $signature       = sha1($hook.$function.$priority);
        $manifestPath    = $this->appPath . '/manifest.xml';
        $app             = simplexml_load_file($manifestPath);

        //Only allow a hook, function, priority tuple to be added ONCE.
        foreach($app->action as $a) {
            if((string)$a->signature == $signature) return $signature;
        }

        $action          = $app->addChild('action');
        $hook            = $action->addChild('hook',$hook);
        $function        = $action->addChild('function',$function);
        $priority        = $action->addChild('priority',$priority);
        $actionSignature = $action->addChild('signature',$signature);

        //Format XML to save indented tree rather than one line
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($app->asXML());
 
        $dom->save($manifestPath);

        return $signature;
    }

    function removeHook($signature) {
        $manifestPath = $this->appPath . '/manifest.xml';
        $dom = new \DOMDocument('1.0');
        $dom->load($manifestPath);
        $elements = $dom->getElementsByTagName('signature');

        //There should only be ONE element with this signature.
        $node = $elements[0];
        $actionNode = $node->parentNode;
        $appNode = $actionNode->parentNode;

        $appNode->removeChild($actionNode);

        //Format XML to save indented tree rather than one line
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
 
        $dom->save($manifestPath);
    }

    /**
     * Signs the manifest file with the private key specified.
     *
     * This function signs manifest.xml to ensure that its contents cannot be
     * altered. This function WILL not work if private.key is found in the app
     * file, and will throw an exception. Users are encouraged to move
     * private.key to a safe place where it cannot be included in the app for
     * distribution.
     *
     * Additionally, public.key must be secured. At minimum, file permissions
     * should set this file to READ ONLY and owned by root (or another super
     * user) for higher levels of security.
     *
     * Example:
     *
     * <code>
     * $S->
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/
    function signApp($privateKeyPath) {

        if(!file_exists($privateKeyPath)) throw new \Exception('Private key ($privateKeyPath) does not appear to exist. Private key is required for signing.', 1);
        
        //Need to find a better way to protect people
        //$privateKeyShouldNotBeInAppPath = $this->appPath . '/private.key';
        //if(file_exists($privateKeyShouldNotBeInAppPath)) throw new \Exception("You CANNOT leave your private key in your app path. Move it to another location where it will not get distributed with your app.", 1);
        
        $manifestPath          = $this->appPath . '/manifest.xml';
        $manifestSignaturePath = $this->appPath . '/manifest.sig';
        $message = file_get_contents($manifestPath);
        $signed_msg = \Sodium\crypto_sign_detached(
            $message,
            base64_decode(file_get_contents($privateKeyPath))
        );

        $fh = fopen($manifestSignaturePath,'w');
        fwrite($fh,base64_encode($signed_msg));
        fclose($fh);

        return file_exists($manifestSignaturePath);
    }

    function verifySignature() {
        $manifestPath          = $this->appPath . '/manifest.xml';
        $manifestSignaturePath = $this->appPath . '/manifest.sig';
        $publicKeyPath         = $this->appPath . '/public.key';
        //var_dump($publicKeyPath);
        //die(__FILE__  . ':' . __LINE__ );
        if(!file_exists($publicKeyPath)) return false;

        //Get the signature of the manifest file
        $publicKey = base64_decode(file_get_contents($publicKeyPath));
        $message   = file_get_contents($manifestPath);
        $signature = base64_decode(file_get_contents($manifestSignaturePath));

        //Compare the signature the the actual manifest file
        if (\Sodium\crypto_sign_verify_detached($signature, $message, $publicKey )) {
            return true;
        } else {
            //echo "Invalid signature detected! ($manifestSignaturePath)" . PHP_EOL;
            return false;
        }
    }

    function verifyApp() {
        //1. Verify the signature on the manifest.xml file before we can trust it.
        $results = [];

        //2. Verify the files all have matching hashes.
        $integrityOK = $this->verifySignature();
        
        if(!$integrityOK) $results[$this->appPath . '/manifest.xml'] = "INVALID SIGNATURE";

        $app = simplexml_load_file($this->appPath . '/manifest.xml');
        foreach($app->file as $file) {
            $filename = (string)$file->name;
            if(sha1_file($file->name) !== (string)$file->hash) {
                $results[$filename] = "HASH FAILURE";
                //print "There was a problem with $file->name. Hashes do not match the manifest." . PHP_EOL;
                $integrityOK = false;
            } else {
                $results[$filename] = "OK";
            }
        }

        
        $results['integrityOK'] = $integrityOK;
        return $results;
    }

    /**
     * Generates a public / private keypair for code signing
     * Example:
     *
     * Requires:
     * -libSodium See: https://paragonie.com/book/pecl-libsodium/read/00-intro.md#installing-libsodium
     *
     * @return array An array containing the paths for public and private keys you created.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function genKeys($instructions = false) {
        $bob_seed = \Sodium\randombytes_buf(\Sodium\CRYPTO_SIGN_SEEDBYTES);
        $bob_sign_kp = \Sodium\crypto_sign_seed_keypair($bob_seed);

        // Split the key for the crypto_sign API for ease of use
        $bob_sign_secretkey = \Sodium\crypto_sign_secretkey($bob_sign_kp);
        $bob_sign_publickey = \Sodium\crypto_sign_publickey($bob_sign_kp);        
        $public             = base64_encode($bob_sign_publickey);
        $private            = base64_encode($bob_sign_secretkey);
        $publicKeyPath      = $this->AE->Configs->document_root . 'public.key';
        $privateKeyPath     = $this->AE->Configs->document_root . 'private.key';

        $fh = fopen($publicKeyPath,'w');
        fwrite($fh,$public);
        fclose($fh);

        $fh = fopen($privateKeyPath,'w');
        fwrite($fh,$private);
        fclose($fh);

        if($instructions) {
            printf("Your public key has been generated here: $publicKeyPath" . PHP_EOL);
            printf("Your private key has been generated here: $privateKeyPath" . PHP_EOL);
            printf("");
            printf("Take a moment to MOVE the private key to a safe place. You'll need it" . PHP_EOL);
            printf("to sign other apps. For simplicity, you should use the same private key to sign" . PHP_EOL);
            printf("all your apps." . PHP_EOL);
            printf("" . PHP_EOL);
            printf("You should also make a COPY of your public key and store it for safe keeping as well." . PHP_EOL);
            printf("Your public key should be issued with your apps (this one and new ones)." . PHP_EOL);
        }
        $this->AE->Configs->setConfig('signing-key',$privateKeyPath);
    }

    private function saveDerivedPublicKey($privateKeyPath) {
        //Get the public key.
        $publicKey = $this->derivePublicKey($privateKeyPath);
        //Save it to the public.key file.
        $publicKeyPath = $this->appPath . '/public.key';        
        $fh = fopen($publicKeyPath,'w');
        fwrite($fh,base64_encode($publicKey));
        fclose($fh);
        return $publicKeyPath;
    }

    function updatePublicKey($privateKeyPath) {
        $publicKeyPath = $this->saveDerivedPublicKey($privateKeyPath);

        $hash = sha1_file($publicKeyPath);

        //Update the manifest.
        $dom = new \DOMDocument('1.0');
        $dom->load($this->manifestPath);
        $elements = $dom->getElementsByTagName('name');
        foreach($elements as $node) {

            $buffer = explode('/',(string)$node->nodeValue);
            if(end($buffer)=='public.key') {
            //Save the path to the key.
            $keyPath = $node->nodeValue;

            //Remove this element.
            $fileNode = $node->parentNode;
            $appNode = $fileNode->parentNode;
            $appNode->removeChild($fileNode);

            //Add the new, updated one in:
            $newFileNode = $dom->createElement('file');
            $nameNode    = $dom->createElement('name',$keyPath);
            $hashNode    = $dom->createElement('hash',$hash);
            //$editedNode  = $dom->createElement('edited','true');

            //Add the name and has to the file node.
            $newFileNode->appendChild($nameNode);
            $newFileNode->appendChild($hashNode);
            //$newFileNode->appendChild($editedNode);
            
            //add the newFileNode to the document
            $appNode->appendChild($newFileNode);

            break;
            }
        }

        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        $dom->save($this->manifestPath);        
    }


    /**
     * Cleans the app to prepare for publication by removing manifest.sig,
     * manifest.xml, public.key, etc...
     *
     * Example:
     *
     * <code>
     * $Signer = new PHPAnt\Core\PHPAntSigner([]);
     * $Signer->setApp('some-app');
     * $Signer->cleanAppCredentials();
     * </code>
     *
     * @return return value
     * @param param
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function cleanAppCredentials() {
        $removeTheseFiles = [ 'manifest.xml'
                            , 'manifest.sig'
                            , 'public.key'
                            ];
        foreach($removeTheseFiles as $fileToDelete) {
            $path = $this->appPath . '/' . $fileToDelete;
            if(file_exists($path)) unlink($path);
        }

        $clean = true;
        foreach($removeTheseFiles as $fileToDelete) {
            if(file_exists($fileToDelete)) $clean = false;
        }

        return $clean;
    }

    /**
     * Runs all the necessary functions to generate a manifest, add actions,
     * sign, and verify an app for publication and use.
     *
     * Example:
     *
     * <code>
     * $args['AE']             = $AE;
     * $args['privateKeyPath'] = '/path/to/key.rsa';
     * $Signer->publish($args);
     * </code>
     *
     * @return boolean True if the process was successful and the app was
     *         signed and verified. False otherwise.
     * @param string $privateKey The fully qualified path to the private key used for signing this app.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function publish($args) {
        $AE             = $args['AE'];
        $privateKeyPath = $args['AE']->Configs->getConfigs(['signing-key'])['signing-key'];

        $return = [];

        $return['setApp'] = $this->setApp($args['appName']);

        //1. Remove files related to publication.
        $return['cleanAppCredentials'] = $this->cleanAppCredentials();

        //2. Derive public key from private key and add to app.
        $return['saveDerivePublicKey'] = $this->saveDerivedPublicKey($privateKeyPath);

        //3. Generate manifest
        $return['generateManifestFile'] = $this->generateManifestFile();
               
        //4. Add actions.
        $actions = $AE->getAppActions($this->appPath . '/app.php');
        $return['appActions'] = json_encode($actions);

        //Add the actions to the manifest file!
        foreach($actions as $hook => $data) {
            foreach($data as $callback => $priority) {
                $this->registerHook($hook,$callback,$priority);
            }
        }

        //5. Sign app
        $return['signApp'] = $this->signApp($privateKeyPath);

        //6. Verify app.
        $return['verifyApp'] = $this->verifyApp();

        return $return;
    }
}