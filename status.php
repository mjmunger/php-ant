<?php
	$status = [ 'user'        => exec('whoami')
			  , 'php_version' => phpversion()];
	echo json_encode($status);