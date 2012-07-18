<?php
/**
 * config.php
 *
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package system
 */
/**
 * SQL CONFIG
 *
 * Codes for SQL:
 * MySQL:   1
 * PgSQL:   2
 * MSSQL:   3
 * MSQL:    4
 * SQLLITE: 5
 */

/**
 * @package system
 */
class Configuration
{
	/*
	 * config_map has the form [section][key][value]
	 */
	private $config_map;
	private $config_file;

	public function __construct($config_file)
	{
		/*
		 * Fill the class with config information
		 * There are two ways to do this:
		 * (1) Load a serialised config_map
		 * (2) Read from a php.ini style file
		 * Methods exist for each, we pick the right one based on the
		 * extension of the filename passed to the constructor...
		 */

		$this->config_file = $config_file;

		if ( preg_match('/\.ini\.php$/', $config_file) )
		{
			// Load from a .ini
			$this->loadConfigINI();
		}
		elseif ( preg_match('/\.dat$/', $config_file) )
		{
			// Load from a .dat (serialised file)
			$this->loadConfigSerialised();
		}
		else
		{
			// Could not determine how to load the information from the file specified
			throw new ConfigException('Could not load configuration from the specified file.');
		}
	}

	public function __destruct()
	{
		// Comment this out if you don't want changes made at run-time to be saved for the future

		if ( preg_match('/\.ini\.php$/', $this->config_file) )
		{
			// Load from a .ini
			$this->saveConfigINI();
		}
		elseif ( preg_match('/\.dat$/', $this->config_file) )
		{
			// Load from a .dat (serialised file)
			$this->saveConfigSerialised();
		}
		else
		{
			// Could not determine how to load the information from the file specified
			throw new ConfigException('Could not load configuration from the specified file.');
		}
	}

	public function get($key)
	{
		$key = strtolower($key);
		$keys = explode('.', $key);
		if ( count($keys) != 2 )
			throw new ConfigException('Invalid config key request.');
		$section = $keys[0];
		$key = $keys[1];
		if ( array_key_exists($section, $this->config_map)
			&& array_key_exists($key, $this->config_map[$section]) )
			return $this->config_map[$section][$key];
		else throw new ConfigException('Config: Key does not exist.');
	}

	public function set($key, $value)
	{
		$key = strtolower($key);
		$keys = explode('.', $key);
		if ( count($keys) != 2 )
			throw new ConfigException('Invalid config key request.');
		$section = $keys[0];
		$key = $keys[1];
		$this->config_map[$section][$key] = $value;
	}

	private function saveConfigSerialised()
	{
		$config_data = serialize($this->config_map);
		#$config_file = fopen($this->config_file, 'w');
		#fwrite($config_file, $config_data);
		#fclose($config_file);
	}

	private function saveConfigSerialized()
	{
		$this->saveConfigSerialised();
	}

	private function saveConfigINI()
	{
		// It appears PHP does not have a function to do this directly
		// Let's try writing our own

		/*$config_file = fopen($this->config_file, 'w');

		$timestamp = date('H:m, l d F Y');
		fwrite($config_file, ";<?php die(\"No access;\"); ?>\n;File written by Cerenkov at $timestamp\n");

		foreach( $this->config_map as $section => $keys )
		{
			fwrite($config_file, "[$section]\n");
			foreach( $keys as $key => $value )
			{
				fwrite($config_file, "$key = \"$value\"\n");
			}
			fwrite($config_file, "\n");
		}

		fclose($config_file);
		 */
	}
	
	public function setPath($path)
	{
		$this->config_file = $path;
	}

	private function loadConfigSerialised()
	{
		$config_data = file_get_contents($this->config_file);
		$this->config_map = unserialize($config_data);
	}

	private function loadConfigSerialized()
	{
		// Compatibility function for US English
		$this->loadConfigSerialised();
	}

 	private function loadConfigINI()
	{
		$this->config_map = parse_ini_file($this->config_file, true);
	}
}
?>
