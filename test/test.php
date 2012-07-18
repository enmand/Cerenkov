<?php
/**
 * test.php
 * 
 * @author Bytekill Group
 * @copyright Unerror.com 2007
 * @package test
 */

require_once('TestFramework.php');

$framework = new TestFramework();

$framework->testClasses();

echo "Passed:\n";
foreach ( $framework->listPassed() as $passed )
{
	echo $passed . "\n";
}

echo "Failed:\n";
foreach($framework->listFailed() as $failed )
{
	echo $failed . "\n";
}

?>