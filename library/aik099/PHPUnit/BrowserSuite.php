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


use aik099\PHPUnit\SessionStrategy\ISessionStrategy;

/**
 * TestSuite class for a set of tests from a single TestCase Class executed with a particular browser.
 */
class BrowserSuite extends \PHPUnit_Framework_TestSuite
{

	/**
	 * Overriding the default: Mink suites are always built from a TestCase class.
	 *
	 * @var boolean
	 */
	protected $testCase = true;

	/**
	 * Session strategy, used currently.
	 *
	 * @var ISessionStrategy
	 * @access protected
	 */
	protected $localSessionStrategy;

	/**
	 * Override to make public.
	 *
	 * @param \ReflectionClass  $class  Class.
	 * @param \ReflectionMethod $method Method.
	 *
	 * @return void
	 * @access public
	 * @see    TestSuite::fromTestCaseClass
	 */
	public function addTestMethod(\ReflectionClass $class, \ReflectionMethod $method)
	{
		parent::addTestMethod($class, $method);
	}

	/**
	 * Create test suite based on given class name on browser configuration.
	 *
	 * @param string $class_name Class name.
	 * @param array  $browser    Browser configuration.
	 *
	 * @return BrowserSuite
	 * @access public
	 */
	public static function fromClassAndBrowser($class_name, array $browser)
	{
		$browser_suite = new self();

		$name = 'undefined';
		$try_settings = array('alias', 'browserName', 'name', 'browser');

		foreach ($try_settings as $try_setting) {
			if ( isset($browser[$try_setting]) ) {
				$name = $browser[$try_setting];
				break;
			}
		}

		$browser_suite->setName($class_name . ': ' . $name);

		return $browser_suite;
	}

	/**
	 * Sets given browser to be used in each underlying test cases and test suites.
	 *
	 * @param array $browser Browser configuration.
	 *
	 * @return void
	 * @access public
	 */
	public function setupSpecificBrowser(array $browser)
	{
		$this->_browserOnAllTests($this, $browser);
	}

	/**
	 * Changes browser configuration recursively in given test suite.
	 *
	 * @param \PHPUnit_Framework_TestSuite $suite   Test suite.
	 * @param array                        $browser Browser configuration.
	 *
	 * @return void
	 * @access private
	 */
	private function _browserOnAllTests(\PHPUnit_Framework_TestSuite $suite, array $browser)
	{
		foreach ($suite->tests() as $test) {
			if ( $test instanceof \PHPUnit_Framework_TestSuite ) {
				// could be TestSuite or BrowserSuite
				$this->_browserOnAllTests($test, $browser);
			}
			else {
				/* @var $test BrowserTestCase */
				$test->setupSpecificBrowser($browser);
			}
		}
	}

	/**
	 * Template Method that is called after the tests of this test suite have finished running.
	 *
	 * @return void
	 * @access protected
	 */
	protected function tearDown()
	{
		/* @var $test BrowserTestCase */

		foreach ($this->tests() as $test) {
			$test->endOfTestCase();
		}
	}

}
