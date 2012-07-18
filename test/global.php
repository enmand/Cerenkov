<?php
/**
 * global.php
 *
 * Before we begin, I must apologize for the amount of comments. They are for PHPDoc. You should be happy we are documenting
 * 
 * @author Cerenkov Group
 * @copyright Cerenkov 2007
 * @package system
 */

/**
 * Define the version of the Cerenkov Framework (F_VERSION)
 */
define("F_VERSION", "1.0a", false);

/**
 * Define the version of the Cerenkov Engine (E_VERISION)
 */
define("E_VERSION", "0.3.1", false);

/**
 * Define if this is a DEBUG version, or release
 * Also, we should prompt users to change this when installing (or do it for them)
 * as it may release harmful information
 * NOTE: This is ONLY for Exceptions. Use debug.debug in config.ini.php for all
 * other debug stuff.
 */
define("DEBUG_E", false, FALSE);

/**
 * Require the CerenkovException class since everything extends it.
 */
require_once("exception/CerenkovException.php");

/**
 * We require the ConfigException for any Exception in Configuration
 */
require_once('ConfigException.php');

/**
 * We require the AutoLoader for any Exception in AutoLoader
 */
require_once('AutoloaderException.php');

/**
 * We must load the Configuration class file in order to load the Configuration class
 */
require_once('Config.php');

/**
 * AutoLoader is user a little further down, but is here for aesthetics
 */ 
require_once('Autoloader.php');

/**
 * The global configuration object is set here.
 *
 * @global Configuration $Config The global configuration object
 */
global $Config;
$Config = new Configuration('etc/config.ini.php');

/**
 * We are resetting the ROOT_PATH here so that we have a full path to the Cerenkov installation
 * since it makes accessing files and classes much easier
 *
 * @global string $GLOBALS['ROOT_PATH']
 */
$GLOBALS['ROOT_PATH'] = $_SERVER['DOCUMENT_ROOT'].'/'.$Config->get("cerenkov.path")."/";

/**
 * The absolute path to the system folder
 * @global string $GLOBALS['SYSTEM_PATH']
 */
$GLOBALS['SYSTEM_PATH'] = $GLOBALS['ROOT_PATH'] . 'system/';

/**
 * The absolute path to the etc (configuration) folder
 * @global string $GLOBALS['CONFIG_PATH']
 */
$GLOBALS['CONFIG_PATH'] = $GLOBALS['SYSTEM_PATH'] . 'etc/';

/**
 * The absolute path to the base classes folder
 * @global string $GLOBALS['CLASS_PATH']
 */
$GLOBALS['CLASS_PATH'] = $GLOBALS['ROOT_PATH'] . 'classes/';

/**
 * The absolute path to the plugins (extra classes, etc) folder
 * @global string $GLOBALS['PLUGIN_PATH']
 */
$GLOBALS['PLUGIN_PATH'] = $GLOBALS['ROOT_PATH'] . 'plugins/';

/**
 * The absolute path to the templates folder
 * @global string $GLOBALS['TEMPLATE_PATH']
 */
$GLOBALS['TEMPLATE_PATH'] = $GLOBALS['ROOT_PATH'] . 'templates/';

/**
 * The absolute path to the test folder
 * @global string $GLOBALS['TEST_PATH']
 */
$GLOBALS['TEST_PATH'] = $GLOBALS['ROOT_PATH']  . 'test/';

/**
 * The absolute path to the test classes folder
 * @global string $GLOBALS['TEST_CLASS_PATH']
 */
$GLOBALS['TEST_CLASS_PATH'] = $GLOBALS['ROOT_PATH']  . 'classes/';

/**
 * The path delimiter to use ('/' should be okay for UNIX and Windows, however, if you have problems we suggest changing it)
 * @global string $GLOBALS['PATH_DELIM']
 */
$GLOBALS['PATH_DELIM'] = '/';

/**
 * The main www folder
 */
$GLOBALS['WWW'] = str_replace("cerenkov/", "", $Config->get("cerenkov.path"));

/*
 * We have to reset the $config_file member variable in the Configuration obect so that fopen() doesn't complain.
 */
$Config->setPath(build_path($GLOBALS['CONFIG_PATH'], 'config.ini.php'));

