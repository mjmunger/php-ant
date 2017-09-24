<?php
use PHPUnit\Framework\TestCase;

class TableLogTest extends TestCase {


	function testSetHeader() {
		$headers = ['Mom','Dad','Daughter'];
		$TL = new \PHPAnt\Core\TableLog();
		$TL->setHeader($headers);
		$this->assertSame($headers, $TL->headers);
	}


	/**
	 * @depends testSetHeader
	 **/

	function testAddRow($row) {

		$headers = ['Mom','Dad','Daughter'];

		$rows = [ ['Erica'    , 'Michael' , 'Sloan'    ]
				, ['Erica'    , 'Michael' , 'Celine'   ]
				, ['Trinity'  , 'Melodi'  , 'Joslyn'   ]
				, ['Marisol'  , 'Carma'   , 'Allen'    ]
				, ['Paul'     , 'Beulah'  , 'Bart'     ]
				, ['Julia'    , 'Candice' , 'Frida'    ]
				, ['Russell'  , 'Cecilia' , 'Shalon'   ]
				, ['Lynwood'  , 'Tuyet'   , 'Cathey'   ]
				, ['Cherrie'  , 'Kayce'   , 'Tiffaney' ]
				, ['Dean'     , 'Dori'    , 'Jarvis'   ]
				, ['Gonzalo'  , 'Clement' , 'Margo'    ]
				, ['Angelyn'  , 'Lavern'  , 'Lucienne' ]
				, ['Noah'     , 'Refugio' , 'Angele'   ]
				, ['Maryland' , 'Joanne'  , 'Chante'   ]
				];

		$TL = new \PHPAnt\Core\TableLog();
		$TL->setHeader($headers);

		foreach($rows as $row) {
			$TL->addRow($row);
		}

		$this->assertCount(14, $TL->rows);
	}

	/**
	 * @covers TableLog::sortRows
	 */
	
	public function testSortRows()
	{
		$headers = ['Mom','Dad','Daughter'];

		$rows = [ ['Erica'    , 'Michael' , 'Sloan'    ]
				, ['Erica'    , 'Michael' , 'Celine'   ]
				, ['Trinity'  , 'Melodi'  , 'Joslyn'   ]
				, ['Marisol'  , 'Carma'   , 'Allen'    ]
				, ['Paul'     , 'Beulah'  , 'Bart'     ]
				, ['Julia'    , 'Candice' , 'Frida'    ]
				, ['Russell'  , 'Cecilia' , 'Shalon'   ]
				, ['Lynwood'  , 'Tuyet'   , 'Cathey'   ]
				, ['Cherrie'  , 'Kayce'   , 'Tiffaney' ]
				, ['Dean'     , 'Dori'    , 'Jarvis'   ]
				, ['Gonzalo'  , 'Clement' , 'Margo'    ]
				, ['Angelyn'  , 'Lavern'  , 'Lucienne' ]
				, ['Noah'     , 'Refugio' , 'Angele'   ]
				, ['Maryland' , 'Joanne'  , 'Chante'   ]
				];

		$TL = new \PHPAnt\Core\TableLog();
		$TL->setHeader($headers);

		foreach($rows as $row) {
			$TL->addRow($row);
		};

		$TL->sortColumn = 0; //default..

		$TL->sortRows();

        $sortA = [ 'Angelyn'
                  , 'Cherrie'
                  , 'Dean'
                  , 'Erica'
                  , 'Erica'
                  , 'Gonzalo'
                  , 'Julia'
                  , 'Lynwood'
                  , 'Marisol'
                  , 'Maryland'
                  , 'Noah'
                  , 'Paul'
                  , 'Russell'
                  , 'Trinity'
                  ];

        $counter = -1;
        foreach($TL->rows as $row) {
        	$counter++;
        	$this->assertSame($sortA[$counter], $row[0]);
        }


	}
	
}