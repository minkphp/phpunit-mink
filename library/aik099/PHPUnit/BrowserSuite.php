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
 * Test Suite class for a set of tests from a single Test Case Class executed with a particular browser.
 */
class BrowserSuite extends TestSuiteBase
{

	/**
	 * Create test suite based on given class name on browser configuration.
	 *
	 * @param string $class_name Class name.
	 * @param array  $browser    Browser configuration.
	 *
	 * @return self
	 */
	public static function fromClassAndBrowser($class_name, array $browser)
	{
		$suite = new static();

		$name = 'undefined';
		$try_settings = array('alias', 'browserName', 'name');

		foreach ($try_settings as $try_setting) {
			if ( isset($browser[$try_setting]) ) {
				$name = $browser[$try_setting];
				break;
			}
		}

		$suite->setName($class_name . ': ' . $name);

		return $suite;
	}

	/**
	 * Sets given browser to be used in each underlying test cases and test suites.
	 *
	 * @param array $browser Browser configuration.
	 *
	 * @return self
	 */
	public function setupSpecificBrowser(array $browser)
	{
		/* @var $test BrowserTestCase */

		foreach ( $this->tests() as $test ) {
			$test->setupSpecificBrowser($browser);
		}

		return $this;
	}

	/**
	 * Template Method that is called after the tests of this test suite have finished running.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		/* @var $test BrowserTestCase */

		foreach ( $this->tests() as $test ) {
			$test->endOfTestCase();
		}
	}

}
