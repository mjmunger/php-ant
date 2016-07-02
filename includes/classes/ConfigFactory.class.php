<?php 

namespace PHPAnt\Core;

class ConfigFactory
{
	static function getConfigs(\PDO $pdo, $vars) {
		switch (php_sapi_name()) {
			case 'cli':
				return new ConfigCLI($pdo,$vars);
				break;
			
			default:
				return new ConfigWeb($pdo);
				break;
		}

	}
}