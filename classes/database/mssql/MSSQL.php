<?php
/**
 * MSSQL.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/mssql
 */

class MSSQL implements Database
{
	private $db; // DB resource
	
	public function connect($dbname, $host, $user, $password, $port = NULL)
	{
		// Connect to MS SQL
		if ( is_null($port) )
			$this->db = mssql_connect($host, $user, $password);
		else
			$this->db = mssql_connect("$host:$port", $user, $password);
		if ( $this->db == FALSE )
			throw new MSException('Could not connect to MS SQL');
		
		// Select database
		if ( mssql_select_db($dbname, $this->db) == FALSE )
			throw new MSException('Could not select MS SQL database');
	}
	
	public function disconnect()
	{
		if ( mssql_close($this->db) == FALSE )
			throw new MSException('Could not close connection to MS SQL');
	}
	
	public function query($sql)
	{
		$result = mssql_query($sql);
		if ( $result == FALSE )
			throw new MSException('Could not issue query');
		else return new MSResult($result);
	}
	
	public function insert($table, array $assoc_array)
	{
		// The MSSQL API has no equivalent to PostgreSQL's pg_insert()
		// Make our own:
		$sql = "INSERT INTO $table(";
		$keys = array_keys($assoc_array);
		$sql .= implode(',',$keys);
		$sql .= ') VALUES(';
		foreach($assoc_array as $key => $value)
		{
			$sql .= "'" . $value . "',";
		}
		$sql = preg_replace('/,$/','', $sql); // Remove trailing comma
		$sql .= ')';
		try
		{
			$this->query($sql);
		}
		catch ( MSException $e)
		{
			// Catch exceptions thrown by query() and rethrow with a meaningful error
			throw new MSException('Could not insert values into table');
		}
	}
	
	public function update($table, array $data, array $condition)
	{
		// PHP's MSSQL library doesn't have an equivalent to pg_update,
		// so write our own...
		$sql = "UPDATE TABLE $table SET ";
		foreach($data as $key => $value)
		{
			$sql .= $key . "='" . $value . "',";
		}
		$sql = preg_replace('/,$/','',$sql); // Remove trailing comma
		
		$sql .= ' WHERE ';
		foreach($condition as $key => $value)
		{
			$sql .= $key . "='" . $value . "' AND ";
		}
		$sql = preg_replace('/ AND $/', '', $sql); // Remove trailing AND
		
		try
		{
			$this->query($sql);
		}
		catch ( MSException $e )
		{
			// Catch exceptions from query() and rethrow with meaningful error
			throw new MSException('Could not update table');
		}
	}
	
	public function delete($table, array $assoc_array)
	{
		$sql = "DELETE FROM $table WHERE ";
		foreach($assoc_array as $key => $value)
		{
			$sql .= $key . "='" . $value . "' AND ";
		}
		$sql = preg_replace('/ AND /', '', $sql); // Remove trailing AND
		
		try
		{
			$this->query($sql);
		}
		catch ( MSException $e )
		{
			throw new MSException('Could not delete from table');
		}
	}
}

?>