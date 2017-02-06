<?php

/* Set the default date and timezone, For a list of supported timezones, see: http://php.net/manual/en/timezones.php */
date_default_timezone_set('America/New_York');

/* Basic system configurations parameters - Defined the CLI vars*/
$who = trim(exec('whoami'));

//$vars = ['http_host'     => 'https://www.yourserver.com/'
//        ,'document_root' => '/home/'.$who.'/www'
//        ];

$vars = ['http_host'     => '%HTTPHOST%'
        ,'document_root' => '%DOCUMENT_ROOT%'
        ];