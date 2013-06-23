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


/**
 * TestSuite class for Mink tests.
 */
abstract class TestSuiteBase extends \PHPUnit_Framework_TestSuite
{

	/**
	 * Overriding the default: Selenium suites are always built from a TestCase class.
	 *
	 * @var boolean
	 */
	protected $testCase = true;

	/**
	 * Adds test methods to the suite.
	 *
	 * @param \ReflectionClass $class Class reflection.
	 *
	 * @return self
	 */
	public function addTestMethods(\ReflectionClass $class)
	{
		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$this->addTestMethod($class, $method);
		}

		return $this;
	}

}
