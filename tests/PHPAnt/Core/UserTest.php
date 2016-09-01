<?php

use PHPUnit\Framework\TestCase;

include_once('tests/test_top.php');

class UsersTest extends TestCase {

	public function testCreateUser() {
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_email        = 'michael.munger@gmail.com';
		$U->users_first        = 'Michael';
		$U->users_last         = 'Munger';
		$U->users_setup        = 'N';
		$U->users_nonce        = md5(time());
		$U->users_token        = hash_hmac('crc32',time(),'095efed2');
		$U->users_active       = 'N';
		$U->users_last_login   = NULL;
		$U->users_mobile_token = NULL;
		$U->users_public_key   = NULL;
		$U->users_owner_id     = 0;
		$U->users_timezone     = NULL;
		$U->users_roles_id	   = 1;
		$U->createHash('password');
		$U->insert_me();

		$this->assertNotNull($U->users_id);
		$this->assertGreaterThan(0, $U->users_id);
		
		$data = [ 'email' => $U->users_email
				, 'uid'   => $U->users_id
				, 'nonce' => $U->users_nonce
				, 'token' => $U->users_token
				];

		return $data;
	}

	/**
	 * @covers Users::loadFromEmail
	 * @depends testCreateUser
	 */
	
	public function testLoadFromEmail($data)
	{
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_email = $data['email'];
		$this->assertTrue($U->loadFromEmail());
		$this->assertSame($data['uid'], $U->users_id);

		return $data;
	}

	/**
	 * @covers Users::loadFromActivation
	 * @depends testLoadFromEmail
	 */
	
	public function testLoadFromActivation($data)
	{
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_nonce = $data['nonce'];
		$this->assertTrue($U->loadFromActivation());
		$this->assertSame($data['uid'], $U->users_id);

		return $data;
	}
	
	/**
	 * @covers Users::authenticate
	 * @depends testLoadFromActivation
	 */
	
	public function testAuthenticate($data)
	{
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_email = $data['email'];
		
		$this->assertTrue($U->loadFromEmail());
		$this->assertTrue($U->authenticate('password'));
		$this->assertFalse($U->authenticate('passwordx'));

		return $data;
	}

	/**
	 * @covers Users:loadFromToken
	 * @depends testAuthenticate
	 **/

	public function testLoadFromToken($data) {
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_token = $data['token'];

		$this->assertTrue($U->loadFromToken());
		$this->assertSame($data['uid'], $U->users_id);
		
		return $data;
	}

	/**
	 * @depends testLoadFromToken
	 * @covers Users::getFullName
	 * @covers Users::getRole
	 */
	
	public function testMisc($data) {
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_token = $data['token'];

		$this->assertTrue($U->loadFromToken());
		$U->load();
		$this->assertSame('Michael Munger', $U->getFullName());
		$this->assertSame('System / Testing',$U->getRole());

		return $data;
	}

	/**
	 * @depends testMisc
	 */
	
	public function testDelete($data)
	{
		$pdo = gimmiePDO();
		$U = new \PHPAnt\Core\Users($pdo);
		$U->users_id = $data['uid'];
		$U->load_me();
		$U->commit_suicide();

		$stmt = $pdo->prepare("SELECT users_id FROM users WHERE ?");
		$stmt->execute[$data['uid']];
		$this->assertSame(0, $stmt->rowCount());
	}
	
	
	
}