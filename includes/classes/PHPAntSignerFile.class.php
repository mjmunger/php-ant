<?php

namespace PHPAnt\Core;

/**
 * Represents a class of PHPAntSigner
 *
 * Generates (and verifies) PHP-Ant apps to ensure their legitimacy
 *
 * @package      PHP-Ant
 * @subpackage   Core
 * @category     Utilities
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 

class PHPAntSignerFile extends \SplFileInfo
{
	var $hash = NULL;

	function getHash() {
		$this->hash = sha1_file($this->getRealPath());
		return $this->hash;
	}
}