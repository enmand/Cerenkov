<?php
/**
 * CSSTidy.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/css
 */


/**
 * CSSTidy
 * @package classes/css
 */
class CSSTidy
{
	private $css;
	private $tidied;

	public function __construct($css)
	{
		$this->css = $css;
		$this->tidied = false;
	}

	public function tidyCSS()
	{
		if ( ! $this->tidied )
		{
			// Add a space after colons
			$pattern = '/(\S):(\S)/';
			$replacement = '$1: $2';
			$this->css = preg_replace($pattern, $replacement, $this->css);

			// Add a space before '{' characters
			$this->css = preg_replace('/(\S)\{/', '$1 {', $this->css);
			$this->tidied = true;
		}
		return $this->css;
	}
}

?>
