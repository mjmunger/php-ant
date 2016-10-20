<?php
	$status = ['user' => exec('whoami')];
	echo json_encode($status);