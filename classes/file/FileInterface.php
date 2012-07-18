<?php

/**
 * FileInterface.php
 * Public interface for class File.
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/file
 */

interface FileInterface
{
	/* PUBLIC STATIC */
	public static function clearStatCache();
	public static function convertPathToLocal($path); /* Returns string */
	public static function convertPathToUNIX($path); /* Returns string */
	public static function convertPathToWindows($path); /* Returns string */
	public static function fileExists($path); /* Returns boolean */
	public static function getUmask(); /* Returns integer */
	public static function setUmask($mask); /* Returns integer */
	public static function glob($pattern, $flags=NULL); /* Returns array */
	

	/* PUBLIC */
	public function __construct($path='');
	public function __destruct();
	public function changeGroup($group);
	public function changeMode($mode);
	public function changeOwner($owner);
	public function changeOwnerGroup($owner, $group);
	public function closeDir();
	public function closeFile();
	public function copy($destination);
	public function delete();
	public function exists(); /* Returns boolean */
	public function flush(); /* Returns boolean */
	public function getAccessTime(); /* Returns integer */
	public function getBaseName(); /* Returns string */
	public function getChangeTime(); /* Returns integer */
	public function getChar(); /* Returns character */
	public function getContents(); /* Returns string */
	public function getContentsArray(); /* Returns array */
	public function getCSV($length=NULL, $delimiter=',', $enclosure='"'); /* Returns array */
	public function getDirectory(); /* Returns string */
	public function getFreeSpace(); /* Returns integer */
	public function getGroup(); /* Returns integer */
	public function getInode(); /* Returns integer */
	public function getLine(); /* Returns string */
	public function getLineStripped($length=NULL, $allowable_tags=NULL); /* Returns string */
	public function getModifiedTime(); /* Returns integer */
	public function getOwner(); /* Returns integer */
	public function getPath(); /* Returns string */
	public function getPathConverted(); /* Returns string */
	public function getPathInfo(); /* Returns array */
	public function getPermissions(); /* Returns integer */
	public function getRealPath(); /* Returns string */
	public function getSize(); /* Returns integer */
	public function getTotalSpace(); /* Returns integer */
	public function getType(); /* Returns string */
	public function hardLink($link); /* Returns boolean */
	public function isDir(); /* Returns boolean */
	public function isEOF(); /* Returns boolean */
	public function isExecutable(); /* Returns boolean */
	public function isFile(); /* Returns boolean */
	public function isLink(); /* Returns boolean */
	public function isReadable(); /* Returns boolean */
	public function isUploaded(); /* Returns boolean */
	public function isWritable(); /* Returns boolean */
	public function isWriteable(); /* Returns boolean */
	public function lockExclusive(); /* Returns TRUE */
	public function lockFile($operation); /* Returns TRUE */
	public function lockShared(); /* Returns TRUE */
	public function makeDirectory($recursive=TRUE); /* Returns boolean */
	public function MIMEType(); /* Returns string */
	public function move($destination);
	public function moveUploadedFile($destination);
	public function openDir(); /* Returns handle created by opendir() */
	public function openFile($mode); /* Returns file handle */
	public function passThrough();
	public function passThroughForceDownload();
	public function passThroughWithHeaders();
	public function putContents($data); /* Returns integer or FALSE on failure */
	public function putCSV(array $fields, $delimiter=',', $enclosure='"'); /* Returns integer or FALSE on failure */
	public function readData($length); /* Returns string (binary data) */
	public function removeDirectory(); /* Returns boolean */
	public function rewind(); /* Returns boolean */
	public function seek($offset, $whence=SEEK_SET); /* Returns integer */
	public function setPath($path);
	public function softLink($link); /* Returns boolean */
	public function statFile(); /* Returns array */
	public function statPath(); /* Returns array */
	public function symLink($link); /* Returns boolean */
	public function tell(); /* Returns integer */
	public function touchFile(); /* Returns boolean */
	public function truncateFile($length=0);
	public function unlockFile(); /* Returns TRUE */
	public function writeData($data, $length=NULL); /* Returns integer or FALSE on failure */
};


