<?php

/* Basic system configurations parameters - Defined the CLI vars*/
$who = trim(exec('whoami'));

//$vars = ['http_host'     => 'https://www.yourserver.com/'
//        ,'document_root' => '/home/'.$who.'/www'
//        ];

$vars = ['http_host'     => '%HTTPHOST%'
        ,'document_root' => $rootDir
        ];