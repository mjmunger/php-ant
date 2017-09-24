<?php

namespace PHPAnt\Setup;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

use \PDO;
use \Exception;

class CLISetupTest extends TestCase
{
//    use TestCaseTrait;
//
//    private $conn       = NULL;
//    static private $pdo = NULL;


//    final public function getConnection()
//    {
//        if ($this->conn === null) {
//            if (self::$pdo == null) {
//                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
//            }
//            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
//        }
//
//        return $this->conn;
//    }
//
//    public function getDataSet() {
//        $return = $this->createMySQLXMLDataSet( __DIR__ .'/AppEngineDBTest.xml');
//        return $return;
//    }

    public static function setUpBeforeClass()
    {
        $setupFiles = [ 'includes/mysql-credentials.php'
                      , 'includes/config.php'
                      , 'includes/cli-config.php'
                      ];

        foreach($setupFiles as $file) {
            if(file_exists($file)) unlink($file);
        }

        $dependencies = ['tests/test_top.php'
            , 'includes/AppEngine.php'
            , 'includes/classes/BaseSetupConfigs.class.php'
            , 'includes/classes/SetupConfigsFactory.class.php'
            , 'includes/classes/InteractiveConfigs.class.php'
            , 'includes/classes/JSONConfigs.class.php'
            , 'includes/classes/Installer.class.php'
        ];

        foreach ($dependencies as $d) {
            require_once($d);
        }
    }

    /**
     * @dataProvider getSetupConfigsFactoryProvider
     */
    public function testGetSetupConfigsFactory($isInteractive, $expected) {

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs($isInteractive);

        $this->assertInstanceOf($expected,$SetupConfigs);

    }

    public function getSetupConfigsFactoryProvider() {

        return  [ [true  , 'PHPAnt\Setup\InteractiveConfigs']
                , [false , 'PHPAnt\Setup\JSONConfigs']
                ];
    }

    public function testJsonConfigs() {

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs(false);

        //For interactive, this triggers the interactive configs.
        $SetupConfigs->loadConfig(__DIR__ . '/setup/settings.json');

        $this->assertSame(0,$SetupConfigs->jsonError);
        $this->assertSame("No error" , $SetupConfigs->jsonErrorMsg);

        $this->assertSame('localhost' , $SetupConfigs->configs->db->server);
        $this->assertSame('phpant'    , $SetupConfigs->configs->db->database);
        $this->assertSame('root'      , $SetupConfigs->configs->db->rootuser);
        $this->assertSame('password'  , $SetupConfigs->configs->db->rootpass);
        $this->assertSame('antuser'   , $SetupConfigs->configs->db->username);
        $this->assertSame('foopass'   , $SetupConfigs->configs->db->userpass);
        $this->assertSame(true        , $SetupConfigs->configs->db->createdb);

        $this->assertSame("Foo"               , $SetupConfigs->configs->adminuser->first);
        $this->assertSame("User"              , $SetupConfigs->configs->adminuser->last);
        $this->assertSame("admin@example.com" , $SetupConfigs->configs->adminuser->username);
        $this->assertSame("monkey1234"        , $SetupConfigs->configs->adminuser->password);
    }

    public function resetDB($pdo) {
        $sql = "DROP DATABASE IF EXISTS phpant";
        $stmt = $pdo->prepare($sql);
        $this->assertTrue($stmt->execute());
    }

