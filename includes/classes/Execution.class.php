<?php

namespace PHPAnt\Core;


class ScriptExecution
{

	var $path                  = NULL;
	var $document_root         = NULL;
	var $context_prefix        = NULL;
	var $context_document_root = NULL;
	var $script_filename       = NULL;
	var $gateway_interface     = NULL;
	var $query_string          = NULL;
	var $php_self              = NULL;

	function setup($server) {
		if(isset($server['PATH']))                  $this->PATH                  = $server['PATH'];
		if(isset($server['DOCUMENT_ROOT']))         $this->DOCUMENT_ROOT         = $server['DOCUMENT_ROOT'];
		if(isset($server['CONTEXT_PREFIX']))        $this->CONTEXT_PREFIX        = $server['CONTEXT_PREFIX'];
		if(isset($server['CONTEXT_DOCUMENT_ROOT'])) $this->CONTEXT_DOCUMENT_ROOT = $server['CONTEXT_DOCUMENT_ROOT'];
		if(isset($server['SCRIPT_FILENAME']))       $this->SCRIPT_FILENAME       = $server['SCRIPT_FILENAME'];
		if(isset($server['GATEWAY_INTERFACE']))     $this->GATEWAY_INTERFACE     = $server['GATEWAY_INTERFACE'];
		if(isset($server['QUERY_STRING']))          $this->QUERY_STRING          = $server['QUERY_STRING'];
		if(isset($server['PHP_SELF']))              $this->php_self              = $server['PHP_SELF'];
	}
}