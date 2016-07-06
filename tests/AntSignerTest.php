<?php

require_once('tests/test_top.php');

use PHPUnit\Framework\TestCase;

class PHPAntSignerTest extends TestCase
{
	function testGenKeys() {

		$publicKeyPath  = 'includes/apps/TestApp/public.key';
		$privateKeyPath = 'includes/apps/TestApp/private.key';
		
		$who = exec('whoami');
		$privateKeyStoragePath = "/home/$who/private.key";


		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->genKeys();

		copy($privateKeyPath,$privateKeyStoragePath);

		$this->assertFileExists($publicKeyPath);
		$this->assertFileExists($privateKeyPath);
	}

	function testSetApp() {
		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$result = $S->setApp('TestApp');
		$this->assertTrue($result);
		$this->assertSame('TestApp', $S->app);
	}

	/**
	 * @depends testSetApp
	 **/

	function testGenerateManifestFile() {
		$manifestFilePath = 'includes/apps/TestApp/manifest.xml';
		if(file_exists($manifestFilePath)) unlink($manifestFilePath);

		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->generateManifestFile();
		$this->assertCount(3, $S->files);

		foreach($S->files as $file) {
			$this->assertInstanceOf('\PHPAnt\Core\PHPAntSignerFile', $file);
		}

		$manifestFilePath = 'includes/apps/TestApp/manifest.xml';
		$this->assertFileExists($manifestFilePath);

		$app = simplexml_load_file($manifestFilePath);

		$this->assertSame('TestAntApp', (string)$app['name']);
		$this->assertSame('PHPAnt\Apps', (string)$app['namespace']);

		foreach($app->file as $f) {
			$this->assertFileExists((string)$f->name);
		}

		foreach($app->file as $f) {
			$filePath = (string)$f->name;
			$this->assertSame(sha1_file($filePath), (string)$f->hash);
		}
	}

	function testRegisterHook() {
		$hook             = 'cli-init';
		$function         = 'declareMySelf';
		$signature        = $hook.$function.'50';
		$manifestFilePath = 'includes/apps/TestApp/manifest.xml';

		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->registerHook($hook,$function);

		$dom = new \DOMDocument('1.0');
        $dom->load($manifestFilePath);
        $elements = $dom->getElementsByTagName('signature');
        $found = false;

        foreach($elements as $node) {
        	if($node->nodeValue = $signature) {
        		$theNode = $node->parentNode;
        		$found=true;
        	}
        }

        //The node should exist.
		$this->assertTrue($found);

		//Checking to make sure we have cli-init as the hook.
		$hook = $theNode->getElementsByTagName('hook');
		$this->assertSame('cli-init', (string)$hook[0]->nodeValue);

		$hook = $theNode->getElementsByTagName('function');
		$this->assertSame('declareMySelf', (string)$hook[0]->nodeValue);

		$hook = $theNode->getElementsByTagName('priority');
		$this->assertSame('50', (string)$hook[0]->nodeValue);

	}

	function testUnregisterHook() {
		$hook             = 'cli-init';
		$function         = 'declareMySelf';
		$signature        = $hook.$function.'50';
		$manifestFilePath = 'includes/apps/TestApp/manifest.xml';

		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');

		$dom = new \DOMDocument('1.0');
        $dom->load($manifestFilePath);
        $elements = $dom->getElementsByTagName('signature');
        $found = false;

        foreach($elements as $node) {
        	if($node->nodeValue = $signature) {
        		$theNode = $node->parentNode;
        		$found=true;
        	}
        }

        $this->assertTrue($found);

        $S->removeHook($signature);

        //Remove this node.
		$dom = new \DOMDocument('1.0');
        $dom->load($manifestFilePath);
        $elements = $dom->getElementsByTagName('signature');
		$this->assertEquals(0, $elements->length);
	}

	/**
	 * @depends testGenKeys
	 **/

