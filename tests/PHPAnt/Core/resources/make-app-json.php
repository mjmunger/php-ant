<?php


$hashColumns    = ['Customer Number'    => 'tmp_gepaid_customernumber'
                  ,'Site Number'        => 'tmp_gepaid_sitenumber'
                  ];

//Allow this app to ONLY run when one of the following filters is present.
$requestFilters = ['GET'   => ['processorType'  => 'gepaid']
				  ,'POST'  => ['processorType'  => 'gepaid']
				  ];

//Whitelist certain actions to ALWAYS run regardless of filters or URIs.
$alwaysRun      = ['supplier-types'];

$configs        = ['testProperty'       => 'gepaid'
                  ,'testPropertyArray'  => $hashColumns
                  ,'requestFilter'      => $requestFilters
                  ,'alwaysRun'	        => $alwaysRun
                  ];


$fp = fopen('app.json','w');
fwrite($fp,json_encode($configs, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));
fclose($fp);