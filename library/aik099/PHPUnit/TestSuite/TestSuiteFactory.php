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
use aik099\PHPUnit\IApplicationAware;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\Application;

/**
 * Creates test suites based on test case class configuration.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class TestSuiteFactory implements IApplicationAware
{

	/**
	 * Session strategy manager.
	 *
	 * @var SessionStrategyManager
	 */
	private $_sessionStrategyManager;

	/**
	 * Application.
	 *
	 * @var Application
	 */
	protected $application;

	/**
	 * Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory
	 */
	private $_browserConfigurationFactory;

	/**
	 * Remote coverage helper.
	 *
	 * @var RemoteCoverageHelper
	 */
	private $_remoteCoverageHelper;

	/**
	 * Creates test suite builder instance.
	 *
	 * @param SessionStrategyManager       $session_strategy_manager      Session strategy manager.
	 * @param IBrowserConfigurationFactory $browser_configuration_factory Browser configuration factory.
	 * @param RemoteCoverageHelper         $remote_coverage_helper        Remote coverage helper.
	 */
	public function __construct(
		SessionStrategyManager $session_strategy_manager,
		IBrowserConfigurationFactory $browser_configuration_factory,
		RemoteCoverageHelper $remote_coverage_helper
	) {
		$this->_sessionStrategyManager = $session_strategy_manager;
		$this->_browserConfigurationFactory = $browser_configuration_factory;
		$this->_remoteCoverageHelper = $remote_coverage_helper;
	}

	/**
	 * Sets application.
	 *
	 * @param Application $application The application.
	 *
	 * @return void
	 */
	public function setApplication(Application $application)
	{
		$this->application = $application;
	}

	/**
	 * Creates test suite based on given test case class.
	 *
	 * @param string $class_name Test case class name.
	 *
	 * @return AbstractTestSuite
	 */
	public function createSuiteFromTestCase($class_name)
	{
		/** @var RegularTestSuite $suite */
		$suite = $this->application->getObject('regular_test_suite');
		$suite->setName($class_name);

		$browsers = $this->_getBrowsers($class_name);

		if ( $browsers ) {
			// Create tests from test methods for multiple browsers.
			foreach ( $browsers as $browser ) {
				$suite->addTest($this->_createBrowserSuite($class_name, $browser));
			}
		}
		else {
			// Create tests from test methods for single browser.
			$suite->addTestMethods($class_name);
			$suite->setTestDependencies(
				$this->_sessionStrategyManager,
				$this->_browserConfigurationFactory,
				$this->_remoteCoverageHelper
			);
		}

		return $suite;
	}

	/**
	 * Returns browser configuration of a class.
	 *
	 * @param string $class_name Test case class name.
	 *
	 * @return array
	 */
	private function _getBrowsers($class_name)
	{
		$class = new \ReflectionClass($class_name);
		$static_properties = $class->getStaticProperties();

		return !empty($static_properties['browsers']) ? $static_properties['browsers'] : array();
	}

	/**
	 * Creates browser suite.
	 *
	 * @param string $class_name Descendant of TestCase class.
	 * @param array  $browser    Browser configuration.
	 *
	 * @return BrowserTestSuite
	 */
	private function _createBrowserSuite($class_name, array $browser)
	{
		/** @var BrowserTestSuite $suite */
		$suite = $this->application->getObject('browser_test_suite');
		$suite->setName($class_name . ': ' . $suite->nameFromBrowser($browser));

		$suite->addTestMethods($class_name);
		$suite->setTestDependencies(
			$this->_sessionStrategyManager,
			$this->_browserConfigurationFactory,
			$this->_remoteCoverageHelper
		);
		$suite->setBrowserFromConfiguration($browser);

		return $suite;
	}

}
