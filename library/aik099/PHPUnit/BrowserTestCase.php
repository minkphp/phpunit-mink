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
use aik099\PHPUnit\Session\ISessionStrategy;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session;
use ConsoleHelpers\PHPUnitCompat\AbstractTestCase;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;

/**
 * Test Case class for writing browser-based tests.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
abstract class BrowserTestCase extends AbstractTestCase
{

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array();

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
	private $_sessionStrategyManager;

	/**
	 * Remote coverage helper.
	 *
	 * @var RemoteCoverageHelper
	 */
	private $_remoteCoverageHelper;

	/**
	 * Session strategy, used currently.
	 *
	 * @var ISessionStrategy
	 */
	private $_sessionStrategy;

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
	 * Sets session strategy manager.
	 *
	 * @param SessionStrategyManager $session_strategy_manager Session strategy manager.
	 *
	 * @return self
	 */
	public function setSessionStrategyManager(SessionStrategyManager $session_strategy_manager)
	{
		$this->_sessionStrategyManager = $session_strategy_manager;

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
		$this->_remoteCoverageHelper = $remote_coverage_helper;
	}

	/**
	 * Sets base url for remote coverage information collection.
	 *
	 * @param string $url URL.
	 *
	 * @return void
	 */
	protected function setRemoteCoverageScriptUrl($url)
	{
		$this->_remoteCoverageScriptUrl = $url;
	}

	/**
	 * Set session meta-info for an API-based browser configurations.
	 *
	 * @return void
	 * @before
	 */
	protected function setUpTest()
	{
		if ( $this->_browser !== null ) {
			$this->_browser->onTestSetup($this);
		}
	}

	/**
	 * Sets browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return self
	 */
	protected function setBrowser(BrowserConfiguration $browser)
	{
		$this->_browser = $browser;

		// Configure session strategy.
		$this->setSessionStrategy(
			$this->_sessionStrategyManager->getSessionStrategy($browser, $this)
		);

		return $this;
	}

	/**
	 * Returns browser configuration.
	 *
	 * @return BrowserConfiguration
	 * @throws \RuntimeException When browser configuration isn't defined.
	 */
	protected function getBrowser()
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
		return $this->setBrowser(
			$this->createBrowserConfiguration($browser_config)
		);
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
	protected function setSessionStrategy(ISessionStrategy $session_strategy = null)
	{
		$this->_sessionStrategy = $session_strategy;

		return $this;
	}

	/**
	 * Returns session strategy used currently.
	 *
	 * @return ISessionStrategy
	 * @see    setSessionStrategy()
	 */
	protected function getSessionStrategy()
	{
		if ( $this->_sessionStrategy !== null ) {
			return $this->_sessionStrategy;
		}

		// Default session strategy (not session itself) shared across all test cases.
		return $this->_sessionStrategyManager->getDefaultSessionStrategy();
	}

	/**
	 * Creates Mink session using current session strategy and returns it.
	 *
	 * @param boolean $auto_create Automatically create session, when missing.
	 *
	 * @return Session
	 */
	public function getSession($auto_create = true)
	{
		if ( $this->_session || $auto_create === false ) {
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
	 * Collects remote coverage information and notifies strategy/browser about test finish.
	 *
	 * @after
	 */
	protected function tearDownTest()
	{
		$result = $this->getTestResultObject();

		if ( $this->getCollectCodeCoverageInformation() ) {
			$result->getCodeCoverage()->append($this->getRemoteCodeCoverageInformation(), $this);
		}

		if ( $this->_browser !== null ) {
			$this->_browser->onTestEnded($this, $result);
		}

		if ( $this->_sessionStrategy !== null ) {
			$this->_sessionStrategy->onTestEnded($this);
		}
	}

	/**
	 * Whatever or not code coverage information should be gathered.
	 *
	 * @return boolean
	 * @throws \RuntimeException When used before test is started.
	 */
	protected function getCollectCodeCoverageInformation()
	{
		if ( !$this->_remoteCoverageScriptUrl ) {
			return false;
		}

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
		if ( $this->_sessionStrategy !== null ) {
			$this->_sessionStrategy->onTestSuiteEnded($this);
		}

		return $this;
	}

	/**
	 * Returns remote code coverage information, when enabled.
	 *
	 * @return array|RawCodeCoverageData
	 */
	protected function getRemoteCodeCoverageInformation()
	{
		return $this->_remoteCoverageHelper->get($this->_remoteCoverageScriptUrl, $this->_testId);
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
	 * @inheritDoc
	 */
	protected function onNotSuccessfulTestCompat($e)
	{
		if ( $this->_sessionStrategy !== null ) {
			$this->_sessionStrategy->onTestFailed($this, $e);
		}
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
