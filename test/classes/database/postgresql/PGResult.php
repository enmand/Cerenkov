<?php
/**
 * PGResule.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/postgresql
 */

class PGResult implements Result
{
	private $result;

	public function __construct($result)
	{
		$this->result = $result;
	}

	public function getAllColumns($column = NULL)
	{
		$cols = NULL;
		if ( is_null($column) )
		{
			$cols = pg_fetch_all_columns($this->result);
		}
		else
		{
			$cols = pg_fetch_all_columns($this->result, $column);
		}
		if ( $cols == FALSE )
			throw new PGException('Could not fetch columns from result');
		else return $cols;
	}

	public function getRows()
	{
		$rows = pg_fetch_all($this->result);
		if ( $rows == FALSE )
			throw new PGException('Could not fetch all rows from result');
		else
			return $rows;
	}

	public function getRow(int $row = NULL)
	{
		$data = NULL;
		if ( is_null($row) )
			$data = pg_fetch_array($this->result);
		else
			$data = pg_fetch_array($this->result, $row);
		return $data;
	}

	public function getResult($column, $row = NULL)
	{
		$data = NULL;
		if ( is_string($column) )
			$column = $this->fieldNumber($column);
		if ( is_null($row) )
			$data = pg_fetch_result($this->result, $column);
		else
			$data = pg_fetch_result($this->result, $row, $column);
		if ( $data == FALSE )
			throw new PGException('Could not fetch result');
		else return $data;
	}

	public function fieldNumber($field)
	{
		$num = pg_field_num($this->result, $field);
		if ( $num == -1 )
			throw new PGException('Could not get field number from field name');
		else return $num;
	}

	public function fieldName($number)
	{
		$name = pg_field_name($this->result, $number);
		if ( $name == FALSE )
			throw new PGException('Could not get field name from field number');
		else return $name;
	}

	public function fieldIsNull($column, $row = NULL)
	{
		$ret = NULL;
		if ( is_null($row) )
			$ret = pg_field_is_null($this->result, $column);
		else
			$ret = pg_field_is_null($this->result, $column, $row);
		if ( $ret == FALSE )
			throw new PGException('Could not determine whether field was SQL NULL');
		else return $ret;
	}

	public function fieldType($field)
	{
		$type = pg_field_type($this->resource, $field);
		if ( $type == FALSE )
			throw new PGException('Could not determine field type');
		else return $type;
	}

	public function numFields()
	{
		$val = pg_num_fields($this->result);
		if ( $val == -1 )
			throw new PGException('Could not determine number of fields');
		else return $val;
	}

	public function numRows()
	{
		$val = pg_num_rows($this->result);
		if ( $val == -1 )
			throw new PGException('Could not determine number of rows');
		else return $val;
	}
}


?>