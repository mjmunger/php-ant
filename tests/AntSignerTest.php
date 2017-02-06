<?php

require_once('test_top.php');

use PHPUnit\Framework\TestCase;

class PHPAntSignerTest extends TestCase
{
	function getAE() {
		$options = getDefaultOptions();
		$AE      = getMyAppEngine($options);
		return $AE;		
	}

	function testGenKeys() {

		$options['AE'] = $this->getAE();

		$publicKeyPath  = $options['AE']->Configs->document_root . '/public.key';
		$privateKeyPath = $options['AE']->Configs->document_root . 'private.key';
		
		$files = [$publicKeyPath, $privateKeyPath];

		foreach($files as $file) {
			if(file_exists($file)) unlink($file);
			$this->assertFileNotExists($file);
		}

		$Signer = new \PHPAnt\Core\PHPAntSigner($options);
		$Signer->setApp('ant-app-test-app');
		$Signer->genKeys();

		//Store the key in the database so it can be used later.
		$options['AE']->Configs->setConfig('signing-key',$privateKeyPath);

		$this->assertFileExists($publicKeyPath);
		$this->assertFileExists($privateKeyPath);
	}

	function testSetApp() {
		$options['AE'] = $this->getAE();
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$result = $S->setApp('ant-app-test-app');
		$this->assertTrue($result);
		$this->assertSame('ant-app-test-app', $S->app);
	}

	/**
	 * @depends testSetApp
	 **/

	function testGenerateManifestFile() {
		$options['AE'] = $this->getAE();
		$manifestFilePath = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';
		if(file_exists($manifestFilePath)) unlink($manifestFilePath);

		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('ant-app-test-app');
		$options['privateKeyPath'] = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];
		$options['appName']        = 'Test Ant App';

		$return = $S->generateManifestFile($options);

		$this->assertFileExists($return);

		foreach($S->files as $file) {
			$this->assertInstanceOf('\PHPAnt\Core\PHPAntSignerFile', $file);
		}

		$manifestFilePath = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';
		$this->assertFileExists($manifestFilePath);

		$app = simplexml_load_file($manifestFilePath);

		$this->assertSame('TestAntApp' , (string) $app['name']);
		$this->assertSame('PHPAnt\Apps', (string) $app['namespace']);

		foreach($app->file as $f) {
			$this->assertFileExists( (string) $f->name);
		}

