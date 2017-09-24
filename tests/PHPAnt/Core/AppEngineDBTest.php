<?php

namespace PHPAnt\Core;
$dependencies = [ 'tests/test_top.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use \PDO;

class AppEngineDBTest extends TestCase
{

    use TestCaseTrait;
    
    private $conn       = NULL;
    static private $pdo = NULL;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }

    public function getDataSet() {
        $return = $this->createMySQLXMLDataSet( __DIR__ .'/AppEngineDBTest.xml');
        return $return;        
    }

	/**
	 * Test whether certain users can have access to the test app's various events.
	 * This function depends on the database having the proper permissions setup.
	 * 
	 * @dataProvider providerTestUserACL
	 **/

	public function testUserACL($users_id, $action, $expected) {
		
		$User = new Users(self::$pdo);
		$User->users_id = $users_id;
		$User->load_me();

		//1. Instantiate app engine
		//Get the configs by themselves.
		$C = getWebConfigs();

		//Set the URI
		$C->Server->Request->uri = $uri;

		//Get an instance of the AppEngine
		$appRoot = $C->document_root . 'includes/apps/';


		$options = getDefaultOptions();
		$BL = new AppBlacklist();
		$options['Blacklist'] = $BL;
		$options['appRoot'] = $appRoot;
		$A = getMyAppEngine($options);	
		$A->Configs = $C;
		$A->Configs->Server->Request->uri = $uri;

		//Check to make sure the actions are registered for the Test app.
		$foundTheApp = false;
		foreach($A->apps as $App) {
			if($App->appName == 'Test Ant App') {
			    $foundTheApp = true;
			    break;
            }
		}

		$this->assertTrue($foundTheApp, 'I was not able to find the app "Test Ant App"! It is not enabled in the test data set?');

		$this->assertTrue($App->hasACL);

		$this->assertSame($expected, $App->userCanExecute(self::$pdo, $action, $User));

	}

	public function providerTestUserACL() {

		return  [ [ 1 , 'non-existent-event' , true  ] //Admin user, has rights to everything.
				, [ 6 , 'cli-load-grammar'   , false ] //standard user, no rights defined, protected action
				, [ 6 , 'unprotected-action' , true  ] //standard user, no rights defined, unprotected action.
				, [ 2 , 'app-hook-test'      , false ] //CLI user, with defined rights for cli-load-grammar, but no others. Protected action.
				, [ 2 , 'uploader-uri-test'  , false ] //CLI user, with defined rights for cli-load-grammar, but no others. Protected action.
				, [ 2 , 'history-uri-test'   , true  ] //CLI user, with defined rights for cli-load-grammar, but no others. unprotected action.
				, [ 2 , 'testasdf-uri-test'  , true  ] //CLI user, with defined rights for cli-load-grammar, but no others, unprotected action.
				, [ 2 , 'cli-load-grammar'   , true  ] //CLI user, with defined rights for cli-load-grammar, but no others.
				, [ 4 , 'app-hook-test'      , true  ] //Test user group, with defined rights for everything except cli-load-grammar.
				, [ 4 , 'uploader-uri-test'  , true  ] //Test user group, with defined rights for everything except cli-load-grammar.
				, [ 4 , 'history-uri-test'   , true  ] //Test user group, with defined rights for everything except cli-load-grammar.
				, [ 4 , 'testasdf-uri-test'  , true  ] //Test user group, with defined rights for everything except cli-load-grammar.
				, [ 4 , 'cli-load-grammar'   , false ] //Test user group, with defined rights for everything except cli-load-grammar.
				];
	}

	/**
	 * Adds user permissions for a group to the ACL.
	 * @dataProvider providerTestAddRolePermissions
	 **/

	public function testAddRolePermissions($rolesId, $event, $appName, $testUserId) {
		$User = new Users(self::$pdo);
		$User->users_id = $testUserId;
		$User->load_me();

		$ACL = new ACL(self::$pdo, $event);

		//1. Check to make sure the user does not yet have permissions.
		$this->assertFalse($ACL->userCanExecute($testUserId));

		//2. Give the user permissions.
		$this->assertTrue($ACL->addPermission($rolesId));

		//3. Check to make sure they now have permissions.
		$this->assertTrue($ACL->userCanExecute($testUserId));
	}

	public function providerTestAddRolePermissions() {
				   //Roles ID //event          // App Name     //Test user 
		return  [ [5        , 'app-hook-test'  ,'Test Ant App' , 5         ]
				];
	}

	/**
	 * Removes user permissions for a group from the ACL.
	 * @dataProvider providerTestRemoveRolePermissions
	 **/

	public function testRemoveRolePermissions($rolesId, $event, $appName, $testUserId) {
		$User = new Users(self::$pdo);
		$User->users_id = $testUserId;
		$User->load_me();

		$ACL = new ACL(self::$pdo, $event);

		//1. Check to make sure the user already has permissions.
		$this->assertTrue($ACL->userCanExecute($testUserId));

		//2. Remove permissions from the role.
		$this->assertTrue($ACL->removePermission($rolesId));

		//3. Check to make sure a sample user that belongs to that group no longer has permissions.
		$this->assertFalse($ACL->userCanExecute($testUserId));
	}

	public function providerTestRemoveRolePermissions() {
				   //Roles ID //event               // App Name     //Test user 
		return  [ [3          , 'cli-load-grammar'  ,'Test Ant App' , 2        ]
				];
	}

}