<?php

require_once('tests/test_top.php');

use PHPUnit\Framework\TestCase;

class PHPAntSignerTest extends TestCase
{
	function testGenKeys() {

		$publicKeyPath  = 'tests/PHPAnt/Core/resources/Apps/TestApp/public.key';
		$privateKeyPath = 'tests/PHPAnt/Core/resources/Apps/TestApp/private.key';
		
		$who = exec('whoami');
		$privateKeyStoragePath = "/home/$who/private.key";


		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->genKeys();

		copy($privateKeyPath,$privateKeyStoragePath);

		$this->assertFileExists($publicKeyPath);
		$this->assertFileExists($privateKeyPath);
	}

	function testSetApp() {
		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$result = $S->setApp('TestApp');
		$this->assertTrue($result);
		$this->assertSame('TestApp', $S->app);
	}

	/**
	 * @depends testSetApp
	 **/

	function testGenerateManifestFile() {
		$manifestFilePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.xml';
		if(file_exists($manifestFilePath)) unlink($manifestFilePath);

		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->generateManifestFile();
		$this->assertCount(3, $S->files);

		foreach($S->files as $file) {
			$this->assertInstanceOf('\PHPAnt\Core\PHPAntSignerFile', $file);
		}

		$manifestFilePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.xml';
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
		$manifestFilePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.xml';

		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
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
		$manifestFilePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.xml';

		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
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

		$publicKeyPath         = 'tests/PHPAnt/Core/resources/Apps/TestApp/public.key';
		$manifestFileSignature = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.sig';
		$privateKeyFailurePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/private.key';

		//Undo the file we made earlier.
		unlink($privateKeyFailurePath);
		$this->assertFalse(file_exists($privateKeyFailurePath));

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key";
		
		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
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
		$publicKeyPath         = 'tests/PHPAnt/Core/resources/Apps/TestApp/public.key';
		$privateKeyFailurePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/private.key';
		$manifestFileSignature = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.sig';

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key.wrong";
		
		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
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
		$publicKeyPath         = 'tests/PHPAnt/Core/resources/Apps/TestApp/public.key';
		$privateKeyFailurePath = 'tests/PHPAnt/Core/resources/Apps/TestApp/private.key';
		$manifestFileSignature = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.sig';

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key";
		
		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
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

		$publicKeyPath  = 'tests/PHPAnt/Core/resources/Apps/TestApp/public.key';
		$privateKeyPath = 'tests/PHPAnt/Core/resources/Apps/TestApp/private.key';
		$privateKeyStoragePath = "/home/$who/private.key";

		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$S->genKeys();
		copy($privateKeyPath,$privateKeyStoragePath);
		unlink($privateKeyPath);

		$S->signApp($privateKeyStoragePath);

		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('TestApp');
		$result = $S->verifySignature();		
		$this->assertTrue($result);
	}

	function testUpdatePublicKey() {
		$who = exec('whoami');

		$manifestPath   = 'tests/PHPAnt/Core/resources/Apps/TestApp/manifest.xml';

		$publicKeyPath  = 'tests/PHPAnt/Core/resources/Apps/TestApp/public.key';
		if(file_exists($publicKeyPath)) unlink($publicKeyPath);
		$this->assertFalse(file_exists($publicKeyPath));

		$privateKeyPath = 'tests/PHPAnt/Core/resources/Apps/TestApp/private.key';
		if(file_exists($privateKeyPath)) unlink($privateKeyPath);
		$this->assertFalse(file_exists($privateKeyPath));

		$privateKeyStoragePath = "/home/$who/private.key";
		$this->assertTrue(file_exists($privateKeyStoragePath));

		$options['appRoot'] = 'tests/PHPAnt/Core/resources/Apps/';
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

	function testDisableThisApp() {
		$AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);
        $result = $AE->disableApp('TestAntApp','tests/PHPAnt/Core/resources/Apps/TestApp/app.php');
        $this->assertTrue($result);
	}
}