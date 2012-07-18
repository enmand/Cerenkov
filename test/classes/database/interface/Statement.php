<?php
/**
 * Statement.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2008
 * @package classes/database
 */
interface Statement
{
	public function bind($var, $value);
	public function execute();
}