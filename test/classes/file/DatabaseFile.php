<?php
/**
 * DatabaseFile.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/file
 */

class DatabaseFile extends File implements FileInterface
{
	/*
	 * File data stored in a 'files' table within the database
	 * The path provides a unique lookup value
	 * Data is stored in text fields and is base64 encoded within the
	 * database, for easy storage and retrieval using string SQL commands.
	 *
	 * Note that base64 encoding takes up approximately 33% more space than the
	 * original data, so if we can avoid doing this, we should.
	 * (Unfortunately, the binary types seem to differ wildly between databases)
	 */
	private $atime;
	private $ctime;
	private $data;
	private $exists;
	private $fileid;
	private $groupid;
	private $mtime;
	private $offset;
	private $permissions;
	private $read;
	private $size;
	private $userid;
	private $write;

	/*************************************************************************
	 * PUBLIC STATIC FUNCTIONS
	 ************************************************************************/

	public static function clearStatCache()
	{
		throw new DatabaseFileException('Clearing the stat cache is not supported for database files.');
	}

	public static function fileExists($path)
	{
		// Grr, now we need another query!
		// (Heh!)
		$result = $GLOBALS['DataBase']->query("SELECT fileid FROM files WHERE path='$path'");
		if ( $result->numRows() == 1 )
			return true;
		else return false; // More than one entry shouldn't be allowed, so it cannot reasonably exist!
	}

	/*************************************************************************
	 * PUBLIC FUNCTIONS
	 ************************************************************************/

	public function __construct($path='')
	{
		$this->path = $path;
		$this->data = NULL;
		$this->size = 0;
		$this->exists = false;
		$this->offset = 0;
		$this->read = $this->write = false;
	}
	
	public function  __destruct()
	{
		// TODO: Flush data buffer back to database if it is not null
	}
	
	public function changeGroup($group)
	{
		$this->updateIntegerField('groupid',$group);
	}
	
	public function changeMode($mode)
	{
		$this->updateIntegerField('permissions',$mode);
	}
	
	public function changeOwner($owner)
	{
		$this->updateIntegerField('userid',$owner);
	}
	
	public function changeOwnerGroup($owner, $group)
	{
		$this->changeOwner($owner);
		$this->changeGroup($group);
	}
	
	public function closeDir()
	{
		throw new DatabaseFileException('Opening and closing directories is not yet supported.');
	}

	public function closeFile()
	{
		if ( is_null($this->data) )
			throw new DatabaseFileException('File is not open.');
		if ( $this->write )
		{
			$this->b64enc();
			$GLOBALS['DataBase']->query("UPDATE files SET data='$this->data' WHERE fileid=$this->fileid");
		}
		$this->data = NULL;
		$this->offset = 0;
	}
	
	public function copy($destination)
	{
		$this->check_exists();
		$new_path = IOSystem::DBEncode($destination);
		$sql = 'INSERT INTO files(path,userid,groupid,permissions,atime,ctime,mtime,size,data)';
		$sql .= " VALUES('$new_path',$this->userid,$this->groupid,$this->permissions,";
		$sql .= "$this->atime,$this->ctime,$this->mtime,$this->size,'')";
		$GLOBALS['DataBase']->query($sql);
		unset($sql);
		$GLOBALS['DataBase']->query("UPDATE files SET data=(SELECT data FROM files WHERE fileid=$this->fileid) WHERE path='$new_path'");
	}

	public function delete()
	{
		$this->check_exists();
		$GLOBALS['DataBase']->query('DELETE FROM files WHERE fileid=$this->fileid');
		$this->exists = false;
	}
	
	public function exists()
	{
		// if check_exists() does not throw, the file exists within the database
		try
		{
			$this->check_exists();
			return true;
		}
		catch(DatabaseFileException $e)
		{
			return false;
		}
	}	

	public function flush()
	{
		$this->put_file_data();
	}

	public function getAccessTime()
	{
		$this->check_exists();
		return $this->atime;
	}

	public function getBaseName()
	{
		return basename($this->path);
	}

	public function getChangeTime()
	{
		$this->check_exists();
		return $this->ctime;
	}

	public function getDirectory()
	{
		return dirname($this->path);
	}
	
	public function getFreeSpace()
	{
		throw new DatabaseFileException('Disk space reporting not available for database files.');
	}

	public function getRealPath()
	{
		// We could try to implement this with preg replaces and similar
		// Unfortunately we'd need to store the fake directory hierarchy
		// in the database, too...
		throw new DatabaseFileException('Obtaining the real path is not supported for database files.');
	}
	
	public function getSize()
	{
		$this->check_exists();
		return $this->size();
	}
		
	public function getTotalSpace()
	{
		throw new DatabaseFileException('Disk space reporting not available for database files.');
	}

	public function move($destination)
	{
		$this->check_exists();
		$new_path = IOSystem::DBEncode($destination);
		$GLOBALS['DataBase']->query("UPDATE files SET path='$new_path' WHERE fileid=$this->fileid");
		$this->path = $destination;
	}

