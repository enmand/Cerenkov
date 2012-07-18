<?php
/**
 * PostgreSQL.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database/postgresql
 */

class PostgreSQL implements Database
{
	private $db; // Database connection resource

	// Connect to a database. Throws PGException if the connection failed.
	public function connect($dbname, $host, $user, $password, $port = NULL)
	{
		if ( is_null($port) )
		{
			$this->db = pg_connect("host=$host dbname=$dbname user=$user password=$password");
		}
		else
		{
			$this->db = pg_connect("host=$host dbname=$dbname user=$user password=$password port=$port");
		}

		if ( $this->db == FALSE )
			throw new PGException('Could not connect to database');
	}

	// Disconnect a database. Throws PGException if closing failed.
	public function disconnect()
	{
		if ( pg_close($this->db) == FALSE )
			throw new PGException('Could not close database connection');
	}

	// Issue a query. This returns a PGResult object or throws PGException on failure.
	public function query($sql)
	{
		$result = pg_query($this->db, $sql);
		if ( $result == FALSE )
			throw new PGException('Error issuing query');
		if ( is_bool($result) )
			return $result;
		else return new PGResult($result);
	}

	// Insert stuff into a database. Throws PGException on failure.
	// Keys of assoc_array are field names, values are values to insert in those fields.
	public function insert($table, array $assoc_array)
	{
		if ( pg_insert($this->db, $table, $assoc_array) == FALSE )
			throw new PGException('Could not insert data into table');
	}

	// Updates fields in a database. Throws PGException on failure.
	// Updates $table setting fields from keys of $data to values of $data where fields
	// in keys of $condition match values of $condition.
	public function update($table, array $data, array $condition)
	{
		if ( pg_update($this->db, $table, $data, $condition) == FALSE )
			throw new PGException('Could not update table');
	}

	// Delete data from a table. Throws PGException on failure.
	// Deletes rows where field (key of $assoc_array) matches value of $assoc_array.
	public function delete($table,array $assoc_array)
	{
		if ( pg_delete($this->db, $table, $assoc_array) == FALSE )
			throw new PGException('Could not delete from table');
	}
}


?>