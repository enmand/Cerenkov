<?php

/**
 * CerenkovException.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package system/exception
 */

/**
 * @package system/exception
 */
class CerenkovException extends Exception
{
	public function fullDebug($alt = "")
	{
		global $Config;
		$output = "<p><b>Exception:</b><p>\n";
		$output .= "<pre><p>Message: <br />\n" . $this->getMessage() . "</p>";
		$output .= "<p>In the file: " . $this->getFile() . " (" . $this->getLine() . "):</p>";
		#if(!empty($alt))
		#	$output .= "Stack trace: Unavailable (".$alt.")";
		#else
			$output .= "<p>Stack trace: <br />" . $this->getTraceAsString() ."):</p>";
		$output .= "<p>Please report this to your system administrator.</p></pre>";
		die($output);
	}
}

?>
