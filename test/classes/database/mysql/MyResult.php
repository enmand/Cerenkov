<?php
/**
 * MyResult.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/mysql
 */

/********************************************************************
NOTE:
The MySQL interface does not provide as much advanced functionality
as the PostgreSQL interface. As such, certain operations have been
'disabled' (i.e. they throw exceptions when attempted). A future
implementation would convert this missing functionality to PHP and
would do so in such a way that where it is not required, speed of
operation is not affected (i.e. only building a result cache when
absolutely required).
********************************************************************/

class MyResult implements Result
{
	private $result;
	
	public function __construct($result)
	{
		$this->result = $result;
	}
	
	public function getRows()
	{
		$rows = array();
		while($row = $this->result->fetch_array(MYSQLI_BOTH))
		{
			$rows[] = $row;
		}
		return $rows;
	}
	
	public function getRow($row = NULL)
	{
		$data = NULL;
		if ( is_null($row) )
		{
			$data =  $this->result->fetch_array(MYSQLI_BOTH);
		}
		else
		{
			throw new MyException('MySQL cannot handle requesting a specific row. Perhaps you should try PostgreSQL.');
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
		return $data[$column];
		//return $row[$column];
	}
	
	public function fieldName($field)
	{
		$finfo = $this->result->fetch_field();
		$name = $finfo->name;
		if ( $name == FALSE )
			throw new MyException('Could not get field name');
		else 
			return $name;
	}
	
	public function fieldNumber($field)
	{
		throw new MyException("MySQL cannot handle retrieving field number by name from a result set. Query the database manually, or use PostgreSQL instead.");
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
		$finfo = $this->result->fetch_field();
		if($finfo->type == FALSE)
			throw new MyException('Could not get field type');
		else
			return $finfo->type;
	}
	
	public function numRows()
	{
		return $this->result->num_rows;
	}
	
	public function numFields()
	{
		return $this->result->field_count;
	}
}

?>
