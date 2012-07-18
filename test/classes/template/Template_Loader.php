<?php
/**
 * Template_Loader.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/template
 */

class Template_Loader extends Template
{
	protected $temp;
	public function __construct(Template $tob)
	{
		global $Config;
		$this->temp = $tob;
	}

	protected function fromFile($tplName, $fname)
	{
		$file = new File(build_path($GLOBALS['TEMPLATE_PATH'], $tplName, $fname));
		$dir = new File(build_path($GLOBALS['TEMPLATE_PATH'], $tplName));

		if(!$dir->isDir())
		{
			throw new TemplateException("There is no template named $tplName");
		}
		else if(!$file->exists())
		{
			throw new TemplateException("There is no file named $fname");
		}
		else
		{
			return $file;
		}
	}

	protected function fromDB($tplName, $fname)
	{
		$result = null;
		try
		{
			$result = $GLOBALS['DataBase']->query("SELECT `page_data` FROM `". $Config->get("sql.prefix") ."template_$tplName` WHERE `page_name` = \"$fname\"");
		}
		catch(DatabaseException $dbex)
		{
			throw new TemplateException("Could not get template from the database");
		}
		return $result;
	}

	protected function fbuildContents($resource)
	{
		if($resource->exists() && $resource->isReadable())
		{
			$resource->openFile('r');
			if($resource->isFile())
				$this->temp->tplContents = $resource->getContents($resource->getSize());
			$resource->closeFile();
		}
		else
		{
			throw new TemplateException("Could not build contents");
		}
	}

	protected function dbuildContents(Result $resource)
	{
		try
		{
			$this->temp->tplContents = $resource->getResult("page_data");
		}
		catch(DatabaseException $dbex)
		{
			echo $dbex->fullDebug();
		}
	}
}
?>