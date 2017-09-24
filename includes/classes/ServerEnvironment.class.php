<?php

namespace PHPAnt\Core;


class ServerEnvironment
{
	var $signature = NULL;
	var $software  = NULL;
	var $name      = NULL;
	var $addr      = NULL;
	var $port      = NULL;
	var $admin     = NULL;
	var $protocol  = NULL;

	//objects
	var $Execution = NULL;
	var $Request   = NULL;
	var $HTTP      = NULL;
	var $SSL       = NULL;

	function setup($server) {
		if(isset($server['SERVER_SIGNATURE'])) $this->signature = $server['SERVER_SIGNATURE'];
		if(isset($server['SERVER_SOFTWARE']))  $this->software  = $server['SERVER_SOFTWARE'];
		if(isset($server['SERVER_NAME']))      $this->name      = $server['SERVER_NAME'];
		if(isset($server['SERVER_ADDR']))      $this->addr      = $server['SERVER_ADDR'];
		if(isset($server['SERVER_PORT']))      $this->port      = $server['SERVER_PORT'];
		if(isset($server['SERVER_ADMIN']))     $this->admin     = $server['SERVER_ADMIN'];
		if(isset($server['SERVER_PROTOCOL']))  $this->protocol  = $server['SERVER_PROTOCOL'];

	}
}