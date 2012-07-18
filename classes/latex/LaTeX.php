<?php
/**
 * LaTeX.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/latex
 */

/**
 * @package classes/latex
 */
class LaTeX
{
	private $preamble = '';

	public function __construct($preamble = NULL)
	{
		if ( is_null($preamble) )
		{
			$this->preamble = "\\documentclass[12pt]{article}\n";
			//$this->preamble .= "\\usepackage{amsmath,amssymb}\n"; // Comment this out if you don't have the AMS stuff
			$this->preamble .= "\\pagestyle{empty}\n";
		} else {
			$this->preamble = $preamble;
		}
	}

	private function generate_temp_name()
	{
		$time = time();
		$rand = mt_rand();
		return "phplatex$time$rand";
	}

	private function delete_directory($dirname)
	{
		if ( ! is_dir($dirname) )
			return false;
		$dscan = array(realpath($dirname));
		$darr = array();
		while ( !empty($dscan))
		{
			$dcur = array_pop($dscan);
			$darr[] = $dcur;
			if ( $d = opendir($dcur))
			{
				while($f = readdir($d))
				{
					if ( $f == '.' || $f == '..')
						continue;
					$f = $dcur . $GLOBALS['PATH_DELIM'] . $f;
					if ( is_dir($f) )
						$dscan[] = $f;
					else unlink($f);
				}
				closedir($d);
			}
		}
		for ( $i = count($darr)-1; $i >= 0; $i--)
		{
			rmdir($darr[$i]);
		}
		return true;
	}

	private function internal_render($tex)
	{
		// Assume $tex contains preamble, since only class members can call it

		/*
			Procedure:
			1. Create a temporary directory someplace
			2. Output the LaTeX as phplatex.tex
			3. Run latex php
			4. Run dvips -E phplatex.dvi
			5. Run convert phplatex.ps phplatex.png
			6. Move phplatex.png someplace else, renaming
			7. Clean up directory
			8. Return path of renamed phplatex.png
		*/

		// Create temporary directory
		$tmpname = $this->generate_temp_name();
		$delim = $GLOBALS['PATH_DELIM'];
		$path = sys_get_temp_dir() . $delim . $tmpname;
		$workingdir = getcwd();
		$PNGpath = NULL;
		try
		{
			if ( mkdir($path, 0700) == FALSE )
				throw new LaTeXException('Could not create temporary directory');
			// Create temporary file and output the LaTeX source
			$tmpname = 'phplatex';
			if ( file_put_contents($path . $delim . $tmpname . '.tex', $tex) == FALSE )
				throw new LaTeXException('Could not write LaTeX to file');

			// Change working directory
			if ( chdir($path) == FALSE )
				throw new LaTeXException('Could not change working directory');

			// Run latex
			exec('latex ' . $tmpname);
			// Check for existence of a .dvi
			if ( file_exists($path . $delim . $tmpname . '.dvi') == FALSE )
				throw new LaTeXException('Processing LaTeX failed');

			// Turn the dvi into PostScript
			exec('dvips -f -E -o ' . $tmpname . '.ps -q ' . $tmpname . '.dvi');
			// Check for existence of a .ps
			if ( file_exists($path . $delim . $tmpname . '.ps') == FALSE )
				throw new LaTeXException('Failed converting DVI to PostScript');

			// Convert PS to PNG using ImageMagick convert
			exec('convert -density 400x400 ' . $tmpname .'.ps ' . $tmpname . '.png');
			// Check for existence of a .png
			if ( file_exists($path . $delim . $tmpname . '.png') == FALSE )
				throw new LaTeXException('Failed converting PostScript to PNG');

			// Move the PNG to someplace safe
			$PNGname = $this->generate_temp_name();
			$PNGpath = sys_get_temp_dir() . $delim . $PNGname;
			if ( rename($path . $delim . $tmpname . '.png', $PNGpath) == FALSE)
				throw new LaTeXException('Could not move PNG');

			// Change CWD back to previous
			chdir($workingdir);

			// Remove the temp directory
			$this->delete_directory($path);

			// Return the PNG file path
			return $PNGpath;
		}
		catch (LaTeXException $e)
		{
			// Change back to working dir
			chdir($workingdir);

			// Remove the temp directory
			$this->delete_directory($path);

			// Rethrow the exception
			throw $e;
		}
	}

	public function render($tex)
	{
		$latex = $this->preamble . "\\begin{document}\n" . $tex . "\n\\end{document}\n";
		return $this->internal_render($latex);
	}

	public function renderMathInline($tex)
	{
		$latex = $this->preamble . "\\begin{document}\n";
		$latex .= '$' . $tex . "\$\n";
		$latex .= "\\end{document}\n";
		return $this->internal_render($latex);
	}

	public function renderMathDisplay($tex)
	{
		$latex = $this->preamble . "\\begin{document}\n";
		$latex .= '$$' . $tex . "\$\$\n";
		$latex .= "\\end{document}\n";
		return $this->internal_render($latex);
	}

	public function renderMathArray($tex)
	{
		$latex = $this->preamble . "\\begin{document}\n";
		$latex .= '\begin{eqnarray}' . $tex . "\\end{eqnarray}\n";
		$latex .= "\\end{document}\n";
		return $this->internal_render($latex);
	}
}

?>
