<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\TestSuite;


use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\IEventDispatcherAware;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\Session\SessionStrategyManager;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base Test Suite class for browser tests.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
abstract class AbstractTestSuite extends \PHPUnit_Framework_TestSuite implements IEventDispatcherAware
{

	/**
	 * Event dispatcher.
	 *
	 * @var EventDispatcherInterface
	 */
	private $_eventDispatcher;

	/**
	 * Overriding the default: Selenium suites are always built from a TestCase class.
	 *
	 * @var boolean
	 */
	protected $testCase = true;

	/**
	 * Sets event dispatcher.
	 *
	 * @param EventDispatcherInterface $event_dispatcher Event dispatcher.
	 *
	 * @return void
	 */
	public function setEventDispatcher(EventDispatcherInterface $event_dispatcher)
	{
		$this->_eventDispatcher = $event_dispatcher;
	}

	/**
	 * Adds test methods to the suite.
	 *
	 * @param string $class_name Test case class name.
	 *
	 * @return self
	 */
	public function addTestMethods($class_name)
	{
		$class = new \ReflectionClass($class_name);

		foreach ( $class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method ) {
			$this->addTestMethod($class, $method);
		}

		return $this;
	}

	/**
	 * Sets session strategy manager recursively to all tests.
	 *
	 * @param SessionStrategyManager       $session_strategy_manager      Session strategy manager.
	 * @param IBrowserConfigurationFactory $browser_configuration_factory Browser configuration factory.
	 * @param RemoteCoverageHelper         $remote_coverage_helper        Remote coverage helper.
	 * @param array                        $tests                         Tests to process.
	 *
	 * @return self
	 */
	public function setTestDependencies(
		SessionStrategyManager $session_strategy_manager,
		IBrowserConfigurationFactory $browser_configuration_factory,
		RemoteCoverageHelper $remote_coverage_helper,
		array $tests = null
	) {
		if ( !isset($tests) ) {
			$tests = $this->tests();
		}

		foreach ( $tests as $test ) {
			if ( $test instanceof \PHPUnit_Framework_TestSuite_DataProvider ) {
				$this->setTestDependencies(
					$session_strategy_manager,
					$browser_configuration_factory,
					$remote_coverage_helper,
					$test->tests()
				);
			}
			else {
				/** @var BrowserTestCase $test */
				$test->setEventDispatcher($this->_eventDispatcher);
				$test->setSessionStrategyManager($session_strategy_manager);
				$test->setBrowserConfigurationFactory($browser_configuration_factory);
				$test->setRemoteCoverageHelper($remote_coverage_helper);
			}
		}

		return $this;
	}

	/**
	 * Report back suite ending to each it's test.
	 *
	 * @param array $tests Tests to process.
	 *
	 * @return void
	 */
	protected function tearDown(array $tests = null)
	{
		if ( !isset($tests) ) {
			$tests = $this->tests();
		}

		foreach ( $tests as $test ) {
			if ( $test instanceof \PHPUnit_Framework_TestSuite_DataProvider ) {
				$this->tearDown($test->tests());
			}
			else {
				/* @var $test BrowserTestCase */
				$test->onTestSuiteEnded();
			}
		}
	}

	/**
	 * Indicates end of the test suite.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function onTestSuiteEnded()
	{
		// Method created just to simplify tearDown method.
	}

}
