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
class TestSuite extends \PHPUnit_Framework_TestSuite
{

	/**
	 * Overriding the default: Selenium suites are always built from a TestCase class.
	 *
	 * @var boolean
	 */
	protected $testCase = true;

	/**
	 * Override to make public.
	 *
	 * @param \ReflectionClass  $class  Class reflection.
	 * @param \ReflectionMethod $method Method reflection.
	 *
	 * @return void
	 * @access public
	 */
	public function addTestMethod(\ReflectionClass $class, \ReflectionMethod $method)
	{
		parent::addTestMethod($class, $method);
	}

	/**
	 * Creating TestSuite from given class.
	 *
	 * @param string $class_name Descendant of TestCase class.
	 *
	 * @return TestSuite
	 * @access public
	 */
	public static function fromTestCaseClass($class_name)
	{
		$suite = new self();
		$suite->setName($class_name);

		$class = new \ReflectionClass($class_name);
		$static_properties = $class->getStaticProperties();

		// create tests from test methods for multiple browsers
		if ( !empty($static_properties['browsers']) ) {
			foreach ($static_properties['browsers'] as $browser) {
				$browser_suite = BrowserSuite::fromClassAndBrowser($class_name, $browser);

				foreach ($class->getMethods() as $method) {
					$browser_suite->addTestMethod($class, $method);
				}

				$browser_suite->setupSpecificBrowser($browser);

				$suite->addTest($browser_suite);
			}
		}
		else {
			// create tests from test methods for single browser
			foreach ($class->getMethods() as $method) {
				$suite->addTestMethod($class, $method);
			}
		}

		return $suite;
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
			if ( $test instanceof \PHPUnit_Framework_TestCase ) {
				$test->endOfTestCase();
			}
		}
	}

}
