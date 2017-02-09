<?php

namespace PHPAnt\Core;


class WebRequest
{
	public $scheme                  = NULL;
	public $method                  = NULL;
	public $uri                     = NULL;
	public $time_float              = NULL;
	public $time                    = NULL;
	public $ip                      = NULL;
	public $port                    = NULL;
	public $script_name             = NULL;
	public $post_vars	            = [];
	public $get_vars	            = [];
	public $request_vars            = [];
	public $authenticityToken       = NULL;
	public $json                    = false;

	function setup($server) {
		if(isset($server['REQUEST_SCHEME']))     $this->scheme      = $server['REQUEST_SCHEME'];

		if(isset($server['REQUEST_METHOD']))     $this->method      = $server['REQUEST_METHOD'];

		if(isset($server['REQUEST_URI']))        $this->uri         = $server['REQUEST_URI'];

		if(isset($server['REQUEST_TIME_FLOAT'])) $this->time_float  = $server['REQUEST_TIME_FLOAT'];

		if(isset($server['REQUEST_TIME']))       $this->time        = $server['REQUEST_TIME'];

		if(isset($server['REMOTE_ADDR']))        $this->ip          = $server['REMOTE_ADDR'];

		if(isset($server['REMOTE_PORT']))        $this->port        = $server['REMOTE_PORT'];

		if(isset($server['SCRIPT_NAME']))        $this->script_name = $server['SCRIPT_NAME'];

		$this->generateAuthenticityToken();
	}

	function generateAuthenticityToken() {

		if(version_compare(phpversion(), '7.0.0','<')) {
			$seed = bin2hex(openssl_random_pseudo_bytes(64));
		} else {
			$seed = bin2hex(random_bytes(64));
		}

		//To make this better, we should generate this key value per-installation at setup.
		$this->authenticityToken = hash_hmac('sha256',$seed, '8EzvCGcys1c9');
		session_start();
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
}