$GLOBALS['ClassLoaders'] = array();
$GLOBALS['ClassLoader'] = new Autoloader('classes');
// SHOULD ONLY BE  SET IN DEVELOPMENT, REMOVE FOR PRODUCTION RELEASES
$GLOBALS['ClassLoader']->forceReload('classes');

/*
 * Register Plugins
 */
register_loader("plugins", new Autoloader("plugins"));
/**
 * Build the path for a specific directory or file based on the $GLOBAL['PATH_DELIM']
 *
 * @param mixed $argv Various arguments are passed to this function
 * @return string The path of the directory or file
 **/
function build_path(/*...varargs...*/)
{
	$args = func_get_args(); // func_get_args() depends on current scope so can't be used as a function parameter...
	return implode($GLOBALS['PATH_DELIM'], $args);
}

/**
 * Create a new AutoLoader for plugins or extra classes
 *
 * @param string $name The name of the AutoLoader
 * @param Autoloader $loader The Autoloader class to register
 * @return void
 **/
function register_loader($name, Autoloader $loader)
{
	$GLOBALS['ClassLoaders'][$name] = $loader;
}

/**
 * Destroys the an AutoLoader registered with register_loader()
 *
 * @param string $name The name of the AutoLoader
 * @return void
 **/
function unregister_loader($name)
{
	if ( array_key_exists($name, $GLOBALS['ClassLoaders']) )
	{
		$GLOBALS['ClassLoaders'] = array_diff_key($GLOBALS['ClassLoaders'], array($name));
	}
}

/**
 * The built in __autoload() function in PHP. Uses the AutoLoader class to load the require class files
 *
 * @param string $class name The name of the class to be loaded
 * @return void
 **/
function __autoload($class_name)
{
	/**
	* A thought for __autoload()... shouldn't we try and load plugin classes first?
	*/
		
	$success = FALSE;
	try
	{
		$GLOBALS['ClassLoader']->loadClass($class_name); // Try to load from CLASS_PATH first.
		$success = TRUE;
	}
	catch (AutoloaderException $e)
	{
		// The requested class could not be found in the CLASS_PATH
		// Try any registered Autoloader objects instead
		foreach ( $GLOBALS['ClassLoaders'] as $scope => $classLoader )
		{
			try
			{
				$classLoader->loadClass($class_name);
				$success = TRUE;
				break;
			}
			catch ( AutoloaderException $e)
			{
				// Ignore and move on to the next loader
			}
		}
	}
	if ( !$success )
		throw new AutoloaderException('Class not found');
}

/**
 * Force the loading of a class
 * 
 * @param string $scope The name the loader is registered under
 * @return void
 **/
function force_load($scope, $class_name)
{
	// Don't catch the AutoloaderException here
	if ( array_key_exists($scope, $GLOBALS['ClassLoaders']) )
	{
		$GLOBALS['ClassLoaders'][$scope]->loadClass($class_name);
	}
}


/**
 * The global Database Object
 * @global string $GLOBALS['DataBase']
 */
$GLOBALS['DataBase'] = DBFactory::create($Config->get('sql.sql_server'));

/*
 * Some classes have to be loaded after the AutoLoad stuff to ensure that they are found.
 */

try
{
	$GLOBALS['DataBase']->connect($Config->get('sql.DB_NAME'), $Config->get('sql.DB_HOST'), $Config->get('sql.DB_USER'), $Config->get('sql.DB_PASS'));
}
catch(DatabaseException $dbex)
{
	echo $dbex->fullDebug("SQL Error");
}
global $DataBase;
$DataBase = $GLOBALS['DataBase'];

/**
 * The global Session Object
 * @global Session $Session
 */
global $Session;
try {
	if(!isset($_SESSION))
		$Session = new Session(); // Also creates $GLOBALS['User']
} catch(AuthException $aex)
{
	$aex->fullDebug();
}

/**
 * The global User Object User Class. $GLOBALS['User'] is set when the session is created. 
 * @global User $User
 */
global $User;
$User =& $GLOBALS['User'];

/**
 * The global Cache Object
 * @global Cache $cache
 */
$Cache = new Cache(true);
global $cacheid;

?>
