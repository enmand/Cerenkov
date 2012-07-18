<?php
/**
 * SQLiteResult.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/sqlite
 */

/********************************************************************
NOTE:
The SQLite interface does not provide as much advanced functionality
as the PostgreSQL interface. As such, certain operations have been
'disabled' (i.e. they throw exceptions when attempted). A future
implementation would convert this missing functionality to PHP and
would do so in such a way that where it is not required, speed of
operation is not affected (i.e. only building a result cache when
absolutely required).
********************************************************************/

class SQLiteResult implements Result
{
	private $result;

	public function __construct($result)
	{
		$this->result = $result;
	}

	public function getRows()
	{
		$rows = array();
		while($row = sqlite_fetch_array($this->result, SQLITE_BOTH))
			$rows[] = $row;
		return $rows;
	}

	public function getRow($row = NULL)
	{
		$data = NULL;
		if ( is_null($row) )
		{
			$data = sqlite_fetch_array($this->result, SQLITE_BOTH);
		}
		else
		{
			throw new MyException('SQLite cannot handle requesting a specific row. Perhaps you should try PostgreSQL.');
		}
		return $data;
	}

	public function getAllColumns($column = NULL)
	{
		$data = $this->getRows();
		$output = array();
		if ( is_null($column) )
			$column = 0;
		foreach($data as $row)
		{
			$output[] = $row[$column];
		}
		return $output;
	}

	public function getResult($column, $row = NULL)
	{
		$data = NULL;
			$data = $this->getRow($row);
		return $row[$column];
	}

	public function fieldName($field)
	{
		$name = sqlite_field_name($this->result, $field);
		if ( $name == FALSE )
			throw new SQLiteException('Could not get field name');
		else return $name;
	}

	public function fieldNumber($field)
	{
		throw new SQLiteException("SQLite cannot handle retrieving field number by name from a result set. Query the database manually, or use PostgreSQL instead.");
	}

	public function fieldIsNull($column, $row = NULL)
	{
		$data = $this->getResult($column, $row);
		if ( is_null($data) )
			return true;
		else return false;
	}

	public function fieldType($field)
	{
		throw new SQLiteException('SQLite does not support getting field type from a result set');
	}

	public function numRows()
	{
		return sqlite_num_rows($this->result);
	}

	public function numFields()
	{
		return mysql_num_fields($this->result);
	}
}

?>