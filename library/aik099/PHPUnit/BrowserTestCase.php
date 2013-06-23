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


use Behat\Mink\Session,
	Behat\Mink\Exception\DriverException,
	aik099\PHPUnit\SessionStrategy\ISessionStrategy,
	aik099\PHPUnit\SessionStrategy\SharedSessionStrategy,
	aik099\PHPUnit\SessionStrategy\IsolatedSessionStrategy,
	aik099\PHPUnit\Common\RemoteCoverage,
	WebDriver\SauceLabs\SauceRest,
	WebDriver\SauceLabs\Capability as SauceLabsCapability;

/**
 * TestCase class that uses Mink to provide the functionality required for web testing.
 */
abstract class BrowserTestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * Strategy, that create new session for each test in a test case.
	 */
	const SESSION_STRATEGY_ISOLATED = 'isolated';

	/**
	 * Strategy, that allows to share session across all tests in a single test case.
	 */
	const SESSION_STRATEGY_SHARED = 'shared';

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 * @access public
	 */
	public static $browsers = array();

	/**
	 * Remote coverage collection url.
	 *
	 * @var string Override to provide code coverage data from the server
	 * @access protected
	 */
	protected $coverageScriptUrl;

	/**
	 * Reference to Mink session.
	 *
	 * @var Session
	 * @access private
	 */
	private $_session;

	/**
	 * Current browser configuration.
	 *
	 * @var array
	 * @access private
	 */
	private $_parameters;

	/**
	 * Session strategy, requested by test case (in setUpBeforeClass method).
	 *
	 * @var ISessionStrategy
	 * @access protected
	 */
	protected static $sessionStrategy;

	/**
	 * Session strategy, that was requested in browser configuration.
	 *
	 * @var array|ISessionStrategy[]
	 * @access protected
	 */
	protected static $sessionStrategiesInUse = array();

	/**
	 * Browser configuration used in last executed test.
	 *
	 * @var array
	 * @access private
	 */
	private static $_lastUsedSessionStrategyHash;

	/**
	 * Session strategy, used currently.
	 *
	 * @var ISessionStrategy
	 * @access protected
	 */
	protected $localSessionStrategy;

	/**
	 * Test ID.
	 *
	 * @var string
	 * @access private
	 */
	private $_testId;

	/**
	 * Whatever or not code coverage information should be gathered.
	 *
	 * @var boolean
	 * @access private
	 */
	private $_collectCodeCoverageInformation;

	/**
	 * Constructs a test case with the given name.
	 *
	 * @param string $name     Test case name.
	 * @param array  $data     Data.
	 * @param string $dataName Data name.
	 *
	 * @access public
	 */
	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->_parameters = array(
			'host' => 'localhost',
			'port' => 4444,
			'browser' => null,
			'browserName' => null,
			'desiredCapabilities' => array(),
			'seleniumServerRequestsTimeout' => 60,
			'baseUrl' => '',
			'sauce' => array(),
		);
	}

	/**
	 * Changes if a shared session should be used across multiple tests in each test cases.
	 *
	 * To be called from bootstrap.
	 *
	 * @param boolean $share_session Share or not the session.
	 *
	 * @return void
	 * @access public
	 * @throws \InvalidArgumentException When incorrect argument is given.
	 * @link http://phpunit.de/manual/3.7/en/selenium.html
	 */
	public static function shareSession($share_session)
	{
		if ( !is_bool($share_session) ) {
			throw new \InvalidArgumentException('The shared session support can only be switched on or off.');
		}

		if ( !$share_session ) {
			self::$sessionStrategy = self::_defaultSessionStrategy();
		}
		else {
			self::$sessionStrategy = new SharedSessionStrategy(self::_defaultSessionStrategy());
		}
	}

	/**
	 * Creates default session strategy.
	 *
	 * @return IsolatedSessionStrategy
	 * @access private
	 */
	private static function _defaultSessionStrategy()
	{
		return new IsolatedSessionStrategy();
	}

	/**
	 * Set session meta-info for "Sauce Labs".
	 *
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		if ( !$this->withSauce() ) {
			return;
		}

		$desired_capabilities = $this->getDesiredCapabilities();

		$desired_capabilities[SauceLabsCapability::NAME] = $this->getSauceLabsJobName();

		$jenkins_build_number = getenv('BUILD_NUMBER');

		if ( $jenkins_build_number ) {
			$desired_capabilities[SauceLabsCapability::BUILD] = $jenkins_build_number;
		}

		$this->setDesiredCapabilities($desired_capabilities);
	}

	/**
	 * Returns Job name for "Sauce Labs" service.
	 *
	 * @return string
	 * @access protected
	 */
	protected function getSauceLabsJobName()
	{
		if ( $this->isShared() ) {
			return get_class($this);
		}

		return $this->toString();
	}

	/**
	 * Initializes a browser with given configuration.
	 *
	 * @param array $params Browser configuration.
	 *
	 * @return self
	 * @access public
	 */
	public function setupSpecificBrowser(array $params)
	{
		$params = $this->_resolveBrowserAlias($params);

		$this->setUpSessionStrategy($params);
		$params = array_merge($this->_parameters, $params);
		$this->setSauce($params['sauce']);
		$this->setHost($params['host'])->setPort($params['port'])->setBrowser($params['browserName']);
		$this->_parameters['browser'] = $params['browser'];
		$this->setDesiredCapabilities($params['desiredCapabilities']);
		$this->setSeleniumServerRequestsTimeout($params['seleniumServerRequestsTimeout']);
		$this->setBaseUrl($params['baseUrl']);

		return $this;
	}

	/**
	 * Resolves browser alias into corresponding browser configuration.
	 *
	 * @param array $params Browser configuration.
	 *
	 * @return array
	 * @access private
	 * @throws \InvalidArgumentException When unable to resolve used browser alias.
	 */
	private function _resolveBrowserAlias(array $params)
	{
		if ( !isset($params['alias']) ) {
			return $params;
		}

		$browser_alias = $params['alias'];
		unset($params['alias']);

		$aliases = $this->getBrowserAliases();

		if ( isset($aliases[$browser_alias]) ) {
			$candidate_params = $this->array_merge_recursive($aliases[$browser_alias], $params);

			return $this->_resolveBrowserAlias($candidate_params);
		}

		throw new \InvalidArgumentException(sprintf('Unable to resolve "%s" browser alias', $browser_alias));
	}

	/**
	 * Similar to array_merge_recursive but keyed-valued are always overwritten.
	 *
	 * Priority goes to the 2nd array.
	 *
	 * @param array $array1 First array.
	 * @param array $array2 Second array.
	 *
	 * @return array
	 * @access protected
	 */
	protected function array_merge_recursive(array $array1, array $array2)
	{
		foreach ($array2 as $array2_key => $array2_value) {
			if ( isset($array1[$array2_key]) ) {
				$array1[$array2_key] = $this->array_merge_recursive($array1[$array2_key], $array2_value);
			}
			else {
				$array1[$array2_key] = $array2_value;
			}
		}

		return $array1;
	}

	/**
	 * Initializes session strategy using given browser configuration.
	 *
	 * @param array $browser Browser configuration.
	 *
	 * @return self
	 * @access protected
	 * @throws \InvalidArgumentException When incorrect parameter given.
	 */
	protected function setUpSessionStrategy(array $browser)
	{
		// This logic creates separate strategy for:
		//  - each browser configuration in self::$browsers (for isolated strategy)
		//  - each browser configuration in self::$browsers for each test case (for shared strategy)
		//  - each test, when self::$browsers not set (for isolated strategy)

		$session_strategy_hash = $this->getSessionStrategyHash($browser);

		if ( $session_strategy_hash == self::$_lastUsedSessionStrategyHash ) {
			// same strategy as in previous test - reuse it
		}
		elseif ( isset($browser['sessionStrategy']) ) {
			$name = $browser['sessionStrategy'];

			switch ( $name ) {
				case self::SESSION_STRATEGY_ISOLATED:
					self::$sessionStrategiesInUse[$session_strategy_hash] = new IsolatedSessionStrategy();
					break;

				case self::SESSION_STRATEGY_SHARED:
					self::$sessionStrategiesInUse[$session_strategy_hash] = new SharedSessionStrategy(self::_defaultSessionStrategy());
					break;

				default:
					throw new \InvalidArgumentException(sprintf(
						'Session strategy must be either "%s" or "%s"',
						self::SESSION_STRATEGY_ISOLATED, self::SESSION_STRATEGY_SHARED
					));
			}
		}
		else {
			self::$sessionStrategiesInUse[$session_strategy_hash] = self::_defaultSessionStrategy();
		}

		self::$_lastUsedSessionStrategyHash = $session_strategy_hash;
		$this->localSessionStrategy = self::$sessionStrategiesInUse[$session_strategy_hash];

		return $this;
	}

	/**
	 * Returns session strategy hash based on browser configuration.
	 *
	 * @param array $browser Browser configuration.
	 *
	 * @return integer
	 * @access protected
	 */
	protected function getSessionStrategyHash(array $browser)
	{
		ksort($browser);
		$ret = crc32(serialize($browser));

		if ( isset($browser['sessionStrategy']) && ($browser['sessionStrategy'] == self::SESSION_STRATEGY_SHARED) ) {
			$ret .= '::' . get_class($this);
		}

		return $ret;
	}

	/**
	 * Called, when last test in a test case has ended.
	 *
	 * @return void
	 * @access public
	 */
	public function endOfTestCase()
	{
		$this->_handleEnd('test_case');
	}

	/**
	 * Tells if session is shared across tests in a test case.
	 *
	 * @return boolean
	 * @access protected
	 */
	protected function isShared()
	{
		return $this->_getStrategy() instanceof SharedSessionStrategy;
	}

	/**
	 * Returns session strategy used currently.
	 *
	 * @return ISessionStrategy
	 * @access private
	 * @see    setUpSessionStrategy()
	 */
	private function _getStrategy()
	{
		if ( $this->localSessionStrategy ) {
			return $this->localSessionStrategy;
		}

		return self::_sessionStrategy();
	}

	/**
	 * Returns session strategy requested by test case or creates default one.
	 *
	 * @return ISessionStrategy
	 * @access private
	 * @see    _getStrategy()
	 */
	private static function _sessionStrategy()
	{
		if ( !self::$sessionStrategy ) {
			self::$sessionStrategy = self::_defaultSessionStrategy();
		}

		return self::$sessionStrategy;
	}

	/**
	 * Creates Mink session using current session strategy and returns it.
	 *
	 * @return Session
	 * @access protected
	 */
	protected function getSession()
	{
		if ( $this->_session ) {
			return $this->_session;
		}

		try {
			$this->_session = $this->_getStrategy()->session($this->_parameters);

			if ( $this->_collectCodeCoverageInformation ) {
				$this->_session->visit($this->getBaseUrl());
			}
		}
		catch ( DriverException $e ) {
			$this->markTestSkipped(sprintf(
				'The Selenium Server is not active on host %s at port %s.',
				$this->_parameters['host'], $this->_parameters['port']
			));
		}

		return $this->_session;
	}

	/**
	 * Runs the test case and collects the results in a TestResult object.
	 *
	 * If no TestResult object is passed a new one will be created.
	 *
	 * @param \PHPUnit_Framework_TestResult $result Test result.
	 *
	 * @return \PHPUnit_Framework_TestResult
	 * @access public
	 * @throws \PHPUnit_Framework_Exception When exception was thrown during a test.
	 */
	public function run(\PHPUnit_Framework_TestResult $result = null)
	{
		$this->_testId = get_class($this) . '__' . $this->getName();

		if ( $result === null ) {
			$result = $this->createResult();
		}

		$this->_collectCodeCoverageInformation = $result->getCollectCodeCoverageInformation();

		parent::run($result);

		if ( $this->_collectCodeCoverageInformation ) {
			$coverage = new RemoteCoverage($this->coverageScriptUrl, $this->_testId);

			$result->getCodeCoverage()->append($coverage->get(), $this);
		}

		if ( $this->withSauce() ) {
			if ( $this->isShared() ) {
				// all tests in a test case use same session -> failed even if 1 test fails
				$passed = $result->wasSuccessful();
			}
			else {
				// each test in a test case are using it's own session -> failed if test fails
				$passed = !$this->hasFailed();
			}

			$this->getSauceRest()->updateJob($this->getSessionId(), array('passed' => $passed));
		}

		// do not call this before to give the time to the Listeners to run
		$this->_handleEnd('test');

		return $result;
	}

	/**
	 * Handles test or test case end.
	 *
	 * @param string $item Item code.
	 *
	 * @return self
	 * @access private
	 * @throws \InvalidArgumentException When incorrect item to handle is given.
	 */
	private function _handleEnd($item)
	{
		if ( $item == 'test' ) {
			$this->_getStrategy()->endOfTest($this->_session);
		}
		elseif ( $item == 'test_case' ) {
			$this->_getStrategy()->endOfTestCase($this->_session);
		}
		else {
			throw new \InvalidArgumentException(sprintf('Unknown item "%s" to stop', $item));
		}

		if ( ($this->_session !== null) && !$this->_session->isStarted() ) {
			$this->_session = null;
		}

		return $this;
	}

	/**
	 * Override to tell remote website, that code coverage information needs to be collected.
	 *
	 * @return mixed
	 * @access protected
	 * @throws \Exception When exception was thrown inside the test.
	 */
	protected function runTest()
	{
		$this->getSession();
		$result = $thrown_exception = null;

		if ( $this->_collectCodeCoverageInformation ) {
			$this->_session->setCookie('PHPUNIT_SELENIUM_TEST_ID', null);
			$this->_session->setCookie('PHPUNIT_SELENIUM_TEST_ID', $this->_testId);
		}

		try {
			$result = parent::runTest();

			/*if ( !empty($this->verificationErrors) ) {
				$this->fail(implode("\n", $this->verificationErrors));
			}*/
		} catch ( \Exception $e ) {
			$thrown_exception = $e;
		}

		if ( $thrown_exception !== null ) {
			throw $thrown_exception;
		}

		return $result;
	}

	/**
	 * Creates test suite for usage with Mink.
	 *
	 * @param string $class_name Test case class name.
	 *
	 * @return TestSuite
	 * @access public
	 */
	public static function suite($class_name)
	{
		return TestSuite::fromTestCaseClass($class_name);
	}

	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return void
	 * @access protected
	 */
	protected function onNotSuccessfulTest(\Exception $e)
	{
		$this->_getStrategy()->notSuccessfulTest($e);

		parent::onNotSuccessfulTest($e);
	}

	/**
	 * Sets hostname to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $host Hostname.
	 *
	 * @return self
	 * @access public
	 * @throws \PHPUnit_Framework_Exception When host is not a string.
	 */
	public function setHost($host)
	{
		if ( $this->withSauce() ) {
			$sauce = $this->getSauce();
			$host = $sauce['username'] . ':' . $sauce['api_key'] . '@ondemand.saucelabs.com';
		}

		if ( !is_string($host) ) {
			throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
		}

		$this->_parameters['host'] = $host;

		return $this;
	}

	/**
	 * Returns hostname from browser configuration.
	 *
	 * @return string
	 * @access public
	 */
	public function getHost()
	{
		return $this->_parameters['host'];
	}

	/**
	 * Sets port to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param integer $port Port.
	 *
	 * @return self
	 * @access public
	 * @throws \PHPUnit_Framework_Exception When port isn't a number.
	 */
	public function setPort($port)
	{
		if ( $this->withSauce() ) {
			$port = 80;
		}

		if ( !is_int($port) ) {
			throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
		}

		$this->_parameters['port'] = $port;

		return $this;
	}

	/**
	 * Returns port from browser configuration.
	 *
	 * @return integer
	 * @access public
	 */
	public function getPort()
	{
		return $this->_parameters['port'];
	}

	/**
	 * Sets browser name to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $browser_name Browser name.
	 *
	 * @return self
	 * @access public
	 * @throws \PHPUnit_Framework_Exception When browser name isn't a string.
	 */
	public function setBrowser($browser_name)
	{
		if ( $this->withSauce() && !is_string($browser_name) ) {
			$browser_name = 'chrome';
		}

		if ( !is_string($browser_name) ) {
			throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
		}

		$this->_parameters['browserName'] = $browser_name;

		return $this;
	}

	/**
	 * Returns browser name from browser configuration.
	 *
	 * @return string
	 * @access public
	 */
	public function getBrowser()
	{
		return $this->_parameters['browserName'];
	}

	/**
	 * Sets default browser url to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $base_url Default browser url.
	 *
	 * @return self
	 * @access public
	 * @throws \PHPUnit_Framework_Exception When browser url isn't a string.
	 */
	public function setBaseUrl($base_url)
	{
		if ( !is_string($base_url) ) {
			throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
		}

		$this->_parameters['baseUrl'] = $base_url;

		return $this;
	}

	/**
	 * Returns default browser url from browser configuration.
	 *
	 * @return string
	 * @access public
	 */
	public function getBaseUrl()
	{
		return $this->_parameters['baseUrl'];
	}

	/**
	 * Sets desired capabilities to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param array $capabilities Desired capabilities.
	 *
	 * @return self
	 * @access public
	 * @link http://code.google.com/p/selenium/wiki/JsonWireProtocol
	 */
	public function setDesiredCapabilities(array $capabilities)
	{
		if ( $this->withSauce() ) {
			if ( !isset($capabilities['platform']) ) {
				$capabilities['platform'] = 'Windows XP';
			}

			if ( !isset($capabilities['version']) ) {
				$capabilities['version'] = '';
			}
		}

		$this->_parameters['desiredCapabilities'] = $capabilities;

		return $this;
	}

	/**
	 * Returns desired capabilities from browser configuration.
	 *
	 * @return array|null
	 * @access public
	 */
	public function getDesiredCapabilities()
	{
		return $this->_parameters['desiredCapabilities'];
	}

	/**
	 * Sets server timeout.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param integer $timeout Server timeout in seconds.
	 *
	 * @return self
	 * @access public
	 */
	public function setSeleniumServerRequestsTimeout($timeout)
	{
		$this->_parameters['seleniumServerRequestsTimeout'] = $timeout;

		return $this;
	}

	/**
	 * Returns server timeout.
	 *
	 * @return integer
	 * @access public
	 */
	public function getSeleniumServerRequestsTimeout()
	{
		return $this->_parameters['seleniumServerRequestsTimeout'];
	}

	/**
	 * Sets "Sauce Labs" connection details.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param array $sauce Connection details.
	 *
	 * @return self
	 * @access public
	 * @link https://saucelabs.com/php
	 */
	public function setSauce(array $sauce)
	{
		$this->_parameters['sauce'] = $sauce;

		return $this;
	}

	/**
	 * Returns "Sauce Labs" connection details.
	 *
	 * @return array
	 * @access public
	 * @link https://saucelabs.com/php
	 */
	public function getSauce()
	{
		return $this->_parameters['sauce'];
	}

	/**
	 * Get test id (generated internally).
	 *
	 * @return string
	 * @access public
	 */
	public function getTestId()
	{
		return $this->_testId;
	}

	/**
	 * Get Selenium2 current session id.
	 *
	 * @return string
	 * @access protected
	 */
	protected function getSessionId()
	{
		if ( $this->_session ) {
			$driver = $this->_session->getDriver();
			/* @var $driver \Behat\Mink\Driver\Selenium2Driver */

			$wd_session = $driver->getWebDriverSession();

			return $wd_session ? basename($wd_session->getUrl()) : '';
		}

		return false;
	}

	/**
	 * Gets browser configuration aliases.
	 *
	 * Allows to decouple actual test server connection details from test cases.
	 *
	 * @return array
	 * @access protected
	 */
	protected function getBrowserAliases()
	{
		return array();
	}

	/**
	 * Tells, that "Sauce Labs" is used by current browser configuration.
	 *
	 * @return boolean
	 * @access protected
	 */
	protected function withSauce()
	{
		$sauce = $this->getSauce();

		return !empty($sauce);
	}

	/**
	 * Returns API class for "Sauce Labs" service interaction.
	 *
	 * @return SauceRest
	 * @access protected
	 * @throws \RuntimeException When no "Sauce Labs" configuration found.
	 */
	protected function getSauceRest()
	{
		if ( !$this->withSauce() ) {
			throw new \RuntimeException('"Sauce Labs" configuration absent in browser configuration');
		}

		$sauce = $this->getSauce();

		return new SauceRest($sauce['username'], $sauce['api_key']);
	}

}