		foreach($app->file as $f) {
			$filePath = (string) $f->name;
			$this->assertSame(sha1_file($filePath), (string)$f->hash);
		}
	}

	/**
	 * @depends testGenerateManifestFile
	 **/

	function testRegisterHook() {
		$options['AE']    = $this->getAE();
		$hook             = 'cli-init';
		$function         = 'declareMySelf';
		$signature        = $hook.$function.'50';
		$manifestFilePath = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';

		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('ant-app-test-app');
		$options['privateKeyPath'] = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];
		$options['appName']        = 'Test Ant App';

		$return = $S->generateManifestFile($options);		
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
		$options['AE']    = $this->getAE();
		$hook             = 'cli-init';
		$function         = 'declareMySelf';
		$signature        = $hook.$function.'50';
		$manifestFilePath = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';

		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('ant-app-test-app');

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

	function testAddHook() {
		$options['AE']  = $this->getAE();
		$manifestPath   = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';
		$S = new \PHPAnt\Core\PHPAntSigner($options);

		$S->setApp('ant-app-test-app');
		$hook = 'app-hook-test';
		$callback = 'doAppHookTest';
		$priority = 50;
		$hash = sha1($hook.$callback.$priority);
		$signature = $S->registerHook($hook,$callback,$priority);

		$this->assertSame($hash, $signature);
		$app = simplexml_load_file($manifestPath);
		$this->assertEquals(1, count($app->action));
	}

	/**
	 * @depends testGenKeys
	 **/

	function testSignApp() {

		$options['AE']         = $this->getAE();

		$publicKeyPath         = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/public.key';
		$privateKeyPath        = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];
		$manifestFileSignature = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.sig';

		//Undo the file we made earlier.
		if(file_exists($privateKeyPath)) unlink($privateKeyPath);
		$this->assertFalse(file_exists($privateKeyPath));

		if(file_exists($manifestFileSignature)) unlink($manifestFileSignature);
		$this->assertFileNotExists($manifestFileSignature);

		$privateKeyPath = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];
		
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->genKeys();
		$S->setApp('ant-app-test-app');
		$this->assertFileExists($privateKeyPath);
		$S->signApp($privateKeyPath);

		$this->assertFileExists($manifestFileSignature);
	}
	 /**
	  * @depends testSignApp
	  **/

	function testPrivateKeyMissingException() {

		$options['AE'] = $this->getAE();

		$publicKeyPath         = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/public.key';
		$privateKeyFailurePath = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/private.key';
		$manifestFileSignature = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.sig';

		$who = exec('whoami');
		$privateKeyPath = "/home/$who/private.key.wrong";
		
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('ant-app-test-app');

		$this->assertFileExists($manifestFileSignature);

		$this->expectException('Exception');
		$S->signApp($privateKeyPath);
	}

	/**
	 * @depends testSignApp
	 **/

	function testPublishApp() {

		$options['AE']             = $this->getAE();
		$options['privateKeyPath'] = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];
		$options['appName']        = 'ant-app-test-app';
		
		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('ant-app-test-app');
		$S->cleanAppCredentials();
		$return = $S->publish($options);

		$this->assertSame(true, $return['verifyApp']['integrityOK']);

		foreach($S->files as $file) {
			$this->assertInstanceOf('\PHPAnt\Core\PHPAntSignerFile', $file);
		}

		$manifestFilePath = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';
		$this->assertFileExists($manifestFilePath);

		$app = simplexml_load_file($manifestFilePath);

		$this->assertSame('TestAntApp' , (string) $app['name']);
		$this->assertSame('PHPAnt\Apps', (string) $app['namespace']);

		foreach($app->file as $f) {
			$this->assertFileExists( (string) $f->name);
		}

		foreach($app->file as $f) {
			$filePath = (string) $f->name;
			$this->assertSame(sha1_file($filePath), (string)$f->hash);
		}
	}	
		
	/**
	 * @depends testSignApp
	 **/

	function testVerifyApp() {

		$options['AE'] = $this->getAE();

		$privateKeyPath = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];

		$S = new \PHPAnt\Core\PHPAntSigner($options);
		$S->setApp('ant-app-test-app');
		//$S->genKeys();

		$S->signApp($privateKeyPath);

		//$S = new \PHPAnt\Core\PHPAntSigner($options);
		//$S->setApp('ant-app-test-app');
		$result = $S->verifySignature();		
		$this->assertTrue($result);
	}

	function testUpdatePublicKey() {
		$options['AE'] = $this->getAE();
		$who = exec('whoami');

		$manifestPath   = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/manifest.xml';

		$publicKeyPath  = $options['AE']->Configs->getAppsDir() . 'ant-app-test-app/public.key';
		if(file_exists($publicKeyPath)) unlink($publicKeyPath);
		$this->assertFalse(file_exists($publicKeyPath));

		$privateKeyPath = $options['AE']->Configs->getConfigs(['signing-key'])['signing-key'];
		
		if(file_exists($privateKeyPath)) unlink($privateKeyPath);
		$this->assertFalse(file_exists($privateKeyPath));

		$privateKeyStoragePath = "/home/$who/private.key";
		$this->assertTrue(file_exists($privateKeyStoragePath));

		$S = new \PHPAnt\Core\PHPAntSigner($options);

		$S->setApp('ant-app-test-app');
		$S->updatePublicKey($publicKeyPath);

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

/*	function testDisableThisApp() {
		$AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);
        $result = $AE->disableApp('TestAntApp',$options['AE']->Configs->getAppsDir() . 'ant-app-test-app/app.php');
        $this->assertTrue($result);
	}*/
}