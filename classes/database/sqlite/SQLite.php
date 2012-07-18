<?php
/**
 * SQLite.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/sqlite
 */

class SQLite implements Database
{
	private $db; // Resource
	
	// Host, user, password, port are ignored
	public function connect($dbname, $host, $user, $password, $port = NULL)
	{
		$this->db = sqlite_open($dbname);
		if ( $this->db == FALSE )
			throw new SQLiteException('Could not open SQLite database');
	}
	
	public function disconnect()
	{
		sqlite_close($this->db);
	}
	
	public function query($sql)
	{
		$result = sqlitel_query($sql, $this->db);
		if ( $result == FALSE )
			throw new SQLiteException('Error issuing query');
		if ( is_bool($result) )
			return $result;
		else return new SQLiteResult($result);
	}
	
	public function insert($table, array $assoc_array)
	{
		// PHP's SQLite library doesn't have an equivalent to the
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
		catch ( SQLiteException $e)
		{
			// Catch exceptions thrown by query() and rethrow with a meaningful error
			throw new SQLiteException('Could not insert values into table');
		}
	}
	
	public function update($table, array $data, array $condition)
	{
		// PHP's SQLite library doesn't have an equivalent to pg_update,
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
		catch ( SQLiteException $e )
		{
			// Catch exceptions from query() and rethrow with meaningful error
			throw new SQLiteException('Could not update table');
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
		catch ( SQLiteException $e )
		{
			throw new SQLiteException('Could not delete from table');
		}
	}
}

?>