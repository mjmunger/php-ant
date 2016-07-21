<?php

namespace PHPAnt\Core;

class AppBlacklist
{
	var $blacklist     = [];
	var $blacklistPath = '.blacklist-load';
	var $disabled      = false;

	function __construct() {
		if(!file_exists($this->blacklistPath)) touch($this->blacklistPath);
		$this->load();
	}

	function load() {
		$this->blacklist = file($this->blacklistPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	function addToBlacklist($path) {
		if($this->disabled) return true;
		array_push($this->blacklist, $path);
		$this->save();
	}

	function isBlacklisted($path) {
		if($this->disabled) return true;
		return in_array($path, $this->blacklist);
	}

	function removeFromBlacklist($path) {
		$key = array_search($path, $this->blacklist);
		$this->unban($key);
	}

	function save() {
		$fp = fopen($this->blacklistPath,'w');
		fwrite($fp,implode(PHP_EOL, $this->blacklist));
		fclose($fp);
	}

	function clear() {
		$fp = fopen($this->blacklistPath,'w');
		ftruncate($fp,0);
		fclose($fp);
		$this->load();
	}

	function unban($key) {
		unset($this->blacklist[$key]);
		$this->save();		
	}
}