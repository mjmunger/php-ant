<?php


$hashColumns = ['Customer Number'    => 'tmp_gepaid_customernumber'
               ,'Site Number'        => 'tmp_gepaid_sitenumber'
               ];

//Allow this app to ONLY run when one of the following filters is present.
$requestFilters = ['GET'  => ['processorType'  => 'gepaid']
				  ,'POST'  => ['processorType' => 'gepaid']
				  ];

$configs = ['dropDownValue' => 'gepaid'
           ,'dropDownText'  => 'GE Paid'
           ,'hashColumns'   => $hashColumns
           ,'requestFilter' => [$requestFilters]
           ];


$fp = fopen('app.json','w');
fwrite($fp,json_encode($configs, JSON_PRETTY_PRINT));
fclose($fp);