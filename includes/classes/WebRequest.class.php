<?php

namespace PHPAnt\Core;


class WebRequest
{
	public $scheme                  = NULL;

	/**
	 * @var $method string Imported from $_SERVER['REQUEST_METHOD'] Which request method was used to access the page; i.e. 'GET', 'HEAD', 'POST', 'PUT'.
	 * */

	public $method                  = NULL;
	public $uri                     = NULL;
	public $time_float              = NULL;
	public $time                    = NULL;
	public $ip                      = NULL;
	public $port                    = NULL;
	public $script_name             = NULL;
	public $post_vars	            = [];
	public $get_vars	            = [];
	public $put_vars	            = [];
	public $put_raw 	            = [];
	public $request_vars            = [];
	public $authenticityToken       = NULL;
	public $json                    = false;
	public $json_error              = false;
	public $authenticityTokenValid  = false;

	function setup($server) {
		session_start();
		if(isset($server['REQUEST_SCHEME']))     $this->scheme      = $server['REQUEST_SCHEME'];

		if(isset($server['REQUEST_METHOD']))     $this->method      = $server['REQUEST_METHOD'];

		if(isset($server['REQUEST_URI']))        $this->uri         = $server['REQUEST_URI'];

		if(isset($server['REQUEST_TIME_FLOAT'])) $this->time_float  = $server['REQUEST_TIME_FLOAT'];

		if(isset($server['REQUEST_TIME']))       $this->time        = $server['REQUEST_TIME'];

		if(isset($server['REMOTE_ADDR']))        $this->ip          = $server['REMOTE_ADDR'];

		if(isset($server['REMOTE_PORT']))        $this->port        = $server['REMOTE_PORT'];

		if(isset($server['SCRIPT_NAME']))        $this->script_name = $server['SCRIPT_NAME'];

	}

	function generateAuthenticityToken() {

		if(version_compare(phpversion(), '7.0.0','<')) {
			$seed = bin2hex(openssl_random_pseudo_bytes(64));
		} else {
			$seed = bin2hex(random_bytes(64));
		}

		//To make this better, we should generate this key value per-installation at setup.
		$this->authenticityToken = hash_hmac('sha256',$seed, '8EzvCGcys1c9');
		$_SESSION['authenticityToken'] = $this->authenticityToken;
	}

	function setCookies($cookies) {
		$this->cookies = $cookies;
	}

	function parsePost($post) {
		foreach($post as $key => $value) {
			$value = $this->normalizeUTF($value);
			$this->post_vars[$key] = $value;
		}
	}

	function verifyAuthenticityToken() {

		//This breaks and short-circuits the CSRF protection, and MUST be fixed.
		//<removeme>
		$this->authenticityTokenValid = true;
		return true;
		//</removeme>

		//var_dump($_POST);
		//echo "<pre>"; var_dump($_SESSION);echo "</pre>"; 
		//die(__FILE__  . ':' . __LINE__ );

		//Bail out early if there is not a post var.
		if(!isset($this->post_vars['authenticityToken'])) return false;

		//If there is, decide if it is valid by comparing it to the session.
		$result = strcmp($_SESSION['authenticityToken'],$this->post_vars['authenticityToken']);

		$this->authenticityTokenValid = ( $result === 0 ? true : false );
	}

	function parseGet($get) {
		foreach($get as $key => $value) {
			//Convert UTF-8 TO Latin-1 if required.
			$value = $this->normalizeUTF($value);
			$this->get_vars[$key] = $value;
		}
	}

	function normalizeUTF($value) {
		if(is_array($value)) {
			//return array_map(['this','normalizeEncoding'], $value);
			return $value;
		} else {
			return $value;
			//return $this->normalizeEncoding($value);
		}
	}

	function mergeRequest() {
		$this->request_vars = [];
		$this->request_vars = array_merge($this->post_vars,$this->get_vars);
	}

	function importJSON($input) {
		if($this->method == 'GET') return false;

		$jsonString = trim(file_get_contents($input));

		try {
			$json = json_decode($jsonString);
		} catch (Exception $e) {
			return false;
		}

		if(json_last_error() != JSON_ERROR_NONE) return false;

		$this->json = $json;

		return true;
	}

	function parsePut($input,$headers) {
		if($this->method != 'PUT') return false;

		$buffer = trim(file_get_contents($input));

		$this->put_raw = $buffer;

		switch ($headers['Content-Type']) {
			case 'application/json':
				try {
					$json = json_decode($buffer);
				} catch (Exception $e) {
					return false;
				}

				if(json_last_error() == JSON_ERROR_NONE) {
					$this->json = $json;
					return true;
				} else {
					$this->json_error = get_json_error(json_last_error());
				}
				break;
			case 'application/x-www-form-urlencoded':
				try {
					$keypairs = explode("&", $buffer);

					$array = [];
					foreach($keypairs as $x) {
						$parts = explode("=", $x);
						$key = $parts[0];
						$value = $parts[1];
						$array[$key] = $value;
					}
					
					$this->put_vars = $array;
				
				} catch (Exception $e) {
					//pass
				}
				break;
			
			default:
				# code...
				break;
		}

	}
	function parsePatch($input,$headers) {

		if($this->method != 'PATCH') return false;

		$buffer = trim(file_get_contents($input));

		$this->patch_raw = $buffer;

		switch ($headers['Content-Type']) {
			case 'application/json':
				try {
					$json = json_decode($buffer);
				} catch (Exception $e) {
					return false;
				}

				if(json_last_error() == JSON_ERROR_NONE) {
					$this->json = $json;
					return true;
				} else {
					$this->json_error = get_json_error(json_last_error());
				}
				break;
			case 'application/x-www-form-urlencoded':
				try {
					$keypairs = explode("&", $buffer);

					$array = [];
					foreach($keypairs as $x) {
						$parts = explode("=", $x);
						$key = $parts[0];
						$value = $parts[1];
						$array[$key] = $value;
					}
					
					$this->put_vars = $array;
				
				} catch (Exception $e) {
					//pass
				}
				break;
			
			default:
				# code...
				break;
		}


	}
}