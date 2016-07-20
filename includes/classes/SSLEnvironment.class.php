<?php 

namespace PHPAnt\Core;

class SSLEnvironment
{
	var $HTTPS             = NULL;
	var $tls_sni           = NULL;
	var $server_s_dn_c     = NULL;
	var $server_s_dn_st    = NULL;
	var $server_s_dn_l     = NULL;
	var $server_s_dn_o     = NULL;
	var $server_s_dn_ou    = NULL;
	var $server_s_dn_cn    = NULL;
	var $server_s_dn_email = NULL;
	var $server_i_dn_cn    = NULL;
	var $server_i_dn_o     = NULL;
	var $server_i_dn_c     = NULL;
	var $server_i_dn_st    = NULL;
	var $server_i_dn_l     = NULL;
	var $server_i_dn_email = NULL;
	var $version_interface = NULL;
	var $version_library   = NULL;
	var $protocol          = NULL;
	var $secure_reneg      = NULL;
	var $compress_method   = NULL;
	var $cipher            = NULL;
	var $cipher_export     = NULL;
	var $cipher_usekeysize = NULL;
	var $cipher_algkeysize = NULL;
	var $client_verify     = NULL;
	var $server_m_version  = NULL;
	var $server_m_serial   = NULL;
	var $server_v_start    = NULL;
	var $server_v_end      = NULL;
	var $server_s_dn       = NULL;
	var $server_i_dn       = NULL;
	var $server_a_key      = NULL;
	var $server_a_sig      = NULL;
	var $session_id        = NULL;
	var $session_resumed   = NULL;

	function setup($server) {
		if(isset($server['HTTPS']))                  $this->HTTPS             = ($server['HTTPS'] == 'on'?true:false);
		if(isset($server['SSL_TLS_SNI']))            $this->tls_sni           = $server['SSL_TLS_SNI'];
		if(isset($server['SSL_SERVER_S_DN_C']))      $this->server_s_dn_c     = $server['SSL_SERVER_S_DN_C'];
		if(isset($server['SSL_SERVER_S_DN_ST']))     $this->server_s_dn_st    = $server['SSL_SERVER_S_DN_ST'];
		if(isset($server['SSL_SERVER_S_DN_L']))      $this->server_s_dn_l     = $server['SSL_SERVER_S_DN_L'];
		if(isset($server['SSL_SERVER_S_DN_O']))      $this->server_s_dn_o     = $server['SSL_SERVER_S_DN_O'];
		if(isset($server['SSL_SERVER_S_DN_OU']))     $this->server_s_dn_ou    = $server['SSL_SERVER_S_DN_OU'];
		if(isset($server['SSL_SERVER_S_DN_CN']))     $this->server_s_dn_cn    = $server['SSL_SERVER_S_DN_CN'];
		if(isset($server['SSL_SERVER_S_DN_Email']))  $this->server_s_dn_email = $server['SSL_SERVER_S_DN_Email'];
		if(isset($server['SSL_SERVER_I_DN_CN']))     $this->server_i_dn_cn    = $server['SSL_SERVER_I_DN_CN'];
		if(isset($server['SSL_SERVER_I_DN_O']))      $this->server_i_dn_o     = $server['SSL_SERVER_I_DN_O'];
		if(isset($server['SSL_SERVER_I_DN_C']))      $this->server_i_dn_c     = $server['SSL_SERVER_I_DN_C'];
		if(isset($server['SSL_SERVER_I_DN_ST']))     $this->server_i_dn_st    = $server['SSL_SERVER_I_DN_ST'];
		if(isset($server['SSL_SERVER_I_DN_L']))      $this->server_i_dn_l     = $server['SSL_SERVER_I_DN_L'];
		if(isset($server['SSL_SERVER_I_DN_Email']))  $this->server_i_dn_email = $server['SSL_SERVER_I_DN_Email'];
		if(isset($server['SSL_VERSION_INTERFACE']))  $this->version_interface = $server['SSL_VERSION_INTERFACE'];
		if(isset($server['SSL_VERSION_LIBRARY']))    $this->version_library   = $server['SSL_VERSION_LIBRARY'];
		if(isset($server['SSL_PROTOCOL']))           $this->protocol          = $server['SSL_PROTOCOL'];
		if(isset($server['SSL_SECURE_RENEG']))       $this->secure_reneg      = $server['SSL_SECURE_RENEG'];
		if(isset($server['SSL_COMPRESS_METHOD']))    $this->compress_method   = $server['SSL_COMPRESS_METHOD'];
		if(isset($server['SSL_CIPHER']))             $this->cipher            = $server['SSL_CIPHER'];
		if(isset($server['SSL_CIPHER_EXPORT']))      $this->cipher_export     = $server['SSL_CIPHER_EXPORT'];
		if(isset($server['SSL_CIPHER_USEKEYSIZE']))  $this->cipher_usekeysize = $server['SSL_CIPHER_USEKEYSIZE'];
		if(isset($server['SSL_CIPHER_ALGKEYSIZE']))  $this->cipher_algkeysize = $server['SSL_CIPHER_ALGKEYSIZE'];
		if(isset($server['SSL_CLIENT_VERIFY']))      $this->client_verify     = $server['SSL_CLIENT_VERIFY'];
		if(isset($server['SSL_SERVER_M_VERSION']))   $this->server_m_version  = $server['SSL_SERVER_M_VERSION'];
		if(isset($server['SSL_SERVER_M_SERIAL']))    $this->server_m_serial   = $server['SSL_SERVER_M_SERIAL'];
		if(isset($server['SSL_SERVER_V_START']))     $this->server_v_start    = $server['SSL_SERVER_V_START'];
		if(isset($server['SSL_SERVER_V_END']))       $this->server_v_end      = $server['SSL_SERVER_V_END'];
		if(isset($server['SSL_SERVER_S_DN']))        $this->server_s_dn       = $server['SSL_SERVER_S_DN'];
		if(isset($server['SSL_SERVER_I_DN']))        $this->server_i_dn       = $server['SSL_SERVER_I_DN'];
		if(isset($server['SSL_SERVER_A_KEY']))       $this->server_a_key      = $server['SSL_SERVER_A_KEY'];
		if(isset($server['SSL_SERVER_A_SIG']))       $this->server_a_sig      = $server['SSL_SERVER_A_SIG'];
		if(isset($server['SSL_SESSION_ID']))         $this->session_id        = $server['SSL_SESSION_ID'];
		if(isset($server['SSL_SESSION_RESUMED']))    $this->session_resumed   = $server['SSL_SESSION_RESUMED'];

	}
}
