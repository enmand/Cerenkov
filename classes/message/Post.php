<?php
/**
 * Post.php
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/message
 */

class Post
{
	private $postid;
	private $author;
	private $timestamp;
	private $body;
	private $title;

	private $table; // Table to select from and insert into
	
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

	public function __construct($table, $postid = NULL)
	{
		$this->table = $table;
		if ( is_null($postid) )
		{
			// Construct an empty post
			// Set postid == NULL for now
			$this->postid = NULL;
			$this->author = '';
			$this->timestamp = time(); // Can't hurt to set this!
			$this->body = '';
			$this->title = '';
		}
		else
		{
			// A postid was provided, request it from the database
			$this->postid = $postid;
			$sql = "SELECT author,timestamp,body,title FROM $this->table WHERE postid=$this->postid";
			$result = $GLOBALS['DataBase']->query($sql);
			if ( $result->numRows() != 1 )
			{
				throw new MessageException('Unique post not found');
			}
			$row = $result->getRow();
			$this->extract_array($row);
		}
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getDate($format=NULL)
	{
		if ( is_null($format) )
		{
			$format = 'r';
		}
		return date($format, $this->timestamp);
	}
	
	public function getBody()
	{
		return $this->body;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setAuthor($author)
	{
		$this->author = $author;
	}
	
	public function setTime($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	public function setTimeNow()
	{
		$this->timestamp = time();
	}

	public function setBody($body)
	{
		$this->body = $body;
	}
	
	public function appendBody($body)
	{
		$this->body .= $body;
	}

	public function save()
	{
		$title = IOSystem::DBEncode($this->title);
		$author = IOSystem::DBEncode($this->author);
		$body = IOSystem::DBEncode($this->body);
		$sql = "INSERT INTO $this->table(title,author,timestamp,body)";
		$sql .= "VALUES('$title','$author',$this->timestamp,'$body')";
		$GLOBALS['DataBase']->query($sql);
	}
	
	public function clear()
	{
		$this->author = '';
		$this->title = '';
		$this->body = '';
		$this->timestamp = now();
		$this->postid = NULL;
	}

 }

?>