	public function openFile($mode)
	{
		// Lets hope people only want to use this with small files...
		// Read the whole lot into a string...
		if ( ! is_null($this->data) )
			throw new DatabaseFileException('File already open.');
			
		$mode = strtolower($mode);
		
		switch($mode)
		{
			case 'r+':
				$this->write = true;
			case 'r':
				$this->read = true;
				// Open and position pointer at beginning
				// Don't truncate
				// Don't create
				$this->check_exists(); // Throw exception if the file doesn't exist
				// Pull file data from the database
				$this->get_file_data();
				// Set offset to zero
				$this->offset = 0;
				break;
			case 'w+':
				$this->read = true;
			case 'w':
				$this->write = true;
				// Open and position pointer at beginning
				// Truncate
				// Create if it doesn't exist
				$this->offset = 0;
				// No need to pull data ; we're truncating anyway!
				// Check if the file exists and create it if not...
				if ( $this->non_throw_check_exists() == false )
				{
					$this->create_file();
				}
				break;
			case 'a+':
				$this->read = true;
			case 'a':
				$this->write = true;
				// get data and set offset = size
				// Create if it doesn't exist
				if ( $this->non_throw_check_exists() == false )
				{
					$this->create_file();
				}
				else
				{
					$this->get_file_data();
				}
				$this->offset = $this->size;
				break;
			case 'x+':
				$this->read = true;
			case 'x':
				$this->write = true;
				// Check if the file exists and FAIL if it does
				if ( $this->non_throw_check_exists() == true )
				{
					throw new DatabaseFileException('File already exists.');
				}
				// Create
				$this->create_file();
				// Set pointer to beginning
				$this->offset = 0;
		}
	}
	
	/*************************************************************************
	 * PRIVATE FUNCTIONS 
	 ************************************************************************/

	private function b64enc()
	{
		if ( ! is_null($this->data) )
			$this->data = base64_encode($this->data);
	}
	
	private function b64dec()
	{
		if ( ! is_null($this->data) )
			$this->data = base64_decode($this->data);
	}
	
	private function check_exists()
	{
		// getFileID() throws if the file does not exist within the database
		// so this is sufficient to test for existence in accordance with
		// File::check_exists()
		// However, getFileInfo() is slow, since it involves a DB query,
		// so we'll cache the result in $this->exists and test that first...
		if ( $this->exists )
			return true;
		else $this->getFileInfo();
	}
	
	private function check_open()
	{
		if ( is_null($this->data) )
			throw new DatabaseFileException('File is not open');
	}
	
	private function create_file()
	{
		global $Config;		
		$path = IOSystem::DBEncode($this->path);
		$this->userid = $Config->get('dbfile.default_userid');
		$this->groupid = $Config->get('dbfile.default_groupid');
		$this->permissions = $Config->get('file.create_mode');
		// Set all times to NOW!
		$this->atime = $this->ctime = $this->mtime = time();
		$this->size = 0;
		$this->data = '';
		$sql = "INSERT INTO files(path,userid,groupid,permissions,atime,ctime,mtime,size,data)";
		$sql .= " VALUES('$path',$this->userid,$this->groupid,$this->permissions,";
		$sql .= "$this->atime,$this->ctime,$this->mtime,$this->size,'$this->data')";
		// Create the database entry
		$GLOBALS['DataBase']->query($sql);
	}
	
	private function extract_array($array)
	{
		// This member function is a bit like the PHP extract() function
		// but extracts to member variables, and performs removal of DB escaped
		// characters 
		foreach($array as $key => $value)
		{
			$this->$key = IOSystem::DBUnencode($value);
		}
	}

	private function get_file_data()
	{
		$result = $GLOBALS['DataBase']->query("SELECT data FROM files WHERE fileid=$this->fileid");
		if ( $result->numRows() != 1 )
			throw new DatabaseFileException('Could not get file data from database.');
		$this->data = $result->getResult(0,0);
		$this->b64dec();
	}

	private function put_file_data()
	{
		$this->check_exists();
		if ( ! is_null($this->data) )
		{
			$this->b64enc();
			$GLOBALS['DataBase']->query("UPDATE files SET data='$this->data' WHERE fileid=$this->fileid");
			$this->data = NULL;
		}
	}

	private function getFileInfo()
	{
		$path = IOSystem::DBEncode($this->path);
		$result = $GLOBALS['DataBase']->query("SELECT fileid,owner,group,permissions,atime,ctime,mtime,size FROM files WHERE path='$path'");
		if ( $result->numRows() != 1 )
		{
				throw new DatabaseFileException('Could not look up file ID in database');
		}
		$this->extract_array($result->getRow());
		$this->exists = true;
	}

	private function non_throw_check_exists()
	{
		if ( $this->exists )
			return true;
		else
		{
			try
			{
				$this->getFileInfo(false);
				return true;
			}
			catch ( DatabaseFileException $e )
			{
				return false;
			}
		}
	}
	
	private function updateIntegerField($field, $new_value)
	{
		$GLOBALS['DataBase']->query("UPDATE files SET $field=$new_value WHERE fileid=$this->fileid");
	}
	
	private function updateTextField($field, $new_value)
	{
		$GLOBALS['DataBase']->query("UPDATE files SET $field='$new_value' WHERE fileid=$this->fileid");
	}

}

?>
