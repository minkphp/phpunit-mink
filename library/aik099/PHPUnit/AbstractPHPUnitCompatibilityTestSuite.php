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


use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Version;

if ( \class_exists('PHPUnit\Runner\Version') ) {
	$runner_version = Version::id();
}
else {
	$runner_version = \PHPUnit_Runner_Version::id();
}


/*
 * @codeCoverageIgnore
 */
trait TAbstractPHPUnitCompatibilityTestSuite
{

	/**
	 * Runs the tests and collects their result in a TestResult.
	 *
	 * @param TestResult $result Test result.
	 *
	 * @return TestResult
	 */
	public function runCompatibilized($result = null)
	{
		return parent::run($result);
	}

	/**
	 * Template Method that is called after the tests
	 * of this test suite have finished running.
	 */
	protected function tearDownCompatibilized()
	{

	}

}

if ( version_compare($runner_version, '6.0.0', '<') ) {
	require_once __DIR__ . '/AbstractPHPUnitCompatibilityTestSuite5.php';
}
else {
	require_once __DIR__ . '/AbstractPHPUnitCompatibilityTestSuite7.php';
}
