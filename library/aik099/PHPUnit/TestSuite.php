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


use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;


/**
 * TestSuite class for Mink tests.
 */
class TestSuite extends TestSuiteBase
{

	/**
	 * Creating TestSuite from given class.
	 *
	 * @param string $class_name Descendant of TestCase class.
	 *
	 * @return self
	 */
	public static function fromTestCaseClass($class_name)
	{
		$suite = new static();
		$suite->setName($class_name);

		$class = new \ReflectionClass($class_name);
		$static_properties = $class->getStaticProperties();

		$session_strategy_manager = SessionStrategyManager::getInstance();

		// create tests from test methods for multiple browsers
		if ( !empty($static_properties['browsers']) ) {
			foreach ($static_properties['browsers'] as $browser) {
				$suite->addTest(static::createBrowserSuite($class, $browser, $session_strategy_manager));
			}
		}
		else {
			// create tests from test methods for single browser
			$suite->addTestMethods($class, $session_strategy_manager);
		}

		return $suite;
	}

	/**
	 * Creates browser suite.
	 *
	 * @param \ReflectionClass       $class                    Class.
	 * @param array                  $browser                  Browser configuration.
	 * @param SessionStrategyManager $session_strategy_manager Session strategy manager.
	 *
	 * @return BrowserSuite
	 */
	protected static function createBrowserSuite(\ReflectionClass $class, array $browser, SessionStrategyManager $session_strategy_manager)
	{
		$suite = BrowserSuite::fromClassAndBrowser($class->name, $browser);

		$suite->addTestMethods($class, $session_strategy_manager);
		$suite->setupSpecificBrowser($browser);

		return $suite;
	}

	/**
	 * Template Method that is called after the tests of this test suite have finished running.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		foreach ($this->tests() as $test) {
			if ( $test instanceof BrowserTestCase ) {
				$test->endOfTestCase();
			}
		}
	}

}
