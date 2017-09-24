<?php
use PHPUnit\Framework\TestCase;

class UsersRoleTest extends TestCase {

	public function testCreateUserRole() {
		$pdo = gimmiePDO();
		$UR = new \PHPAnt\Core\UsersRoles($pdo);
		$UR->users_roles_title = 'TestRole';
		$UR->users_roles_role  = 'X';
		$this->assertTrue($UR->insert_me());

		return $UR->users_roles_id;
	}

	/**
	 * @covers UsersRoles::generateAbbreviation
	 * @depends testCreateUserRole
	 */
	
	public function testGenerateAbbrev($id)
	{
		$pdo = gimmiePDO();
		$UR = new \PHPAnt\Core\UsersRoles($pdo);
		$UR->users_roles_id = $id;
		$UR->load_me();

		$this->assertSame('TestRole', $UR->users_roles_title);
		$this->assertSame('X', $UR->users_roles_role);

		$this->asserttrue($UR->commit_suicide());
	}
	
}