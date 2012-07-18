<?php
/**
 * Template_VariableModifier.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/template
 */

class Template_VariableModifier extends Template
{
	private $temp;
	protected $tplContents;
	public function __construct(Template $tob)
	{
		$this->temp = $tob;
		$this->tplContents = &$this->temp->tplContents;
	}
	
	public function getFuncs()
	{
		return get_class_methods($this);
	}
	
	public function doMods()
	{
		$tModfuncs = $this->getFuncs();
		for($i=3;$i<count($tModfuncs) && !stristr($tModfuncs[$i], "parseTemplate");$i++)
		{
			$this->$tModfuncs[$i]();
		}
		$this->temp->sysvars->builtIn();
	}
	
	protected function uppercase()
	{
		if(preg_match_all('/\<(.+)\|upper\>/iU', $this->tplContents, $matches))
		{
			$this->tplContents = preg_replace('/\<(.+)\|upper\>/iU', strtoupper($matches[1][0]), $this->tplContents);
		}
	}
	
	protected function lowercase()
	{
		if(preg_match_all('/\<(.+)\|lower\>/iU', $this->tplContents, $matches))
		{
			$this->tplContents = preg_replace('/\<(.+)\|lower\>/iU', strtolower($matches[1][0]), $this->tplContents);
		}
	}
	
	protected function title()
	{
		if(preg_match_all('/\<(.+)\|title\>/iU', $this->tplContents, $matches))
		{
			$wordA = explode(" ", $matches[1][0]);
			foreach($wordA as $i => $ival)
			{
				$wordA[$i][0] = strtoupper($wordA[$i][0]);
			}
			$wordS = implode(" ", $wordA);
			$this->tplContents = preg_replace('/\<(.+)\|title\>/iU', $wordS, $this->tplContents);
		}
	}
	protected function count_chars()
	{
		if(preg_match_all('/\<(.+)\|count_chars\>/iU', $this->tplContents, $matches))
		{
			$this->tplContents = preg_replace('/\<(.+)\|count_chars\>/iU', strlen($matches[1][0]), $this->tplContents);
		}
	}
	
	protected function includePHP()
	{
		if(preg_match_all("/\<include_php=\"?(.+)(\.php|\.phtml|\.php(\d+))+\?\>/iU", $this->tplContents, $matches))
		{
			include($matches[1][0].$matches[2][0]);
			$this->tplContents = preg_replace("/\<include_php=(.+)(\.php|\.phtml|\.php(\d+))+\>/iU", "", $this->tplContents);
		}
	}
	
	protected function runPHP()
	{
		if(preg_match_all("/\<php\>((.+)*)\<\/php\>/isU", $this->tplContents, $matches))
		{
			$this->tplContents = preg_replace("/\<php\>((.+)*)\<\/php\>/isU", eval($matches[1][0]), $this->tplContents);
		}
	}
	
	protected function includeHTML()
	{
		if(preg_match_all("/\<include_html=\"?(.+)(\.html|\.tpl|\.htm)\"?\>/iU", $this->tplContents, $matches))
		{
				$this->tplContents = preg_replace("/\<include_html=\"?(.+)(\.html|\.tpl|\.htm)\"?\>/iU", file_get_contents($matches[1][0].$matches[2][0]), $this->tplContents);
				$this->doMods();
		}
	}
	
	// Work in progress. Not optimized, then again, logic in the templates is ill-advised.
	protected function conditional()
	{
		$matches = array();
		$nblocks = 0;
		$nconds = 0;
		$if = false;
		
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_QUIET_EVAL, 1);
		
		if(preg_match_all("/<if\((.+)\)(.+)\/if>/isU", $this->tplContents, $matches[0]) && preg_match_all("/\{(.+)\}/isU", $matches[0][2][0], $matches[1]))
		{
			if(assert($matches[0][1][0]))
			{
				$this->tplContents = preg_replace("/<if\((.+)\)(.+)\/if>/isU", $matches[1][1][0], $this->tplContents);
				$if = true;
			}
			if(preg_match_all("/if\((.+)\)/isU", $matches[0][2][0], $matches[2]) && !$if)
			{
				$nblocks = count($matches[1][0]);
				$nconds = count($matches[2][1]);
				for($i = 0; $i <= count($nconds); $i++)
				{
					if(!$if && assert($matches[2][1][$i]))
					{
						$this->tplContents = preg_replace("/<if\((.+)\)(.+)\/if>/isU", $matches[1][1][$i+1], $this->tplContents);
						$if = true;
					}
				}
			}
			if(!$if && ($nblocks-$nconds>1 || ($nblocks === 0 && $nconds === 0)))
			{
				$this->tplContents = preg_replace("/<if\((.+)\)(.+)\/if>/isU", $matches[1][1][$nconds+1], $this->tplContents);
			}
		}
	}
}

?>
