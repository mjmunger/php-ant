<?php
/**
 * TODO: DOCBLOCK SUMMARY NEEDED FOR Acl
 */

 /**
 * TODO: DOCBLOCK FOR CHILD CLASS Acl
 *
 */ 

class Acl extends acls
{
	function loadByAclCombo($app,$feature,$role) {

		$query = "SELECT 
				      acls_id
				  FROM
				      timing.acls
				  WHERE
				      users_roles_id = ?
				          AND acls_app = ?
				          AND acls_feature = ?
				          LIMIT 1";

	    $stmt = $this->pdo->prepare($query);
	    $values = [$role,$app,$feature];

		$result = $stmt->execute($values);

		if($stmt->rowCount() > 0) {
			$row = $stmt->fetchObject();
	
			$this->acls_id = $row->acls_id;
			$this->load_me();

			return true;

		} else {

			return false;

		}
	}
}