<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit;


use PHPUnit\Runner\Version;

if ( \class_exists('\PHPUnit_Framework_IncompleteTestError') ) {
	\class_alias('\PHPUnit_Framework_IncompleteTestError', '\aik099\PHPUnit\Framework\IncompleteTestError');
}
else {
	\class_alias('\PHPUnit\Framework\IncompleteTestError', '\aik099\PHPUnit\Framework\IncompleteTestError');
}

if ( class_exists('\PHPUnit_Framework_SkippedTestError') ) {
	\class_alias('\PHPUnit_Framework_SkippedTestError', '\aik099\PHPUnit\Framework\SkippedTestError');
}
else {
	\class_alias('\PHPUnit\Framework\SkippedTestError', '\aik099\PHPUnit\Framework\SkippedTestError');
}

if ( class_exists('\PHPUnit_Framework_TestSuite_DataProvider') ) {
	\class_alias('\PHPUnit_Framework_TestSuite_DataProvider', '\aik099\PHPUnit\Framework\DataProviderTestSuite');
}
else {
	\class_alias('\PHPUnit\Framework\DataProviderTestSuite', '\aik099\PHPUnit\Framework\DataProviderTestSuite');
}

if ( class_exists('\PHPUnit_Framework_TestResult') ) {
	\class_alias('\PHPUnit_Framework_TestResult', '\aik099\PHPUnit\Framework\TestResult');
}
else {
	\class_alias('\PHPUnit\Framework\TestResult', '\aik099\PHPUnit\Framework\TestResult');
}

if ( class_exists('\PHPUnit_Framework_Test') ) {
	\class_alias('\PHPUnit_Framework_Test', '\aik099\PHPUnit\Framework\Test');
}
else {
	\class_alias('\PHPUnit\Framework\Test', '\aik099\PHPUnit\Framework\Test');
}

if ( class_exists('\PHP_CodeCoverage') ) {
	\class_alias('\PHP_CodeCoverage', '\aik099\SebastianBergmann\CodeCoverage\CodeCoverage');
}
else {
	\class_alias('\SebastianBergmann\CodeCoverage\CodeCoverage', '\aik099\SebastianBergmann\CodeCoverage\CodeCoverage');
}

if ( \interface_exists('\PHP_CodeCoverage_Driver') ) {
	\class_alias('\PHP_CodeCoverage_Driver', '\aik099\SebastianBergmann\CodeCoverage\Driver\Driver');
}
else {
	\class_alias('\SebastianBergmann\CodeCoverage\Driver\Driver', '\aik099\SebastianBergmann\CodeCoverage\Driver\Driver');
}

if ( class_exists('\PHP_CodeCoverage_Filter') ) {
	\class_alias('\PHP_CodeCoverage_Filter', '\aik099\SebastianBergmann\CodeCoverage\Filter');
}
else {
	\class_alias('\SebastianBergmann\CodeCoverage\Filter', '\aik099\SebastianBergmann\CodeCoverage\Filter');
}

if ( \class_exists('PHPUnit\Runner\Version') ) {
	$runner_version = Version::id();
}
else {
	$runner_version = \PHPUnit_Runner_Version::id();
}


/**
 * @codeCoverageIgnore
 */
trait TAbstractPHPUnitCompatibilityTestCase
{

	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param \Exception|\Throwable $e Exception.
	 *
	 * @return void
	 */
	protected function onNotSuccessfulTestCompatibilized($e)
	{

	}

	/**
	 * @after
	 */
	protected function verifyMockeryExpectations()
	{
		if ( \class_exists('Mockery') ) {
			// Add Mockery expectations to assertion count.
			if ( ($container = \Mockery::getContainer()) !== null ) {
				$this->addToAssertionCount($container->mockery_getExpectationCount());
			}

			// Verify Mockery expectations.
			\Mockery::close();
		}
	}

}

if ( version_compare($runner_version, '6.0.0', '<') ) {
	require_once __DIR__ . '/AbstractPHPUnitCompatibilityTestCase5.php';
}
else {
	require_once __DIR__ . '/AbstractPHPUnitCompatibilityTestCase7.php';
}