    public function testCreateDatabase() {

        $pdo = new PDO( $GLOBALS['DB_DSN_NO_DB'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );

        $this->resetDB($pdo);

        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='phpant'";
        $stmt = $pdo->prepare($sql);

        $this->assertTrue($stmt->execute());

        $this->assertSame(0,$stmt->rowCount());

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs(false);
        $SetupConfigs->loadConfig(__DIR__ . '/setup/settings.json');

        $Installer = new Installer($SetupConfigs);

        $this->assertInstanceOf('PHPAnt\Setup\Installer', $Installer);

        $results = $Installer->createDatabase();

        $this->assertTrue($results['success']);

        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='phpant'";
        $stmt = $pdo->prepare($sql);

        $this->assertTrue($stmt->execute());

        $this->assertSame(1,$stmt->rowCount());

    }

    public function testImportSkel() {
        $skeletonPath = 'setup/skel.sql';

        $pdo = new PDO( $GLOBALS['DB_DSN_NO_DB'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );

        $this->resetDB($pdo);

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs(false);
        $SetupConfigs->loadConfig(__DIR__ . '/setup/settings.json');

        $Installer = new Installer($SetupConfigs);
        $Installer->createDatabase();

        $Installer->importSkeleton($skeletonPath);

        $sql = "SHOW TABLES FROM phpant";
        $stmt = $pdo->prepare($sql);
        $this->assertTrue($stmt->execute());

        $this->assertSame(9,$stmt->rowCount());
    }

    /**
     * @covers Installer::grantUserPermissions
     */

    public function testGrantPermissions() {
        $skeletonPath = 'setup/skel.sql';

        $pdo = new PDO( $GLOBALS['DB_DSN_NO_DB'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );

        $this->resetDB($pdo);

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs(false);
        $SetupConfigs->loadConfig(__DIR__ . '/setup/settings.json');

        $Installer = new Installer($SetupConfigs);
        $Installer->createDatabase();
        $Installer->importSkeleton($skeletonPath);

        $result = $Installer->grantUserPermissions();

        $this->assertTrue($result['success'],$result['errorMessage']);

        //Try to connect with those permissions.

        $dsn = sprintf("mysql:server=%s;dbname=%s"
            , $Installer->Configs->db->server
            , $Installer->Configs->db->database
        );

        $pdo = new PDO($dsn,$Installer->Configs->db->username, $Installer->Configs->db->userpass);

        $this->assertInstanceOf('PDO',$pdo);
    }

    public function testCreateAdmin() {
        $skeletonPath = 'setup/skel.sql';

        $pdo = new PDO( $GLOBALS['DB_DSN_NO_DB'] , $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );

        $this->resetDB($pdo);

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs(false);
        $SetupConfigs->loadConfig(__DIR__ . '/setup/settings.json');

        $Installer = new Installer($SetupConfigs);
        $Installer->createDatabase();
        $Installer->importSkeleton($skeletonPath);

        $result = $Installer->grantUserPermissions();
        $this->assertTrue($result['success'],$result['errorMessage']);

        $result = $Installer->createAdminUser();
        $this->assertTrue($result['success'],$result['errorMessage']);

        //confirm the user exists.
        $pdo = new PDO( $GLOBALS['DB_DSN'] , $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );

        $sql = "SELECT 
                    COUNT(*) AS theCount
                FROM
                    users
                WHERE
                    users_email = 'admin@example.com'
                        AND users_first = 'Foo'
                        AND users_last = 'User'
                        AND users_roles_id = 1
                        AND users_active = 'Y'";

        $stmt = $pdo->prepare($sql);
        $this->assertTrue($stmt->execute(), $stmt->errorInfo()[2]);

        $row = $stmt->fetchObject();

        $this->assertSame(1,(int) $row->theCount);
    }

    public function testSaveConfigs() {
        $credentialsFile = 'includes/mysql-credentials.php';
        $webConfigFile   = 'includes/config.php';
        $cliConfigFile   = 'includes/cli-config.php';

        $configFiles = [ $credentialsFile
                       , $webConfigFile
                       , $cliConfigFile
                       ];

        foreach($configFiles as $config) {
            if(file_exists($config) == true ) unlink($config);
            $this->assertFalse(file_exists($config));
        }

        $skeletonPath = 'setup/skel.sql';

        $pdo = new PDO( $GLOBALS['DB_DSN_NO_DB'] , $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );

        $this->resetDB($pdo);

        $SetupConfigs = SetupConfigsFactory::getSetupConfigs(false);
        $SetupConfigs->loadConfig(__DIR__ . '/setup/settings.json');

        $Installer = new Installer($SetupConfigs);
        $Installer->createDatabase();
        $Installer->importSkeleton($skeletonPath);

        $result = $Installer->grantUserPermissions();
        $this->assertTrue($result['success'],$result['errorMessage']);

        $result = $Installer->createAdminUser();
        $this->assertTrue($result['success'],$result['errorMessage']);

        $result = $Installer->saveDatabaseCredentials();

        $this->assertTrue($result['success'],$result['errorMessage']);
        $this->assertTrue(file_exists($credentialsFile));

        $result = $Installer->saveConfigs();
        $this->assertTrue($result['success'],$result['errorMessage']);
        $this->assertTrue(file_exists($webConfigFile), "$webConfigFile does not exist, and it should!");
        $this->assertTrue(file_exists($cliConfigFile), "$cliConfigFile does not exist, but it should!");
    }

}