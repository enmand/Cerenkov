<?php
/**
 * Autoloader.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package system
 */

require_once('AutoloaderException.php');

/**
 * @package system
 */
class Autoloader
{
	// Associative array mapping filename => filepath
	private $filemap;
	
	// Return (recursive) list of files from starting directory, not including directories themselves
	// Array is associative with filename => filepath mapping
	private function dir2array($dir)
	{
		$items = array();
		if ( $handle = opendir($dir))
		{
			while ( false !== ($file = readdir($handle)) )
			{
				if ( $file != '.' && $file != '..' )
				{
					if ( is_dir($dir . '/' . $file) )
					{
						$items = array_merge($items, $this->dir2array($dir . '/' . $file));
					}
					else
					{
						$filepath = preg_replace('#//#si', '/', $dir . '/' . $file);
						$items[$file] = $filepath;
					}
				}
			}
			closedir($handle);
		}
		return $items;
	}
	
	// Construct an Autoloader object based on directory given by $path
	// If path is 'classes', base on built-in classes.
	public function __construct($path)
	{
		/*
		 * Search $path for .php files and build a map of filename => path
		 */
		if ( $path == 'classes')
		{
			$cache_path = build_path($GLOBALS['SYSTEM_PATH'], 'cache/', 'classes.dat');
			if ( file_exists($cache_path))
			{
				// Load array from cache/classes.dat
				$this->filemap = unserialize(file_get_contents($cache_path));
			}
			else
			{
				// Generate the array, serialize and store in cache/classes.dat
				$this->filemap = $this->dir2array($GLOBALS['CLASS_PATH']);
				file_put_contents($cache_path, serialize($this->filemap));
			}
		}
		else
		{
			// Plugin or other service requested us, blindly use the path provided (relative to root) and don't cache it
			$this->filemap = $this->dir2array(build_path($GLOBALS['ROOT_PATH'], $path));
		}
	}
	
	// Load a named class
	public function loadClass($class_name)
	{
		if ( array_key_exists($class_name.".php", $this->filemap) )
		{
			require($this->filemap[$class_name.".php"]);
		}
		else
			throw new AutoloaderException("Class $class_name not found");
	}
	
	// Return a list of keys in the class map...
	public function listClasses()
	{
		return array_keys($this->filemap);
	}

	public function forceReload($path) {
		if($path == 'classes') {
			$cache_path = build_path($GLOBALS['SYSTEM_PATH'], 'cache', 'classes.dat');
			// Generate the array, serialize and store in cache/classes.dat
			$this->filemap = $this->dir2array($GLOBALS['CLASS_PATH']);
			file_put_contents($cache_path, serialize($this->filemap));
		}
	}
}

?>
