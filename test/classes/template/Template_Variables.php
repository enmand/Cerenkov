<?php
/**
 * Template_Variables.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/template
 */

class Template_Variables extends Template
{
	protected $temp;
	protected $tplContents;
	
	public function __construct(Template $tob)
	{
		$this->temp = $tob;
		$this->tplContents = &$this->temp->tplContents;
		$this->builtIn();
	}
	
	public function builtIn()
	{
		// Date options
		if(preg_match_all('/\<\$template\.time\((.+)?\)\>/iU', $this->tplContents, $matches))
		{
			if(empty($matches[1][0]))
			{
				$this->tplContents = preg_replace('/\<\$template\.time\(\"?(.+)?\"?\)\>/iU', date("h:i:s a"), $this->tplContents);
			}
			else
			{
				$this->tplContents = preg_replace('/\<\$template\.time\((.+)?\)\>/iU', date($matches[1][0]), $this->tplContents);
			}
		}
		
		// Template Name
		if(preg_match_all('/\<\$template\.name\>/iU', $this->tplContents, $matches))
		{
			$this->tplContents = preg_replace('/\<\$template\.name\>/iU', $this->temp->tplName, $this->tplContents);
		}
		
		// Template Path
		global $Config;
		if(preg_match_all('/\<\$template\.path\>/iU', $this->tplContents, $matches))
		{
			$t_path = explode("/", $GLOBALS['TEMPLATE_PATH']);
			$c_path = explode("/", $GLOBALS['ROOT_PATH']);
			$diff = array_merge(array_diff($t_path, $c_path), array());
			$ct_path = $Config->get("cerenkov.path");
			foreach($diff as $key => $value)
				$ct_path .= $value . "/";
			$this->tplContents = preg_replace('/\<\$template\.path\>/iU', $ct_path . $this->temp->tplName, $this->tplContents);
		}

		if(preg_match_all('/\<\$template\.www\>/iU', $this->tplContents, $matches))
		{
			$this->tplContents = preg_replace('/\<\$template\.www\>/iU', $GLOBALS['WWW'], $this->tplContents);
		}

		// Defined Constant
		if(preg_match_all('/\<\$template\.const\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(defined($matches[1][0]))
			{
				$this->tplContents = preg_replace('/\<\$template\.const\.(.+)\>/iU', constant($matches[1][0]), $this->tplContents);
			}
		}
		
		// _GET, _POST, _SERVER, _SESSION, _COOKIE, _REQUEST
		if(preg_match_all('/\<\$template\.get\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(isset($_GET[$matches[1][0]]))
			{
				$this->tplContents = preg_replace('/\<\$template\.get\.(.+)\>/iU', $_GET[$matches[1][0]], $this->tplContents);
			}
		}
		
		if(preg_match_all('/\<\$template\.post\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(isset($_POST[$matches[1][0]]))
			{
				$this->tplContents = preg_replace('/\<\$template\.post\.(.+)\>/iU', $_POST[$matches[1][0]], $this->tplContents);
			}
		}
		
		if(preg_match_all('/\<\$template\.server\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(isset($_SERVER[strtoupper($matches[1][0])]))
			{
				$this->tplContents = preg_replace('/\<\$template\.server\.(.+)\>/iU', $_SERVER[strtoupper($matches[1][0])], $this->tplContents);
			}
		}
		
		if(preg_match_all('/\<\$template\.session\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(isset($_SESSION[$matches[1][0]]))
			{
				$this->tplContents = preg_replace('/\<\$template\.session\.(.+)\>/iU', $_SESSION[$matches[1][0]], $this->tplContents);
			}
		}
		
		if(preg_match_all('/\<\$template\.cookie\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(isset($_COOKIE[$matches[1][0]]))
			{
				$this->tplContents = preg_replace('/\<\$template\.cookie\.(.+)\>/iU', $_COOKIE[$matches[1][0]], $this->tplContents);
			}
		}
		
		if(preg_match_all('/\<\$template\.request\.(.+)\>/iU', $this->tplContents, $matches))
		{
			if(isset($_REQUEST[$matches[1][0]]))
			{
				$this->tplContents = preg_replace('/\<\$template\.request\.(.+)\>/iU', $_REQUEST[$matches[1][0]], $this->tplContents);
			}
		}
	}
}
?>
