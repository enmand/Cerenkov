<?php
/**
 * TestFramework.php
 *
 * @author Bytekill Group
 * @copyright Unerror.com 2007
 * @package test
 */

require_once('../system/global.php');

/**
 * @package test
 */
class TestFramework
{
	private $testLoader;
	private $passed;
	private $failed;

	public function __construct()
	{
		//$this->testLoader = new Autoloader($_GLOBALS['TEST_CLASS_PATH']);
		$passed = array();
		$failed = array();
	}

	public function testClass($class_name)
	{
		$this->testLoader->loadClass($class_name);
		$instance = new $class_name();
		$instance->runTest();
		if ( $instance->testResult() == true )
			$passed[] = $class_name;
		else
			$failed[] = $class_name;
	}

	public function testClasses()
	{
		$class_list = $this->testLoader->listClasses();
		foreach ( $class_list as $class_name )
		{
			//$this->testClass($class_name);
		}
	}

	public function listPassed()
	{
		return $this->passed;
	}

	public function listFailed()
	{
		return $this->failed;
	}

}

?>