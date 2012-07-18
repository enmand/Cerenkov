<?php
/**
 * Result.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database
 */

interface Result {
	// Return all rows in a particular column as array
	public function getAllColumns($column = NULL);
	
	// Return all result rows as an array
	public function getRows();
	
	// Return requested or next row as both associative and indexed array
	public function getRow($row = NULL);
	
	// Return result in row, column specified
	// If no row is specified, gets data in specified column for the next row.
	public function getResult($column, $row = NULL);
	
	// Return field number of named field
	public function fieldNumber($field);
	
	// Return field name of numbered field
	public function fieldName($number);
	
	// Returns 1 if the field specified is SQL NULL, 0 otherwise.
	public function fieldIsNull($column, $row = NULL);
	
	// Return the SQL data type of the specified field
	public function fieldType($field);
	
	// Return number of fields in the result
	public function numFields();
	
	// Return number of rows in the result
	public function numRows();
}



?>