	function testSignApp() {

		$publicKeyPath         = 'includes/apps/TestApp/public.key';
		$manifestFileSignature = 'includes/apps/TestApp/manifest.sig';
		$privateKeyFailurePath = 'includes/apps/TestApp/private.key';

		//Undo the file we made earlier.
		unlink($privateKeyFailurePath);
		$this->assertFalse(file_exists($privateKeyFailurePath));

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key";
		
		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$this->assertFileExists($privateKeyPath);
		$S->signApp($privateKeyPath);

		$this->assertFileExists($manifestFileSignature);

	}
	 /**
	  * @depends testSignApp
	  **/

	function testPrivateKeyMissingException() {
		$publicKeyPath         = 'includes/apps/TestApp/public.key';
		$privateKeyFailurePath = 'includes/apps/TestApp/private.key';
		$manifestFileSignature = 'includes/apps/TestApp/manifest.sig';

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key.wrong";
		
		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');

		$this->assertFileExists($manifestFileSignature);

		$this->expectException('Exception');
		$S->signApp($privateKeyPath);
	}

	/**
	 * @depends testPrivateKeyMissingException
	 **/

	function testPrivateKeyInAppException() {
		$publicKeyPath         = 'includes/apps/TestApp/public.key';
		$privateKeyFailurePath = 'includes/apps/TestApp/private.key';
		$manifestFileSignature = 'includes/apps/TestApp/manifest.sig';

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key";
		
		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');

		$this->assertFileExists($manifestFileSignature);

		$fh = fopen($privateKeyFailurePath,'w');
		fwrite($fh,'key would go here');
		fclose($fh);

		$this->expectException('Exception');
		$S->signApp($privateKeyPath);
	}
		
	/**
	 * @depends testSignApp
	 **/

	function testVerifyApp() {
		$who = exec('whoami');

		$publicKeyPath  = 'includes/apps/TestApp/public.key';
		$privateKeyPath = 'includes/apps/TestApp/private.key';
		$privateKeyStoragePath = "/home/$who/private.key";

		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->genKeys();
		copy($privateKeyPath,$privateKeyStoragePath);
		unlink($privateKeyPath);

		$S->signApp($privateKeyStoragePath);

		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$result = $S->verifySignature();		
		$this->assertTrue($result);
	}

	function testUpdatePublicKey() {
		$who = exec('whoami');

		$manifestPath   = 'includes/apps/TestApp/manifest.xml';

		$publicKeyPath  = 'includes/apps/TestApp/public.key';
		if(file_exists($publicKeyPath)) unlink($publicKeyPath);
		$this->assertFalse(file_exists($publicKeyPath));

		$privateKeyPath = 'includes/apps/TestApp/private.key';
		if(file_exists($privateKeyPath)) unlink($privateKeyPath);
		$this->assertFalse(file_exists($privateKeyPath));

		$privateKeyStoragePath = "/home/$who/private.key";
		$this->assertTrue(file_exists($privateKeyStoragePath));

		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);

		$S->setApp('TestApp');
		$S->updatePublicKey($privateKeyStoragePath);

		//Make sure the file exists.
		$this->assertTrue(file_exists($publicKeyPath));
		$hash = sha1_file($publicKeyPath);

		//Make sure THIS hashed version is what's in the manifest file.
		$app = simplexml_load_file($manifestPath);

		foreach($app->file as $f) {
			$buffer = explode('/',(string)$f->name);
			if(end($buffer) == 'public.key') {
				$this->assertSame($hash, (string)$f->hash);
				break;
			}
		}
	}

	function testAddHook() {
		$manifestPath   = 'includes/apps/TestApp/manifest.xml';
		$options = []; //$options['appRoot'] = 'includes/apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);

		$S->setApp('TestApp');
		$hook = 'app-hook-test';
		$callback = 'doAppHookTest';
		$priority = 50;
		$hash = sha1($hook.$callback.$priority);
		$signature = $S->registerHook($hook,$callback,$priority);

		$this->assertSame($hash, $signature);
		$app = simplexml_load_file($manifestPath);
		$this->assertEquals(1, count($app->action));
	}

/*	function testDisableThisApp() {
		$AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);
        $result = $AE->disableApp('TestAntApp','includes/apps/TestApp/app.php');
        $this->assertTrue($result);
	}*/
}