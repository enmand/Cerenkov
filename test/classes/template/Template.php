<?php
/**
 * Template.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/template
 */

class Template
{
	protected $tplName = ''; // Name of the current template
	protected $tplContents = '';
	protected $tFuncs;
	protected $tMod;
	protected $sysvars;

	public function __construct($fname = NULL)
	{
		global $Config;
		$this->tplName = $Config->get("template.name");
		$tLoader = new Template_Loader($this);
		if(stristr($Config->get('template.type'), "file"))
		{
			try
			{
				if(!$fname)
				{
					$tLoader->fbuildContents($this->tplName);
				}else{
					$tLoader->fbuildContents($tLoader->fromFile($this->tplName, $fname));
				}
			}
			catch(TemplateException $tex)
			{
				echo $tex->fullDebug();
			}
		}
		else if(stristr($Config->get('template.type'), "database"))
		{
			try
			{
				if(!$fname)
				{
					$tLoader->fbuldContenets($this->tplName);
				}else{
					$tLoader->dbuildContents($tLoader->fromDB($this->tplName, $fname));
				}
			}
			catch(TemplateException $tex)
			{
				echo $tex->fullDebug();
			}
		}
		else
		{
			try
			{
				throw new TemplateException("Could not initalize Template object");
			}
			catch(TemplateException $tex)
			{
				echo $tex->fullDebug();
			}
		}
		$this->tidy = $Config->get('tidy.USE_TIDY');
		$this->tFuncs = new Template_Functions($this);
		$this->tMod = new Template_VariableModifier($this);
		$this->sysvars = new Template_Variables($this);
		$this->tMod->doMods();
	}

	public function tidy($on = TRUE)
	{
		$this->tidy = $on;
	}

	private function comment()
	{
		if(preg_match_all("/\/\*(.*)\*\\//is", $this->tplContents, $match, PREG_SET_ORDER) > 0)
		{
			$this->tplContents = preg_replace("/\/\*(.*)\*\\//is", "", $this->tplContents);
			$this->comment();
		}
	}

	public function parseTemplate()
	{
		global $Config;
		/*
		 * Start the output buffer, and (if enabled) clean up the xhtml with tidy.
		 */
		$this->comment();

		if(stristr($Config->get('tidy.USE_TIDY'), "true") && $this->tidy)
		{
			ob_start();
			echo $this->tplContents;
			$html_ob = ob_get_clean();
			$html = new tidy;
			$html->parseString($html_ob, build_path($GLOBALS['TEMPLATE_PATH'], $this->tplName, "tidy.cfg"), 'utf8');
			$html->cleanRepair();
		}
		else
		{
			ob_start();
			$html = $this->tplContents;
		}
		return $html;
	}
	
	public function getPath()
	{
		global $Config;
		$t_path = explode("/", $GLOBALS['TEMPLATE_PATH']);
		$c_path = explode("/", $GLOBALS['ROOT_PATH']);
		$ct_path = '';
		$diff = array_values(array_flip(array_diff_assoc($t_path, $c_path)));
		for($i = $diff[0]; $i < count($t_path) - 1; $i++)
		{
			$ct_path .= $t_path[$i].'/';
		}
		return build_path($ct_path, $this->tplName);
	}
	

	public function __call($name, $args)
	{
		return $this->tFuncs->$name($args);
	}
}

?>
