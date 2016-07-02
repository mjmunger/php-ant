<?php 

namespace PHPAnt\Core;

class ConfigCLI extends ConfigBase
{
	function __construct(\PDO $pdo, $vars) {
		$this->pdo           = $pdo;
		$this->http_host     = $vars['http_host'];
		$this->document_root = $vars['document_root'];
		$this->environment   = ConfigCLI::CLI;
	}

	function getSpecialValues() {
		$specialValues = [ '%SERVER%' => $this->http_host
                         , '%THISYEAR%' => date('Y')
                         ];
        return $specialValues;
	}

	/**
	 * Prints console alert. Typically used for uniform alerts within a system.
	 * Example:
	 *
	 * <code>
	 * divAlert('There was an error','danger');
	 * </code>
	 *
	 * @return return value
	 * @param string $msg The message that should appear in the alert
	 * @param string $type The class used to decorate the alert. (success, info, warning, danger). See: http://getbootstrap.com/components/#alerts
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	
	function divAlert($msg,$type) {
		printf(str_pad('', 80,'=') . PHP_EOL);
        printf("%s" . PHP_EOL,$msg);
		printf(str_pad('', 80,'=') . PHP_EOL);
	}

	/**
	 * Prints out an array or variable in a nice format so we can see what's going on with it.
	 * @param string $msg The variable, object, or array that needs to be printed out.
	 * @param boolean $return If true, function returns the output as a string. If false, it prints it.
	 * @return string|null The output string, or nothing if it prints it directly.
	 **/
	
	function debug_print($msg, $return=false) {

		$output = (is_object($msg) || is_array($msg))?print_r($msg,true):$msg;

		if($return) {
			return $output;
		} else {
			echo $output;
		}
	}	
}