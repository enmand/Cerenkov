<?php
/**
 * Cache.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/cache
 */

class Cache
{
	private $cacheDir;
	private $cache;
	private $cacheTime;
	private $id = 0;
	/**
	*
	*	$opts array:
	*		$cacheDir => Where to store the cached files (string)
	*		$cache => Enable or disable caching of files (boolean)
	*		$cacheTime => How long should we store the file? in seconds (int)
	*
	*/
	public function __construct($params)
	{
		global $Config;
		$this->cacheDir = $Config->get("file.temp_dir");
		if($Config->get("cache.enable_cache") === "false")
			$this->cache = false;
		else
			$this->cache = true;
		$this->cacheTime = $Config->get("cache.time");
		if($this->cache)
		{
			if(isset($params))
				$idParams = $params;
			else
				$idParams = array(
						  'url' => $_SERVER['REQUEST_URI'],
						  'get' => $_GET,
						  'cookies' => $_SESSION);
			$this->id = $this->generateID($idParams);
		}
	}

	public static function generateID($idParams)
	{
		$id = md5(serialize($idParams));
		return $id;
	}

	public function getID()
	{
		return $this->id;
	}

	public function save($cachedContent){
		global $Config;
		$cacheFile = '';
		if($this->cache)
		{
			$cacheFile = new File($GLOBALS['ROOT_PATH'] . '/' .$Config->get("file.temp_dir").'/cache/');
			$cacheFile->setPath($cacheFile->getPathConverted());
			if(!$cacheFile->exists())
			{
				$cacheFile->makeDirectory();
			}
			$cacheFile->setPath($cacheFile->getPathConverted() . $this->id);
			if(!$cacheFile->exists() || time() - $cacheFile->getModifiedTime() > $this->cacheTime)
			{
				$cacheFile->touchFile();
				$cacheFile->openFile("rw+");
				$cacheFile->writeData($cachedContent);
			}
		}
	}

	public function get($return = false)
	{
		global $Config;
		$cacheFile = '';
		$cachedContent = '';
		if($this->cache)
		{
			$cacheFile = new File($GLOBALS['ROOT_PATH'] . '/' . $Config->get("file.temp_dir").'/cache/'.$this->id);
			$cacheFile->setPath($cacheFile->getPathConverted());
			if($cacheFile->exists())
			{
				$cachedContent = $cacheFile->getContents();
				if($return)
					return $cachedContent;
				echo $cachedContent;
				exit();
			}
		}
	}

	public function isCached()
	{
		global $Config;
		$cacheFile = new File($GLOBALS['ROOT_PATH'] . '/' . $Config->get("file.temp_dir") . '/cache/' . $this->id);
		$cacheFile->setPath($cacheFile->getPathConverted());
		if($cacheFile->exists() && $this->cache)
			if(time() - $cacheFile->getModifiedTime() < $this->cacheTime)
				return true;
		return false;
	}

	public static function clear()
	{
		global $Config;
		$cacheFile = new File($GLOBALS['ROOT_PATH'] . '/' . $Config->get("file.temp_dir").'/cache/');
		$cacheFile->setPath($cacheFile->getPathConverted());
		if($handle = $cacheFile->openDir())
		{
			while(false !== ($file = readdir($handle)))
			{
				if($file!='.' && $file!='..' && $file!='.svn')
				{
					$cacheFile->setPath($GLOBALS['ROOT_PATH'] . '/' . $Config->get("file.temp_dir").'/cache/'.$file);
					$cacheFile->delete();
				}
			}
		} else {
			throw new CacheException("Could not clear cache dir");
		}
	}

	public static function clearOld()
	{
		global $Config;
		$cacheFile = new File($GLOBALS['ROOT_PATH'] . '/' . $Config->get("file.temp_dir").'/cache/');
		$cacheFile->setPath($cacheFile->getPathConverted());
		if($handle = $cacheFile->openDir())
		{
			while(false !== ($file = readdir($handle)))
			{
				if($file!='.' && $file!='..' && $file!='.svn')
				{
					$cacheFile->setPath($GLOBALS['ROOT_PATH'] . '/' . $Config->get("file.temp_dir").'/cache/'.$file);
					if(time() - $cacheFile->getModifiedTime() > $Config->get("cache.time"))
					{
						$cacheFile->delete();
					}
				}
			}
		} else {
			throw new CacheException("Could not clear old cache");
		}
	}

}
