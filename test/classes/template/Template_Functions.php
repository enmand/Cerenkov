<?php
/**
 * Template_Functions.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/template
 */

class Template_Functions extends Template
{
	/**
	*	Breifly, let me mention something about Template_Functions. All of the arguments for the functions will be arrays. Within the comments for the
	*	functions, more information will be given about what array index does what. None of these functions are actually called directly, but are called
	*	with __call() within Template. I'm open to suggestions on a better way to do this, but for now, this is how it'll be done.
	*/
	public $temp;
	public function __construct(Template $tob)
	{
		$this->temp = $tob;
	}
	/**
	 * replace() 
	 * 
	 * replace() is the world function that replaces certain patterns in the template itself. It takes one argument, which is an array set up like this:
	 * $replaceWith[0]: The name of what will be replaced. eg. {$name}
	 * $replaceWith[1]: What {$name} will be replaced by. Can also be a 1- or 2-D array.
	 *
	 * @access protected
	 * @return void
	 * @param array $replaceWith
	 **/
	protected function replace(array $replaceWith)
	{
		if(!is_array($replaceWith[1]))
		{
			if(count($replaceWith)!=2)
			{
				throw new TemplateException("Error in function replace(). Please check the number of arguments");
			}
			// Will match to {$$replaceWith[0]}
			if(preg_match_all('/\<\$'.$replaceWith[0].'+\>/i', $this->temp->tplContents, $matches))
			{
				// will replace {$$replaceWith[0]} by $replaceWith[1] (if not an array)
				$this->temp->tplContents = preg_replace('/\<\$'.$replaceWith[0].'+?\>/i', $replaceWith[1], $this->temp->tplContents);
			}
		} else {
			$this->replaceArray($this->temp->tplContents, $replaceWith);
		}
		$this->temp->sysvars->builtIn();
		$this->temp->tMod->doMods();
	}
	
	/**
	 * isMap() 
	 * 
	 * isMap() checks to see if the given array is a map (associative array).
	 *
	 * @access private
	 * @return bool
	 * @param array $array
	 **/
	private function isMap(array $array)
	{
		$m = 0;
		foreach($array as $i => $val)
		{
			if($i != $m)
				return true;
			$m++;
		}
		return false;
	}
	
	/**
	 * replaceArray() 
	 * 
	 * replaceArray() is what is called if $replaceWith[1] is an array. This will replace a normal array, as well as a map. However, it will only loop
	 * through the values is the array is not a map (why would you want to loop through a map?).
	 *
	 * @access private
	 * @return void
	 * @param string $loopedContent
	 * @param array $replaceWith
	 **/
	private function replaceArray($loopedContents, array $replaceWith)
	{
		$loopedContent = '';
		$map = $this->isMap($replaceWith[1]);
		$lasti = '';
		// Will match {$$replaceWith[0]|start}(contents){$$replaceWith[0]|end}
		if(preg_match_all('/\<\$'.$replaceWith[0].'+\|start\>(.+)*\<\$'.$replaceWith[0].'+\|end\>/isU', $this->temp->tplContents, $matches))
		{
			$loopedContent = $matches[0][0];
			foreach($replaceWith[1] as $i => $ival)
			{
				if(!$map && $lasti != $i) {
					$loopedContent .= $matches[0][0];
				}
				if(!is_array($replaceWith[1][$i]))
				{
					// Will match {$$replaceWith[0].mapname}
					while(stristr($loopedContent, '<$'.$replaceWith[0].'.'.$i.'>'))
					{
						$loopedContent = preg_replace('/\<\$'.$replaceWith[0].'+\.'.$i.'>/i', $ival, $loopedContent);
					}
					// Will match {$$replaceWith[0]}
					while(stristr($loopedContent, '<$'.$replaceWith[0].'>'))
					{
						$loopedContent = preg_replace('/\<\$'.$replaceWith[0].'+\>/i', $ival, $loopedContent);
					}
				} else if(is_array($ival))
				{
					foreach($replaceWith[1][$i] as $j => $jval)
					{
						// a1: Array 1 ($replaceWith[1])
						// a2: Array 2 ($replaceWith[1][$i])
						if(!$this->isMap($replaceWith[1][$i]) && $lasti != $i && !$this->isMap($map))
						{
							$loopedContent .= $matches[0][0];
						}
						if(is_array($jval))
						{
							throw new TemplateException("Replace in array(): Array to deep.");
						}
						// Will match {$$replaceWith[0].a1mapnam.a2mapname}
						while(stristr($loopedContent, '<$'.$replaceWith[0].'.'.$i.'.'.$j.'>'))
						{
							$loopedContent = preg_replace('/\<\$'.$replaceWith[0].'+\.'.$i.'\.'.$j.'\>/i', $replaceWith[1][$i][$j], $loopedContent);
						}
						// Will match {$$replaceWith[0].a2mapname}
						while(stristr($loopedContent, '<$'.$replaceWith[0].'.'.$j.'>'))
						{
							$loopedContent = preg_replace('/\<\$'.$replaceWith[0].'+\.'.$j.'\>/i', $jval, $loopedContent);
						}
						// Will match {$$replaceWith[0]}
						while(stristr($loopedContent, '<$'.$replaceWith[0].'?*>'))
						{
							$loopedContent = preg_replace('/\<\$'.$replaceWith[0].'+?\>/i', $jval, $loopedContent);
						}
						$lasti = $i;
					}
				}
			}
			// Will replace {$$replaceWith[0]|start}(contents){$$replaceWith|end}
			$loopedContent = preg_replace('/\<\$'.$replaceWith[0].'+\|(start|end)\>/isU', '', $loopedContent);
			if(empty($replaceWith[1]))
				$this->temp->tplContents = preg_replace('/\<\$'.$replaceWith[0].'+\|start\>(.+)*\<\$'.$replaceWith[0].'+\|end\>/isU', "", $this->temp->tplContents);
			else
				$this->temp->tplContents = preg_replace('/\<\$'.$replaceWith[0].'+\|start\>(.+)*\<\$'.$replaceWith[0].'+\|end\>/isU', $loopedContent, $this->temp->tplContents);
		}else{
			foreach($replaceWith[1] as $i => $val)
				if(stristr($this->temp->tplContents, '<$'.$replaceWith[0].'.'.$i.'>'))
					$this->temp->tplContents = preg_replace('/\<\$'.$replaceWith[0].'+\.'.$i.'>/i', $val, $this->temp->tplContents);
		}
	}

	public function buildHelper($name)
	{
		$helper = file_get_contents("cerenkov/templates/".$this->temp->tplName."/helpers/".$name[0].".html");
		return $helper;
	}

	public function __call($name, $args)
	{
		throw new TemplateException("Invalid mathod name");
	}
}
?>
