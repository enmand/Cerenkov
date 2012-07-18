<?php
/**
 * Database.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database
 */

interface Database {
	public function connect($dbname, $host, $user, $password, $port = NULL);
	public function disconnect();
	public function query($sql);
	public function insert($table, array $assoc_array);
	public function update($table, array $data, array $condition);
	public function delete($table, array $assoc_array);
	public function prepare($sql);
}

?>
