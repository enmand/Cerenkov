<?php
/**
 * DBFactory.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/database
 */

class DBFactory
{
	const DB_MYSQL = 1;
	const DB_PGSQL = 2;
	const DB_MSSQL = 3;
	const DB_MSQL = 4;
	const DB_SQLITE = 5;
	
	public static function &create($type)
	{
		$db = NULL;
		try
		{
			switch($type)
			{
				case self::DB_MYSQL:
					$db = new MySQL();
					break;
				case self::DB_PGSQL:
					$db = new PostgreSQL();
					break;
				case self::DB_MSSQL:
					$db = new MSSQL();
					break;
				case self::DB_MSQL:
					$db = new mSQL();
					break;
				case self::DB_SQLITE:
					$db = new SQLite();
					break;
				default:
					break;
			}
		}
		catch(DatabaseException $dbex)
		{
			$dbex->fullDebug();
		}
		return $db;
	}
}

?>