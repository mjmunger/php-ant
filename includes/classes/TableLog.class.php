<?php

namespace PHPAnt\Core;

/**
 * Represents a tabular log output for the CLI
 */

 /**
 *
 * This class is used to display tabular data in the CLI.
 *
 * @package      BFW
 * @subpackage   Core
 * @category     Plugins
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */     

Class TableLog
{
	var $headers    = NULL;
	var $rows       = [];
	var $columnSize = [];
	var $sortColumn = 0;
	var $tableWidth = 0;

	function __construct() {
		//TBD
	}

	function setHeader($headers) {
		$this->headers = $headers;
	}

	function addRow($row) {
		if(count($this->rows) > 0) {
			//Make sure that we are submitting a row with adequate / same columns.
			if(count($row) != count($this->headers)) throw new Exception('You rows must have the same number of columns as the header.');
		}
		array_push($this->rows, $row);
	}

	function sortRows() {

		$buffer = [];
		foreach($this->rows as $row) {
			$key = $row[$this->sortColumn];
			$buffer[$key] = $row;
		}
		ksort($buffer);
		$this->rows = $buffer;
	}

	/**
	 * Calculates the column size for each column based on the largest input from any row in that column. This is automatically called when you display the log output.
	 * Example:
	 *
	 * @return void
	 * @param void
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	function calculateColumnSize() {

		//Get the column count
		$columnCount = count($this->headers);
		$columnSize = [];

		for($c = 0; $c < $columnCount; $c++) {
			$max = 0;
			foreach($this->rows as $row) {
				if(strlen($row[$c]) > $max) $max = strlen($row[$c]);
			}
			$columnSize[$c] = $max + 1;
		}

		//Calculate the width
		$width = 0;
		foreach($columnSize as $c) {
			$width += $c;
		}
		$this->tableWidth = $width;
		$this->columnSize = $columnSize;
	}

	/**
	 * Shows the table directly in the CLI
	 *
	 * This is a helper function that first calls TableLog::makeTable(), and
	 * then displays it immediately in the CLI.
	 * Example:
	 *
	 * <code>
	 * $TL->showTable();
	 * </code>
	 *
	 * @return void
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/

	function showTable() {
		echo $this->makeTable();
	}

	/**
	 * Creates the table for the CLI
	 * Example:
	 *
	 * <code>
	 * $table = $TL->makeTable();
	 * </code>
	 *
	 * @return string The table ready to display in the CLI.
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	function makeTable() {
		$buffer = '';

		$this->sortRows();
		$this->calculateColumnSize();
		
		$buffer .= PHP_EOL;

		//Print the header
		$buffer .= str_pad('', $this->tableWidth,'-');
		$buffer .= PHP_EOL;
		
		for($c=0;$c<count($this->headers); $c++) {
			$buffer .= str_pad($this->headers[$c], $this->columnSize[$c]);
		}
		$buffer .= PHP_EOL;
		$buffer .= str_pad('', $this->tableWidth,'-');
		$buffer .= PHP_EOL;

		//Print all the lines.
		foreach($this->rows as $row) {
			//Print the columns for this line.
			for($c=0;$c<count($this->headers); $c++) {
				$buffer .= str_pad($row[$c], $this->columnSize[$c]);
			}
			//button up the line.
			$buffer .= PHP_EOL;
		}
		$buffer .= PHP_EOL;

		return $buffer;
	}
}