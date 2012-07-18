<?php

/**
 * IOSystem.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package classes/iosystem
 */

/**
 * @package classes/iosystem
 */
class IOSystem
{
	public static function GET($key)
	{
		return IOSystem::cleanInput($_GET[$key]);
	}

	public static function POST($key)
	{
		return IOSystem::cleanInput($_POST[$key]);
	}

	public static function REQUEST($key)
	{
		return IOSystem::cleanInput($_REQUEST[$key]);
	}

	public static function FILE($key)
	{
		// Return the information from $_FILE, after input cleaning
		return array('name' => IOSystem::cleanInput($_FILES[$key]['name']),
			'type' => IOSystem::cleanInput($_FILES[$key]['type']),
			'size' => $_FILES[$key]['size'],
			'tmp_name' => $_FILES[$key]['tmp_name'],
			'error' => $_FILES[$key]['error']
			);
	}

	public static function COOKIE($key)
	{
		return IOSystem::cleanInput($_COOKIE[$key]);
	}

	public static function forceCleanInput($data)
	{
		// Ignores config settings and performs both
		// tag removal and HTML entity encoding.
		// Does not allow any tags.
		return htmlentities(strip_tags($data), ENT_NOQUOTES);
	}

	public static function cleanInput($data)
	{
		global $Config;

		if ( stristr($Config->get('iosystem.strip_tags'), 'true') )
		{
			$data = strip_tags($data, $Config->get('iosystem.allowed_tags') );
		}
		if ( stristr($Config->get('iosystem.entity_quote_html'), 'true') )
		{
			$data = htmlentities($data, ENT_NOQUOTES);
		}
		return $data;
	}

	public static function hash($password)
	{
		global $Config;
		return hash($Config->get("iosystem.hash_type"), $password);
	}

	public static function plainText($data)
	{
		// Removes URL encoding or entity encoding in the data
		// Suitable for plain text output (as opposed to HTML or XHTML output)
		return urldecode(html_entity_decode($data, ENT_NOQUOTES));
	}

	public static function DBEncode($data)
	{
		return addslashes($data);
	}

	public static function DBUnencode($data)
	{
		if(@get_magic_quotes_gpc()) // THIS NEEDS TO BE REPLACED BY PHP6
			return stripslashes($data);
		return $data;
	}
	
	public static function send($host, $script, $data, $uagent, $method = "POST", $port = 80)
	{
		$fp = fsockopen($host,$port);
		if ($method == 'GET') $script .= '?' . $data;
		fputs($fp, "$method $script HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
		if ($method == 'POST') fputs($fp, "Content-length: " . strlen($data) . "\r\n");
		fputs($fp, "User-Agent: $uagent\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		if ($method == 'POST') fputs($fp, $data);
		$buf = '';
		while (!feof($fp)) $buf .= fgets($fp, 128);
		fclose($fp);
		return $buf;
	}
}

?>
