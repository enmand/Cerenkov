<?php
/**
 * mSQLStatement.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2008
 * @package classes/database/msql
 */

class mSQLStatement implements Statement
{
	private $prepared;
	
	public function __construct($sql)
	{
		$this->prepared = $sql;
		return $this;
	}
	
	public function bind($var, $value)
	{
		str_replace("?", $value, $this->prepared, $count = 1);
		if(stristr($this->prepared, "?"))
		{
			throw new mSQLException("Not all placeholders were filled. A mSQL->bind() is needed for each placeholder");
		}
		return true;
	}
	
	public function execute()
	{
		return $this->query($this->prepared);
	}
	
}