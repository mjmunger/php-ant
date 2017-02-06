<?php

namespace PHPAnt\Core;


class WebRequest
{
	var $scheme       = NULL;
	var $method       = NULL;
	var $uri          = NULL;
	var $time_float   = NULL;
	var $time         = NULL;
	var $ip           = NULL;
	var $port         = NULL;
	var $script_name  = NULL;
	var $post_vars	  = [];
	var $get_vars	  = [];
	var $request_vars = [];

	function setup($server) {
		if(isset($server['REQUEST_SCHEME']))     $this->scheme      = $server['REQUEST_SCHEME'];
		if(isset($server['REQUEST_METHOD']))     $this->method      = $server['REQUEST_METHOD'];
		if(isset($server['REQUEST_URI']))        $this->uri         = $server['REQUEST_URI'];
		if(isset($server['REQUEST_TIME_FLOAT'])) $this->time_float  = $server['REQUEST_TIME_FLOAT'];
		if(isset($server['REQUEST_TIME']))       $this->time        = $server['REQUEST_TIME'];
		if(isset($server['REMOTE_ADDR']))        $this->ip          = $server['REMOTE_ADDR'];
		if(isset($server['REMOTE_PORT']))        $this->port        = $server['REMOTE_PORT'];
		if(isset($server['SCRIPT_NAME']))        $this->script_name = $server['SCRIPT_NAME'];
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
		//if(mb_check_encoding($value,'UTF-8')) $value = mb_convert_encoding($value, "ISO-8859-1", mb_detect_encoding($value, "UTF-8, ISO-8859-1, ISO-8859-15", true));
		return $value;
	}

	function mergeRequest() {
		$this->request_vars = [];
		$this->request_vars = array_merge($this->post_vars,$this->get_vars);
	}
}