<?php

namespace PHPAnt\Core;

class ConfigWeb extends ConfigBase
{
	public $Server    				= NULL;

	function __construct(\PDO $pdo,$vars) {
		
		$this->http_host     = $vars['http_host']; //$_SERVER['HTTP_HOST'];
		$this->document_root = $vars['document_root']; //$_SERVER['DOCUMENT_ROOT'];
		$this->environment   = ConfigWeb::WEB;
		$this->pdo           = $pdo;
	}

	function getSpecialValues() {
		$specialValues = [ '%SERVER%' => $this->getHostFQDN()
                         , '%THISYEAR%' => date('Y')
                         ];
        return $specialValues;
	}


	/**
	 * Prints an HTML + Boostrap alert div on the page. Typically used for uniform alerts within a system.
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
        switch($type) {
            case 'error':
                printf('<div class="alert alert-danger text-center">%s</div>',$msg);
                break;
            case 'success':
                printf('<div class="alert alert-success text-center">%s</div>',$msg);
                break;
            case 'warning':
                printf('<div class="alert alert-warning text-center">%s</div>',$msg);     
                break;
            default:
                printf('<div class="alert alert-%s text-center">%s</div>','info',$msg);
                break;
	    }
	}

	/**
	 * Gets the fully qualified URI to the current resource.
	 * Example:
	 *
	 * <code>
	 * $link = getMyURI();
	 * </code>
	 *
	 * @return string. URI to the current resource.
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	
	function getMyURI() {
	    $URI = $_SERVER['REQUEST_URI'];
	    //strip the leading /
	    $URI = substr($URI,1,strlen($URI));
	    return getHostFQDN() . $URI;
	}

	/**
	 * Prints out an array or variable in a nice format so we can see what's going on with it.
	 * @param string $msg The variable, object, or array that needs to be printed out.
	 * @param boolean $return If true, function returns the output as a string. If false, it prints it.
	 * @return string|null The output string, or nothing if it prints it directly.
	 **/
	
	function debug_print($msg, $return=false, $minimumVerbosity = 0) {

		if($this->verbosity < $minimumVerbosity) return false;
		
		$output = sprintf(PHP_EOL . "<!-- %s -->" . PHP_EOL ,print_r($msg,true));

		if($return) return $output;

		echo $output;
	}

	function checkWebVerbosity(PluginEngine $PE) {
		/*check to see if we have entered a debug / verbosity level, and if so, store it in a session variable. */
		if(isset($_GET['verbosity'])) {
		
		    $level = filter_var($_GET['verbosity'],FILTER_VALIDATE_INT);
		
		    if($level) {
		        $_SESSION['verbosity'] = $level;
		    } elseif($_GET['verbosity'] == 'off') {
		        unset($_SESSION['verbosity']);
		        $this->divAlert("Verbosity disabled.");
		    }
		}
		
		/* If the session variable has a verbosity level, use it so we can debug. */
		
		if(isset($_SESSION['verbosity'])) {
		    $level = filter_var($_SESSION['verbosity'],FILTER_VALIDATE_INT);
		    $PE->setVerbosity($level);
		}	
	}

	/**
	 * Gets the fully qualified domain name of the site including the protocol (http vs. https).
	 * Example:
	 *
	 * <code>
	 * $site = getHostFQDN();
	 * </code>
	 *
	 * @return string The fully qualified domain name of the host.
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	
	function getHostFQDN() {
	    //returns the FQDN of the host. Ex: https://www.thedomain.com/
	    $prefix = ($this->Server->SSL->HTTPS?'https://':'http://');
	    return $prefix . $this->Server->HTTP->host . '/';
	}	
}