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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageTool;
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\Event\TestFailedEvent;
use aik099\PHPUnit\Session\ISessionStrategy;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session;
use PHPUnit\Framework\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test Case class for writing browser-based tests.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
abstract class BrowserTestCase extends AbstractPHPUnitCompatibilityTestCase implements IEventDispatcherAware
{

	const TEST_ENDED_EVENT = 'test.ended';

	const TEST_SUITE_ENDED_EVENT = 'test_suite.ended';

	const TEST_FAILED_EVENT = 'test.failed';

	const TEST_SETUP_EVENT = 'test.setup';

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array();

	/**
	 * Event dispatcher.
	 *
	 * @var EventDispatcherInterface
	 */
	private $_eventDispatcher;

	/**
	 * Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory
	 */
	private $_browserConfigurationFactory;

	/**
	 * Remote coverage collection url.
	 *
	 * @var string Override to provide code coverage data from the server
	 */
	private $_remoteCoverageScriptUrl;

	/**
	 * Current browser configuration.
	 *
	 * @var BrowserConfiguration
	 */
	private $_browser;

	/**
	 * Reference to Mink session.
	 *
	 * @var Session
	 */
	private $_session;

	/**
	 * Session strategy manager.
	 *
	 * @var SessionStrategyManager
	 */
	protected $sessionStrategyManager;

	/**
	 * Remote coverage helper.
	 *
	 * @var RemoteCoverageHelper
	 */
	protected $remoteCoverageHelper;

	/**
	 * Session strategy, used currently.
	 *
	 * @var ISessionStrategy
	 */
	protected $sessionStrategy;

	/**
	 * Test ID.
	 *
	 * @var string
	 */
	private $_testId;

	/**
	 * Sets application.
	 *
	 * @param IBrowserConfigurationFactory $browser_configuration_factory Browser configuration factory.
	 *
	 * @return void
	 */
	public function setBrowserConfigurationFactory(IBrowserConfigurationFactory $browser_configuration_factory)
	{
		$this->_browserConfigurationFactory = $browser_configuration_factory;
	}

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
	 * Sets session strategy manager.
	 *
	 * @param SessionStrategyManager $session_strategy_manager Session strategy manager.
	 *
	 * @return self
	 */
	public function setSessionStrategyManager(SessionStrategyManager $session_strategy_manager)
	{
		$this->sessionStrategyManager = $session_strategy_manager;

		return $this;
	}

	/**
	 * Sets remote coverage helper.
	 *
	 * @param RemoteCoverageHelper $remote_coverage_helper Remote coverage helper.
	 *
	 * @return void
	 */
	public function setRemoteCoverageHelper(RemoteCoverageHelper $remote_coverage_helper)
	{
		$this->remoteCoverageHelper = $remote_coverage_helper;
	}

	/**
	 * Sets base url for remote coverage information collection.
	 *
	 * @param string $url URL.
	 *
	 * @return void
	 */
	public function setRemoteCoverageScriptUrl($url)
	{
		$this->_remoteCoverageScriptUrl = $url;
	}

	/**
	 * Set session meta-info for "Sauce Labs".
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_eventDispatcher->dispatch(
			self::TEST_SETUP_EVENT,
			new TestEvent($this)
		);
	}

	/**
	 * Sets browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return self
	 */
	public function setBrowser(BrowserConfiguration $browser)
	{
		$this->_browser = $browser->attachToTestCase($this);

		// Configure session strategy.
		return $this->setSessionStrategy($this->sessionStrategyManager->getSessionStrategy($browser));
	}

	/**
	 * Returns browser configuration.
	 *
	 * @return BrowserConfiguration
	 * @throws \RuntimeException When browser configuration isn't defined.
	 */
	public function getBrowser()
	{
		if ( !is_object($this->_browser) ) {
			throw new \RuntimeException('Browser configuration not defined');
		}

		return $this->_browser;
	}

	/**
	 * Initializes a browser with given configuration.
	 *
	 * @param array $browser_config Browser configuration.
	 *
	 * @return self
	 */
	public function setBrowserFromConfiguration(array $browser_config)
	{
		return $this->setBrowser($this->createBrowserConfiguration($browser_config));
	}

	/**
	 * Returns browser configuration instance.
	 *
	 * @param array $browser_config Browser.
	 *
	 * @return BrowserConfiguration
	 */
	protected function createBrowserConfiguration(array $browser_config)
	{
		return $this->_browserConfigurationFactory->createBrowserConfiguration($browser_config, $this);
	}

	/**
	 * Sets session strategy.
	 *
	 * @param ISessionStrategy $session_strategy Session strategy.
	 *
	 * @return self
	 */
	public function setSessionStrategy(ISessionStrategy $session_strategy = null)
	{
		$this->sessionStrategy = $session_strategy;

		return $this;
	}

