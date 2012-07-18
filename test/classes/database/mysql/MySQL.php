<?php
/**
 * MySQL.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/mysql
 */

class MySQL implements Database
{
	protected $db; // MySQLi object
	
	public function __construct()
	{
		$this->db = new MySQLi();
	}
	
	public function connect($dbname, $host, $user, $password, $port = NULL)
	{
		// Connect to MySQL
		$this->db->connect($host, $user, $password, $dbname, $port);
		
		if (mysqli_connect_errno())
			throw new MyException("Could not connect to the MySQL database (" . mysqli_connect_error() . ")");
	}
	
	public function disconnect()
	{
		if (!$this->db->close())
			throw new MyException('Could not close MySQL connection');
	}
	
	public function query($sql)
	{
		$result = $this->db->query($sql);
		if ( $result == FALSE )
			throw new MyException('Error issuing query: '.$this->db->error);
		if ( is_bool($result) && !$result )
			return $result;
		else 
			return new MyResult($result);
	}
	
	public function insert($table, array $assoc_array)
	{
		// PHP's MySQL library doesn't have an equivalent to the
		// pg_insert function for PostgreSQL. Make our own version instead...
		$sql = "INSERT INTO $table(";
		$keys = array_keys($assoc_array);
		$sql .= implode(',',$keys);
		$sql .= ') VALUES(';
		foreach($assoc_array as $key => $value)
		{
			$sql .= "'" . IOSystem::DBEncode($value) . "',";
		}
		$sql = preg_replace('/,$/','', $sql); // Remove trailing comma
		$sql .= ')';
		try
		{
			$this->query($sql);
		}
		catch ( MyException $e)
		{
			// Catch exceptions thrown by query() and rethrow with a meaningful error
			throw new MyException('Could not insert values into table');
		}
	}
	
	public function update($table, array $data, array $condition)
	{
		// PHP's MySQL library doesn't have an equivalent to pg_update,
		// so write our own...
		$sql = "UPDATE $table SET ";
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
		catch ( MyException $e )
		{
			// Catch exceptions from query() and rethrow with meaningful error
			throw new MyException('Could not update table');
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
		catch ( MyException $e )
		{
			throw new MyException('Could not delete from table');
		}
	}
	
	public function prepare($sql)
	{}
	
	public function bind($var, $value)
	{}
	
	public function execute()
	{}
}

?>
