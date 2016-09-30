<?php
use PHPUnit\Framework\TestCase;

require_once('test_top.php');

class AntConfigTest extends TestCase
{
    var $passed = false;

    public function testConfigsExist() {
        $exists = file_exists('includes/config.php');
        $this->assertTrue($exists,'The file config.php must exist in the root (www) dir.');
        return $exists;
    }

    /**
     * @depends testConfigsExist
     **/
         
    function testConfigVars() {

        $vars = getMockVars();

        $this->assertCount(3,$vars,'The $vars array must be included and defined so it can be included from config.php!');

        $this->assertInternalType('string',$vars['http_host'],'The http_host must be a defined string.');
        $this->assertInternalType('string',$vars['document_root'],'The document_root must be a defined string.');
        $this->assertStringStartsWith('http',$vars['http_host'],'http_host must be an FQDN starting with http');
    }


    function testConstructor() {
        $vars = getMockVars();
        $pdo = new PDOMock();

        $BC = new PHPAnt\Core\ConfigBase($pdo,$vars);

        //Make sure we have a pdo instance 
        $this->assertInstanceOf('pdo', $BC->pdo);

        //make sure we havea  URL in https_host
        $this->assertStringStartsWith('https://', $BC->http_host);

        //make sure we have a non-https URL when requested.
        $vars = getMockVars(false);
        $BC = new PHPAnt\Core\ConfigBase($pdo,$vars);
        $this->assertInstanceOf('PHPAnt\Core\ConfigBase', $BC);
        $this->assertStringStartsWith('http://', $BC->http_host);
    }

    /**
     * @depends testConstructor
     * @covers PHPAnt\Core\ConfigBase::getWebURI
     **/

    function testBaseConfig() {
        $vars = getMockVars();
        $pdo = new PDOMock();
        $this->assertFileExists($vars['document_root']);

        $BC = new PHPAnt\Core\ConfigBase($pdo,$vars);
        $BC->environment = PHPAnt\Core\ConfigBase::CLI;

        //Make sure we have a pdo instance 
        $this->assertInstanceOf('pdo', $BC->pdo);

        //Make sure the directories exist for each of the 'get' functions. 
        $dirs = ['Includes','Libs','Attachment','Images'];

        foreach($dirs as $dir) {
            $function = sprintf('get%sDir',$dir);
            $this->assertFileExists($BC->$function());
        }

        //Make sure getWebURI works

        $url = $BC->getWebURI($vars['document_root'] .'/includes/libs/library/resources/thing.png');
        $expectedURL = $vars['http_host'] . '/includes/libs/library/resources/thing.png';
        $this->assertSame($expectedURL, $url);
    }


    private function getTestKeyCount(\PDO $pdo) {
        $query = "SELECT COUNT(*) AS total FROM settings WHERE settings_key = ?";
        $values = ['test'];
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);

        if($stmt->rowCount() != 1) return false;

        $row = $stmt->fetchObject();
        return intval($row->total);
    }
    /**
     * @depends testBaseConfig
     * @covers PHPAnt\Core\ConfigBase::setConfig
     * @covers PHPAnt\Core\ConfigBase::delConfig
     * @covers PHPAnt\Core\ConfigBase::getConfigs
     */
    public function testGetSetConfigs()
    {
        $vars = getMockVars();
        //Use a live database connection.
        $pdo = gimmiePDO();

        $BC = new PHPAnt\Core\ConfigBase($pdo,$vars);
        $BC->environment = PHPAnt\Core\ConfigBase::CLI;

        $result = $BC->setConfig('test','success');
        $this->assertTrue($result);

        //Make sure that is REALLY in the database.
        $buffer = $BC->getConfigs(['test']);
        $this->assertSame('success', $buffer['test']);

        //Perform an update to this same key.
        $result = $BC->setConfig('test','success2');

        //Make sure the update happened.
        $buffer = $BC->getConfigs(['test']);
        $this->assertSame('success2', $buffer['test']);

        //Make sure there is only ONE 'test' key
        $this->assertSame(1, $this->getTestKeyCount($pdo));

        //Remove the config.
        $result = $BC->delConfig('test');
        $this->assertTrue($result);

        //Make sure it (and everything that is like it) is gone.
        $this->assertSame(0, $this->getTestKeyCount($pdo));
    }

    /**
     * @covers PHPAnt\Core\ConfigCLI::divAlert
     **/

    public function testConfigCLI() {
        $vars = getMockVars();
        $pdo = new PDOMock();

        $CC = new PHPAnt\Core\ConfigCLI($pdo,$vars);

        $msg = "The quick brown fox jumped over the lazy dog.";

        $outputString = sprintf(str_pad('', 80,'=') . PHP_EOL)
                      . sprintf("%s" . PHP_EOL,$msg)
                      . sprintf(str_pad('', 80,'=') . PHP_EOL);

        $this->expectOutputString($outputString);
        $CC->divAlert($msg,null);

    }

    public function testConfigWeb() {
        $vars = getMockVars();
        $pdo = new PDOMock();
        $CW = new PHPant\Core\ConfigWeb($pdo,$vars);

         //Make sure we have a pdo instance 
        $this->assertInstanceOf('pdo', $CW->pdo);

        //make sure we havea  URL in https_host
        $this->assertStringStartsWith('https://', $CW->http_host);

        //make sure we have a non-https URL when requested.
        $vars = getMockVars(false);
        $CW = new PHPAnt\Core\ConfigBase($pdo,$vars);
        $this->assertInstanceOf('PHPAnt\Core\ConfigBase', $CW);
        $this->assertStringStartsWith('http://', $CW->http_host);       

    }
}