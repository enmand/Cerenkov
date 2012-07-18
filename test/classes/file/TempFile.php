<?php

/**
 * TempFile.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/file
 */

class TempFile extends File
{
	private $persistent;
	
	public function __construct($persistent)
	{
		global $Config;
		$this->persistent = $persistent; // Whether to keep the file after the destructor is called
		$tmp_dir = $Config->get('file.temp_dir');
		$tmp_prefix = $Config->get('file.temp_prefix');
		$this->path = tempnam($tmp_dir, $tmp_prefix);
	}
	
	public function __destruct()
	{
		if ( ! $this->persistent )
		{
			$this->delete();
		}
	}
}

?>