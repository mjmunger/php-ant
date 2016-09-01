<?php

namespace PHPAnt\Core;

interface UserAuthenticator
{
	function getHash($unencryptedPassword);
	function validatePassword($unencryptedPassword);
	function getRole();
}