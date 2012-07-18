<?php
/**
 * File.php
 * File abstracts moving, copying, file output, etc.
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/file
 */

class File implements FileInterface
{
	private $path;
	private $handle;

	public function __construct($path='')
	{
		// Should probably call convertPathToLocal()
		// before assigning...
		$this->path = $path;
		$this->handle = NULL;
	}

	public function __destruct()
	{
		if ( $this->handle != NULL && $this->isFile())
			$this->closeFile();
		else if($this->handle != NULL && $this->isDir())
			$this->closeDir();
	}

	public function setPath($path)
	{
		$this->path = $path;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getPathConverted()
	{
		return File::convertPathToLocal($this->path);
	}

	public static function convertPathToLocal($path)
	{
		global $Config;
		$type = strtolower($Config->get('file.path_type'));
		switch($type)
		{
			case 'unix':
				return File::convertPathToUNIX($path);
				break;
			case 'dos':
			case 'windows':
				return File::convertPathToWindows($path);
				break;
			default:
				throw new FileException('Local path type not understood. Check config.ini');
				break;
		}
	}

	public static function convertPathToUNIX($path, $root='/')
	{
		// Replace drive letter if present with $root
		$path = preg_replace('/^[A-Za-z]\:/', $root, $path, 1);

		// Convert \ to /
		$path = preg_replace('/\\//', '/', $path);
		return $path;
	}

	public static function convertPathToWindows($path, $drive='C')
	{
		// Replace / with C:\ where C is the drive letter from $drive
		$path = preg_replace('#^/#', $drive . ':\\', $path);

		// Convert / to \
		$path = preg_replace('#/#', '\\', $path);
		return $path;
	}

	private function check_exists()
	{
		if ( file_exists($this->path) )
			return true;
		else throw new FileException('File does not exist.');
	}

	public function isUploaded()
	{
		$this->check_exists();
		return is_uploaded_file($this->path);
	}

	public function moveUploadedFile($destination)
	{
		if ( $this->isUploaded() )
		{
			$this->move($destination);
		}
		else
		{
			throw new FileException('Source file is not uploaded.');
		}
	}

	public function move($destination)
	{
		// Move from $this->path to $destination
		if ( file_exists($destination) )
		{
			throw new FileException('Destination file exists, delete it first.');
		}
		else
		{
			if ( $this->isUploaded() )
			{
				move_uploaded_file($this->path, $destination);
			}
			else
			{
				rename($this->path, $destination);
			}
			$this->path = $destination;
		}
	}

	public function copy($destination)
	{
		if ( file_exists($destination) )
		{
			throw new FileException('Destination file exists, delete it first.');
		}
		else
		{
			copy($this->path, $destination);
		}
	}

	public function delete()
	{
		// Delete the file this object references
		$this->check_exists();
		unlink($this->path);
		$this->handle = NULL;
	}

	public function exists()
	{
		return file_exists($this->path);
	}

	public static function fileExists($path)
	{
		return file_exists($path);
	}

	public function getBaseName()
	{
		return basename($this->path);
	}

	public function getRealPath()
	{
		$this->check_exists();
		return realpath($this->path);
	}

	public function getSize()
	{
		$this->check_exists();
		return filesize($this->path);
	}

	public function changeGroup($group)
	{
		$this->check_exists();
		return chgrp($this->path, $group);
	}

	public function changeMode($mode)
	{
		$this->check_exists();
		return chmod($this->path, $mode);
	}

	public function changeOwner($owner)
	{
		$this->check_exists();
		return chmod($this->path, $owner);
	}

	public function changeOwnerGroup($owner, $group)
	{
		$this->check_exists();
		if ( $this->changeOwner($owner) and $this->changeGroup($group) )
		{
			return true;
		}
		else return false;
	}

	public static function clearStatCache()
	{
		clearstatcache();
	}

	public function getDirectory()
	{
		return dirname($this->path);
	}

	public function getFreeSpace()
	{
		return disk_free_space($this->path);
	}

	public function getTotalSpace()
	{
		return disk_total_space($this->path);
	}

	private function check_open()
	{
		$this->check_exists();
		if ( is_null($this->handle) )
		{
			throw new FileException('File is not open.');
		}
	}

	public function openFile($mode)
	{
		$this->check_exists();
		if ( is_null($this->handle) )
		{
			$this->handle = fopen($this->path, $mode);
			return $this->handle;
		}
		else throw new FileException('File is already open.');
	}

	public function closeFile()
	{
		fclose($this->handle);
		$this->handle = NULL;
	}

	public function openDir()
	{
		$this->isDir();
		if(is_null($this->handle))
		{
			$this->handle = opendir($this->path);
			return $this->handle;
		}
	}

	public function closeDir()
	{
		closedir($this->handle);
		$this->handle = NULL;
	}
	public function isEOF()
	{
		$this->check_open();
		return feof($this->handle);
	}

	public function flush()
	{
		$this->check_open();
		return fflush($this->handle);
	}

	public function getChar()
	{
		$this->check_open();
		return fgetc($this->handle);
	}

	public function getCSV($length=NULL, $delimiter=',', $enclosure='"')
	{
		$this->check_open();
		if ( is_null($length) )
		{
			return fgetcsv($this->handle);
		}
		else
		{
			return fgetcsv($this->handle, $length, $delimiter, $enclosure);
		}
	}

	public function putCSV(array $fields, $delimiter=',', $enclosure='"')
	{
		$this->check_open();
		return fputcsv($this->handle, $fields, $delimiter, $enclosure);
	}

	public function getLine($length=NULL)
	{
		$this->check_open();
		if ( is_null($length) )
			return fgets($this->handle);
		else return fgets($this->handle, $length);
	}

	public function readData($length)
	{
		$this->check_open();
		return fread($this->handle, $length);
	}

	public function writeData($data, $length=NULL)
	{
		$this->check_open();
		if ( is_null($length) )
			return fwrite($this->handle, $data);
		else
			return fwrite($this->handle, $data, $length);
	}

	public function seek($offset, $whence=SEEK_SET)
	{
		$this->check_open();
		return fseek($this->handle, $offset, $whence);
	}

	public function tell()
	{
		$this->check_open();
		return ftell($this->handle);
	}

	public function rewind()
	{
		$this->check_open();
		return rewind($this->handle);
	}

	public function truncateFile($length=0)
	{
		$this->check_open();
		ftruncate($this->handle, $length);
	}

	public function statFile()
	{
		$this->check_open();
		return fstat($this->handle);
	}

	public function statPath()
	{
		$this->check_exists();
		return stat($this->path);
	}

	public function getLineStripped($length=NULL, $allowable_tags=NULL)
	{
		$this->check_open();
		if ( is_null($length) )
		{
			return fgetss($this->handle);
		}
		elseif ( is_null($allowable_tags) )
		{
			return fgetss($this->handle, $length);
		}
		else return fgetss($this->handle, $length, $allowable_tags);
	}

	public function getContents($check = FALSE)
	{
		if($check) $this->check_exists();
		return file_get_contents($this->path);
	}

	public function putContents($data)
	{
		$this->check_exists();
		return file_put_contents($this->path, $data);
	}

	public function getContentsArray()
	{
		$this->check_exists();
		return file($this->path);
	}

	public function getAccessTime()
	{
		$this->check_exists();
		return fileatime($this->path);
	}

	public function getChangeTime()
	{
		$this->check_exists();
		return filectime($this->path);
	}

	public function getModifiedTime()
	{
		$this->check_exists();
		return filemtime($this->path);
	}

	public function getGroup()
	{
		$this->check_exists();
		return filegroup($this->path);
	}

	public function getInode()
	{
		$this->check_exists();
		return fileinode($this->path);
	}

	public function getOwner()
	{
		$this->check_exists();
		return fileowner($this->path);
	}

	public function getPermissions()
	{
		$this->check_exists();
		return fileperms($this->path);
	}

	public function getType()
	{
		$this->check_exists();
		return filetype($this->path);
	}

	public function lockFile($operation)
	{
		$this->check_open();
		$would_block = 0;
		if ( flock($this->handle, $operation, $would_block) == false )
		{
			if ( $would_block )
				throw new FileException('Locking would block.');
			else throw new FileException('Could not lock file.');
		}
		return true;
	}

	public function lockShared()
	{
		return $this->lockFile( LOCK_SH | LOCK_NB );
	}

	public function lockExclusive()
	{
		return $this->lockFile( LOCK_EX | LOCK_NB );
	}

	public function unlockFile()
	{
		return $this->lockFile( LOCK_UN | LOCK_NB );
	}

	public function passThrough()
	{
		$this->check_exists();
		if ( is_null($this->handle) )
		{
			// Use readfile()
			return readfile($this->path);
		}
		else
		{
			// use fpassthru()
			return fpassthru($this->handle);
		}
	}

	public static function glob($pattern, $flags=NULL)
	{
		if ( is_null($flags) )
			return glob($pattern);
		else return glob($pattern, $flags);
	}

	public function isDir()
	{
		//$this->check_exists();
		return is_dir($this->path);
	}

	public function isExecutable()
	{
		$this->check_exists();
		return is_executable($this->path);
	}

	public function isFile()
	{
		return is_file($this->path);
	}

	public function isLink()
	{
		$this->check_exists();
		return is_link($this->path);
	}

	public function isReadable()
	{
		$this->check_exists();
		return is_readable($this->path);
	}

	public function isWritable()
	{
		$this->check_exists();
		return is_writable($this->path);
	}

	public function isWriteable()
	{
		// Compatibility function
		return $this->isWritable();
	}

	public function hardLink($link)
	{
		$this->check_exists();
		return link($this->path, $link);
	}

	public function symLink($link)
	{
		$this->check_exists();
		return symlink($this->path, $link);
	}

	public function softLink($link)
	{
		// Compatibility function
		$this->symLink($link);
	}

	public function makeDirectory($recursive=true)
	{
		global $Config;
		$mode = $Config->get('file.create_mode');
		return mkdir($this->path, $mode, $recursive);
	}

	public function removeDirectory()
	{
		$this->check_exists();
		if ( is_dir($this->path) )
			return rmdir($this->path);
		else throw new FileException('File is not a directory.');
	}

	public function getPathInfo()
	{
		return pathinfo($this->path);
	}

	public function touchFile()
	{
		return touch($this->path);
	}

	public static function setUmask($mask)
	{
		return umask($mask);
	}

	public static function getUmask()
	{
		return umask();
	}

	public function MIMEType()
	{
		$this->check_exists();
		return mime_content_type($this->path);
	}

	public function passThroughWithHeaders()
	{
		// Output file contents into http stream with suitable headers
		header('Content-Type: ' . $this->MIMEType() );
		header('Content-Length: ' . $this->getSize() );
		header('Content-Disposition: attachment; filename="'. basename($this->path) .'"');
		header("Content-Transfer-Encoding: binary\n");
		readfile($this->path);
	}

	public function passThroughForceDownload()
	{
		// Output file as application/octet-stream to try to force browsers to download
		// rather than display
		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . $this->getSize() );
		header('Content-Disposition: attachment; filename="'. basename($this->path) .'"');
		header("Content-Transfer-Encoding: binary\n");
		readfile($this->path);
	}
}

?>
