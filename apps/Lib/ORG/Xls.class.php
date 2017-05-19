<?php

/**
 * @file XLS Write Class Library - Version 0.1
 * @author Lourenzo Ferreira
 */

/**
* XLS Class
*/
class xlsDocument {
	var $doc;
	var $filename;
	
	/**
	 * The start of a XLS file
	 *  
	 * @return void
	 */
	function BOF() {
		$this->doc .= pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	}
	
	/**
	 * The end of a XLS file 
	 *
	 * @return void
	 */
	function EOF() {
		$this->doc .= pack("ss", 0x0A, 0x00);
	}
	
	/**
	 * Write a text label
	 *
	 * @param int $row
	 * @param int $col
	 * @param string $value
	 * 
	 * @return void
	 */
	function writeLabel($row, $col, $value ) { 
	    $l = strlen($value); 
	    $this->doc .= pack("ssssss", 0x204, 8 + $l, $row, $col, 0x0, $l); 
	    $this->doc .= $value; 
	}
	
	/**
	 * Write a Number
	 *
	 * @param int $row
	 * @param int $col
	 * @param float $value
	 * 
	 * @return void
	 */
	function writeNumber($row, $col, $value) { 
	    $this->doc .= pack("sssss", 0x203, 14, $row, $col, 0x0); 
	    $this->doc .= pack("d", $value);
	}
	
	/**
	 * Write a Row
	 *
	 * @param int $row
	 * @param array $values
	 * 
	 * @return void
	 */
	function writeRow($row, $values) {
		$col=0;
		foreach ($values as $value) {
			// @todo detect field type, default to text label
			$this->writeLabel($row,$col,utf8_decode($value));
			$col++;
		}
	}
	
	/**
	 * Write and send a XSL to download
	 *
	 * @param string $filename
	 * @param array $header
	 * @param array $lines
	 */
	function output($header, $lines) {
		
		
	  // BOF
		$this->BOF();
	
		// HEADER
		$this->writeRow(0, $header);
	
		// LINES
		$row = 1;
		foreach ($lines as $content) {
			$this->writeRow($row, $content);
			$row++;
		}
	
		// EOF
		$this->EOF();
	
		// HTTP HEADER
		header("Pragma: public");
	  header("Expires: 0");
	  header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	  header("Content-Type: application/force-download");
	  header("Content-Type: application/octet-stream");
	  header("Content-Type: application/download");;
	  header("Content-Disposition: attachment;filename={$this->filename}.xls ");
	  header("Content-Transfer-Encoding: binary ");
		echo $this->doc;
	
	}
	
	function save() {
		
	}
	
	
	function __construct($filename)
	{
		$this->filename = $filename;
	}
}
