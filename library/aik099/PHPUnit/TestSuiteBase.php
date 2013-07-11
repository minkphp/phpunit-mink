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
 * Base Test Suite class for browser tests.
 *
 * @method \Mockery\Expectation shouldReceive
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
	 * @param \ReflectionClass       $class                    Class reflection.
	 * @param SessionStrategyManager $session_strategy_manager Session strategy manager.
	 *
	 * @return self
	 */
	public function addTestMethods(\ReflectionClass $class, SessionStrategyManager $session_strategy_manager)
	{
		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$this->addTestMethod($class, $method);
		}

		$this->setSessionStrategyManager($session_strategy_manager);

		return $this;
	}

	/**
	 * Sets session strategy manager.
	 *
	 * @param SessionStrategyManager $session_strategy_manager Session strategy manager.
	 *
	 * @return self
	 */
	public function setSessionStrategyManager(SessionStrategyManager $session_strategy_manager)
	{
		/* @var $test \aik099\PHPUnit\BrowserTestCase */
		foreach ( $this->tests() as $test ) {
			$test->setSessionStrategyManager($session_strategy_manager);
		}

		return $this;
	}

	/**
	 * Report back suite ending to each it's test.
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

	/**
	 * Indicates end of the test suite.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function endOfTestCase()
	{
		// method created just to simplify tearDown method
	}

}
