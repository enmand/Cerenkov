<?php
/**
 * mSQL.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/msql
 */

class mSQL implements Database
{
	private $db;
	private $prepared;
	
	// Note: mSQL doesn't seem to accept a username or password so these are ignored
	public function connect($dbname, $host, $user, $password, $port = NULL)
	{
		// Connect to mSQL server
		if ( is_null($port) )
			$this->db = msql_connect($host);
		else
			$this->db = msql_connect("$host,$port");
		if ( $this->db == FALSE )
			throw new mSQLException('Could not connect to mSQL');
		
		// Select the database
		if ( msql_select_db($dbname, $this->db) == FALSE )
			throw new mSQLException('Could not select database');
	}
	
	public function disconnect()
	{
		if ( msql_close($this->db) == FALSE )
			throw new mSQLException('Could not close connection to mSQL');
	}
	
	public function query($sql)
	{
		$res = msql_query($sql);
		if ( $res == FALSE )
			throw new mSQLException('Could not issue query');
		else return new mSQLResult($res);
	}
	
	public function insert($table, array $assoc_array)
	{
		// PHP's mSQL library doesn't have an equivalent to the
		// pg_insert function for PostgreSQL. Make our own version instead...
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
		catch ( mSQLException $e)
		{
			// Catch exceptions thrown by query() and rethrow with a meaningful error
			throw new mSQLException('Could not insert values into table');
		}
	}
	
	public function update($table, array $data, array $condition)
	{
		// PHP's mSQL library doesn't have an equivalent to pg_update,
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
		catch ( mSQLException $e )
		{
			// Catch exceptions from query() and rethrow with meaningful error
			throw new mSQLException('Could not update table');
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
		catch ( mSQLException $e )
		{
			throw new mSQLException('Could not delete from table');
		}
	}
	
	public function prepare($sql)
	{
		/*
		 * mSQL does not support prepared statements, so we'll use
		 * pseudo-prepared statements
		 */
		return new mSQLStatement($sql);
	}

}

?>