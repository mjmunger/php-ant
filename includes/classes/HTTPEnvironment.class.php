<?php

namespace PHPAnt\Core;

class HTTPEnvironment {
	var $host                      = NULL;
	var $user_agent                = NULL;
	var $accept                    = NULL;
	var $accept_language           = NULL;
	var $accept_encoding           = NULL;
	var $cookie                    = NULL;
	var $connection                = NULL;
	var $upgrade_insecure_requests = NULL;
	var $cache_control             = NULL;

	function setup($server) {
		if(isset($server['HTTP_HOST']))                      $this->host                      = $server['HTTP_HOST'];
		if(isset($server['HTTP_USER_AGENT']))                $this->user_agent                = $server['HTTP_USER_AGENT'];
		if(isset($server['HTTP_ACCEPT']))                    $this->accept                    = $server['HTTP_ACCEPT'];
		if(isset($server['HTTP_ACCEPT_LANGUAGE']))           $this->accept_language           = $server['HTTP_ACCEPT_LANGUAGE'];
		if(isset($server['HTTP_ACCEPT_ENCODING']))           $this->accept_encoding           = $server['HTTP_ACCEPT_ENCODING'];
		if(isset($server['HTTP_COOKIE']))                    $this->cookie                    = $server['HTTP_COOKIE'];
		if(isset($server['HTTP_CONNECTION']))                $this->connection                = $server['HTTP_CONNECTION'];
		if(isset($server['HTTP_UPGRADE_INSECURE_REQUESTS'])) $this->upgrade_insecure_requests = $server['HTTP_UPGRADE_INSECURE_REQUESTS'];
		if(isset($server['HTTP_CACHE_CONTROL']))             $this->cache_control             = $server['HTTP_CACHE_CONTROL'];

	}
}