	/**
	 * Returns session strategy used currently.
	 *
	 * @return ISessionStrategy
	 * @see    setSessionStrategy()
	 */
	public function getSessionStrategy()
	{
		if ( $this->sessionStrategy ) {
			return $this->sessionStrategy;
		}

		// Default session strategy (not session itself) shared across all test cases.
		return $this->sessionStrategyManager->getDefaultSessionStrategy();
	}

	/**
	 * Creates Mink session using current session strategy and returns it.
	 *
	 * @return Session
	 */
	public function getSession()
	{
		if ( $this->_session ) {
			return $this->_session;
		}

		$browser = $this->getBrowser();

		try {
			$this->_session = $this->getSessionStrategy()->session($browser);

			if ( $this->getCollectCodeCoverageInformation() ) {
				$this->_session->visit($browser->getBaseUrl());
			}
		}
		catch ( DriverException $e ) {
			$message = 'The Selenium Server is not active on host %s at port %s';
			$this->markTestSkipped(sprintf($message, $browser->getHost(), $browser->getPort()));
		}

		return $this->_session;
	}

	/**
	 * Runs the test case and collects the results in a TestResult object.
	 *
	 * If no TestResult object is passed a new one will be created.
	 *
	 * @param TestResult $result Test result.
	 *
	 * @return TestResult
	 */
	public function run(TestResult $result = null)
	{
		if ( $result === null ) {
			$result = $this->createResult();
		}

		parent::run($result);

		if ( $result->getCollectCodeCoverageInformation() ) {
			$result->getCodeCoverage()->append($this->getRemoteCodeCoverageInformation(), $this);
		}

		/*$this->setTestResultObject($result);*/

		// Do not call this before to give the time to the Listeners to run.
		$this->_eventDispatcher->dispatch(
			self::TEST_ENDED_EVENT,
			new TestEndedEvent($this, $result, $this->_session)
		);

		/*$this->setTestResultObject(null);*/

		return $result;
	}

	/**
	 * Whatever or not code coverage information should be gathered.
	 *
	 * @return boolean
	 * @throws \RuntimeException When used before test is started.
	 */
	public function getCollectCodeCoverageInformation()
	{
		$result = $this->getTestResultObject();

		if ( !is_object($result) ) {
			throw new \RuntimeException('Test must be started before attempting to collect coverage information');
		}

		return $result->getCollectCodeCoverageInformation();
	}

	/**
	 * Override to tell remote website, that code coverage information needs to be collected.
	 *
	 * @return mixed
	 */
	protected function runTest()
	{
		if ( $this->getCollectCodeCoverageInformation() ) {
			$this->_testId = get_class($this) . '__' . $this->getName();

			$session = $this->getSession();
			$session->setCookie(RemoteCoverageTool::TEST_ID_VARIABLE, null);
			$session->setCookie(RemoteCoverageTool::TEST_ID_VARIABLE, $this->_testId);
		}

		return parent::runTest();
	}

	/**
	 * Called, when last test in a test case has ended.
	 *
	 * @return self
	 */
	public function onTestSuiteEnded()
	{
		$this->_eventDispatcher->dispatch(
			self::TEST_SUITE_ENDED_EVENT,
			new TestEvent($this, $this->_session)
		);

		return $this;
	}

	/**
	 * Returns remote code coverage information, when enabled.
	 *
	 * @return array
	 */
	public function getRemoteCodeCoverageInformation()
	{
		if ( $this->_remoteCoverageScriptUrl ) {
			return $this->remoteCoverageHelper->get($this->_remoteCoverageScriptUrl, $this->_testId);
		}

		return array();
	}

	/**
	 * Creates test suite for usage with Mink.
	 *
	 * @param string $class_name Test case class name.
	 *
	 * @return RegularTestSuite
	 */
	public static function suite($class_name)
	{
		$application = Application::getInstance();

		return $application->getTestSuiteFactory()->createSuiteFromTestCase($class_name);
	}

	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param \Throwable $e Exception.
	 *
	 * @return void
	 */
	protected function onNotSuccessfulTestCompatibilized(\Throwable $e)
	{
		$this->_eventDispatcher->dispatch(
			self::TEST_FAILED_EVENT,
			new TestFailedEvent($e, $this, $this->_session)
		);
	}

	/**
	 * Get test id (generated internally).
	 *
	 * @return string
	 */
	public function getTestId()
	{
		return $this->_testId;
	}

	/**
	 * Gets browser configuration aliases.
	 *
	 * Allows to decouple actual test server connection details from test cases.
	 *
	 * @return array
	 */
	public function getBrowserAliases()
	{
		return array();
	}

}
