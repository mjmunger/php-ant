<?php

namespace PHPAnt\Setup;

use \PDO;
use \Exception;


class Installer {
    public $Configs = null;
    public $pdo     = null;
    public $baseDir = null;

    public function __construct($Configs) {
        $this->Configs = $Configs->configs;
        $this->baseDir = $Configs->baseDir;
    }

    public function install() {
        $success = true;

        $results = $this->createDatabase();
        $results = $this->importSkeleton();
        $results = $this->grantUserPermissions();
        $results = $this->createAdminUser();
        $results = $this->saveDatabaseCredentials();
        $results = $this->saveConfigs();
        $results = $this->getDefaultApps();
        $results = $this->installApps();

    }

    public function createDatabase() {
        $dsn = sprintf("mysql:host=%s;"
            , $this->Configs->db->server
        );

        $pdo = new PDO($dsn,$this->Configs->db->rootuser, $this->Configs->db->rootpass);

        $sql = "DROP DATABASE IF EXISTS " . $this->Configs->db->database;
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();
        if($result == false) throw new Exception(sprintf("Database creation error (%s) %s",$stmt->errorInfo()[1], $stmt->errorInfo()[2]));

        $sql = "CREATE DATABASE " . $this->Configs->db->database;

        $stmt = $pdo->prepare($sql);

        $result = $stmt->execute();

        if($result == false) throw new Exception(sprintf("Database creation error (%s) %s",$stmt->errorInfo()[1], $stmt->errorInfo()[2]));

        //Create a new PDO object we can continue to use.
        $dsn = sprintf("mysql:host=%s;dbname=%s"
            , $this->Configs->db->server
            , $this->Configs->db->database
        );

        $this->pdo = new PDO($dsn,$this->Configs->db->rootuser, $this->Configs->db->rootpass);

        $return = [];
        $return['success'] = true;

        return $return;
    }

    public function importSkeleton($skel = 'setup/skel.sql') {

        //Import the skeleton.
        $buffer = file_get_contents($skel);

        $statements = explode(';', $buffer);
        foreach($statements as $statement) {
            $statement = trim($statement);
            if(strlen($statement) == 0 ) continue;

            $stmt = $this->pdo->prepare($statement);
            if(!$stmt->execute()) {
                var_dump($stmt->errorInfo());
                return false;
            }
        }
    }

    public function grantUserPermissions() {
        $return = [];

        $sql = sprintf("GRANT ALL ON %s.* TO `%s` IDENTIFIED BY '%s'"
            ,$this->Configs->db->database
            ,$this->Configs->db->username
            ,$this->Configs->db->userpass
        );

        $stmt = $this->pdo->prepare($sql);

        if($stmt->execute() == false) {
            $return['success'] = false;
            $return['errorMessage'] = $stmt->errorInfo()[2];
            return $return;
        }

        $return['success'] = true;
        $return['errorMessage'] = 'No error';
        return $return;
    }
    
    public function createAdminUser() {
        $return = [];

        $sql = "INSERT INTO `users`
				(`users_email`,
				`users_password`,
				`users_first`,
				`users_last`,
				`users_roles_id`)
				VALUES
				( ?
				, ?
				, ?
				, ?
				, ?
				)";

        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare($sql);

        $vars = [ $this->Configs->adminuser->username
                , password_hash($this->Configs->adminuser->password, PASSWORD_DEFAULT)
                , $this->Configs->adminuser->first
                , $this->Configs->adminuser->last
                , 1
                ];

        $result = $stmt->execute($vars);
        $this->pdo->commit();

        if($result == false) {
            $return['success']      = false;
            $return['errorMessage'] = $stmt->errorInfo()[2];
        }

        $return['success']      = true;
        $return['errorMessage'] = '';

        return $return;
    }

    public function saveDatabaseCredentials() {

        $sampleConfig = 'setup/sample-mysql-credentials.php';
        $targetConfig = 'config/mysql-credentials.php';

        $fields = [ '%USER%'     => $this->Configs->db->username
                  , '%PASSWORD%' => $this->Configs->db->userpass
                  , '%DATABASE%' => $this->Configs->db->database
                  , '%HOST%'     => $this->Configs->db->server
        ];

        $buffer = file_get_contents($sampleConfig);

        foreach($fields as $field => $value) {
            $buffer = str_replace($field, $value, $buffer);
        }

        $fh = fopen($targetConfig,'w');
        fwrite($fh,$buffer);
        fclose($fh);

        $return = [];
        $return['success']      = file_exists($targetConfig);
        $return['errorMessage'] = '';

        return $return;
    }

    function createConfig($http_host, $document_root, $sampleConfig, $targetConfig) {

        //create web config.
        $buffer = file_get_contents($sampleConfig);
        $buffer = str_replace('%HTTPHOST%',$http_host,$buffer);
        $buffer = str_replace('%DOCUMENT_ROOT%',$document_root,$buffer);

        $fh = fopen($targetConfig,'w');
        fwrite($fh,$buffer);
        fclose($fh);
    }

    public function saveConfigs() {
        $success = true;

        $configs = [ 'setup/sample.config.php'     => 'config/config.php'
                   , 'setup/sample-cli-config.php' => 'config/cli-config.php'
        ];

        foreach($configs as $sample => $target) {
            $this->createConfig( $this->Configs->http_host
                               , $this->Configs->document_root
                               , $sample
                               , $target);

            if(file_exists($target) == false) return ['success' => false, "$target was not created"];
        }

        return ['success' => true, 'errorMessage' => '' ];

    }

    public function getDefaultApps() {

        $appsDir = $this->baseDir . '/includes/apps';

        printf("Checking for the existence of $appsDir");

        if(file_exists($appsDir) === false) mkdir($appsDir);
        chdir($appsDir);

        $defaultRepos = [ 'https://github.com/mjmunger/ant-app-test-app.git'
            , 'https://github.com/mjmunger/ant-app-default.git'
            , 'https://github.com/mjmunger/ant-app-configs.git'
            , 'https://github.com/mjmunger/phpant-app-manager.git'
        ];

        foreach($defaultRepos as $repo) {
            $cmd = sprintf("git clone %s",$repo);
            print "Running: $cmd" . PHP_EOL;
            passthru($cmd);
        }
    }

    function installApps() {

        $appsDir = $this->baseDir . '/includes/apps';

        printf("Apps directory: $appsDir" . PHP_EOL);
        
        if(file_exists($appsDir) === false) mkdir($appsDir);

        foreach($this->Configs->apps as $app) {
            chdir($appsDir);
            $cmd = sprintf('git clone %s',$app->remote);

            $buffer = explode('/', $app->remote);
            $git = end($buffer);
            $slug = str_replace('.git', '', $git);
            $appDir = $appsDir . '/' . $slug;

            system($cmd);

            if($app->hash == null) continue;

            chdir($appDir);

            $cmd = sprintf('git checkout %s',$app->hash);
            system($cmd);
        }
    }

}