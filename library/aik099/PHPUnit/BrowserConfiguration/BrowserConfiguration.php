<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\BrowserConfiguration;


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;

/**
 * Browser configuration for browser.
 *
 * @method \Mockery\Expectation shouldReceive
 */
class BrowserConfiguration
{

	/**
	 * Browser configuration.
	 *
	 * @var array
	 */
	protected $parameters;

	/**
	 * Browser configuration aliases.
	 *
	 * @var array
	 */
	private $_aliases;

	/**
	 * Creates browser configuration.
	 *
	 * @param array $aliases Browser configuration aliases.
	 */
	public function __construct(array $aliases = array())
	{
		$this->parameters = array(
			'host' => 'localhost',
			'port' => 4444,
			'browserName' => 'firefox',
			'desiredCapabilities' => array(),
			'seleniumServerRequestsTimeout' => 60,
			'baseUrl' => '',
			'sessionStrategy' => SessionStrategyManager::ISOLATED_STRATEGY,
		);

		$this->_aliases = $aliases;
	}

	/**
	 * Initializes a browser with given configuration.
	 *
	 * @param array $parameters Browser configuration parameters.
	 *
	 * @return self
	 */
	public function setup(array $parameters)
	{
		$parameters = array_merge($this->parameters, $this->resolveAlias($parameters));

		$this->setHost($parameters['host'])->setPort($parameters['port']);
		$this->setBrowserName($parameters['browserName']);
		$this->setDesiredCapabilities($parameters['desiredCapabilities']);
		$this->setSeleniumServerRequestsTimeout($parameters['seleniumServerRequestsTimeout']);
		$this->setBaseUrl($parameters['baseUrl']);
		$this->setSessionStrategy($parameters['sessionStrategy']);

		return $this;
	}

	/**
	 * Sets hostname to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $host Hostname.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When host is not a string.
	 */
	public function setHost($host)
	{
		if ( !is_string($host) ) {
			throw new \InvalidArgumentException('Host must be a string');
		}

		$this->parameters['host'] = $host;

		return $this;
	}

	/**
	 * Returns hostname from browser configuration.
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->parameters['host'];
	}

	/**
	 * Sets port to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param integer $port Port.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When port isn't a number.
	 */
	public function setPort($port)
	{
		if ( !is_int($port) ) {
			throw new \InvalidArgumentException('Port must be an integer');
		}

		$this->parameters['port'] = $port;

		return $this;
	}

	/**
	 * Returns port from browser configuration.
	 *
	 * @return integer
	 */
	public function getPort()
	{
		return $this->parameters['port'];
	}

	/**
	 * Sets browser name to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $browser_name Browser name.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When browser name isn't a string.
	 */
	public function setBrowserName($browser_name)
	{
		if ( !is_string($browser_name) ) {
			throw new \InvalidArgumentException('Browser must be a string');
		}

		$this->parameters['browserName'] = $browser_name;

		return $this;
	}

	/**
	 * Returns browser name from browser configuration.
	 *
	 * @return string
	 */
	public function getBrowserName()
	{
		return $this->parameters['browserName'];
	}

	/**
	 * Sets default browser url to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $base_url Default browser url.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When browser url isn't a string.
	 */
	public function setBaseUrl($base_url)
	{
		if ( !is_string($base_url) ) {
			throw new \InvalidArgumentException('Base url must be a string');
		}

		$this->parameters['baseUrl'] = $base_url;

		return $this;
	}

	/**
	 * Returns default browser url from browser configuration.
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->parameters['baseUrl'];
	}

	/**
	 * Sets desired capabilities to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param array $capabilities Desired capabilities.
	 *
	 * @return self
	 * @link http://code.google.com/p/selenium/wiki/JsonWireProtocol
	 */
	public function setDesiredCapabilities(array $capabilities)
	{
		$this->parameters['desiredCapabilities'] = $capabilities;

		return $this;
	}

	/**
	 * Returns desired capabilities from browser configuration.
	 *
	 * @return array
	 */
	public function getDesiredCapabilities()
	{
		return $this->parameters['desiredCapabilities'];
	}

	/**
	 * Sets server timeout.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param integer $timeout Server timeout in seconds.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When timeout isn't integer.
	 */
	public function setSeleniumServerRequestsTimeout($timeout)
	{
		if ( !is_int($timeout) ) {
			throw new \InvalidArgumentException('Timeout must be an integer');
		}

		$this->parameters['seleniumServerRequestsTimeout'] = $timeout;

		return $this;
	}

	/**
	 * Returns server timeout.
	 *
	 * @return integer
	 */
	public function getSeleniumServerRequestsTimeout()
	{
		return $this->parameters['seleniumServerRequestsTimeout'];
	}

	/**
	 * Sets session strategy name.
	 *
	 * @param string $session_strategy Session strategy name.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When unknown session strategy name given.
	 */
	public function setSessionStrategy($session_strategy)
	{
		$allowed = array(SessionStrategyManager::ISOLATED_STRATEGY, SessionStrategyManager::SHARED_STRATEGY);

		if ( !in_array($session_strategy, $allowed) ) {
			throw new \InvalidArgumentException(vsprintf('Session strategy must be either "%s" or "%s"', $allowed));
		}

		$this->parameters['sessionStrategy'] = $session_strategy;

		return $this;
	}

	/**
	 * Returns session strategy name.
	 *
	 * @return string
	 */
	public function getSessionStrategy()
	{
		return $this->parameters['sessionStrategy'];
	}

	/**
	 * Tells if browser configuration requires a session, that is shared across tests in a test case.
	 *
	 * @return boolean
	 */
	public function isShared()
	{
		return $this->getSessionStrategy() == SessionStrategyManager::SHARED_STRATEGY;
	}

	/**
	 * Returns session strategy hash based on given test case and current browser configuration.
	 *
	 * @param BrowserTestCase $test_case Test case.
	 *
	 * @return integer
	 */
	public function getSessionStrategyHash(BrowserTestCase $test_case)
	{
		$ret = $this->getBrowserHash();

		if ( $this->isShared() ) {
			$ret .= '::' . get_class($test_case);
		}

		return $ret;
	}

	/**
	 * Returns test run status based on session strategy requested by browser.
	 *
	 * @param BrowserTestCase               $test_case   Browser test case.
	 * @param \PHPUnit_Framework_TestResult $test_result Test result.
	 *
	 * @return boolean
	 * @see    IsolatedSessionStrategy
	 * @see    SharedSessionStrategy
	 */
	public function getTestStatus(BrowserTestCase $test_case, \PHPUnit_Framework_TestResult $test_result)
	{
		if ( $this->isShared() ) {
			// all tests in a test case use same session -> failed even if 1 test fails
			return $test_result->wasSuccessful();
		}

		// each test in a test case are using it's own session -> failed if test fails
		return !$test_case->hasFailed();
	}

	/**
	 * Returns hash from current configuration.
	 *
	 * @return integer
	 */
	protected function getBrowserHash()
	{
		ksort($this->parameters);

		return crc32(serialize($this->parameters));
	}

	/**
	 * Creates new session based on browser configuration.
	 *
	 * @return Session
	 */
	public function createSession()
	{
		$capabilities = $this->getDesiredCapabilities();
		$capabilities['browserName'] = $this->getBrowserName();

		// TODO: maybe doesn't work
		ini_set('default_socket_timeout', $this->getSeleniumServerRequestsTimeout());

		// create driver:
		$driver = new Selenium2Driver(
			$this->getBrowserName(),
			$capabilities,
			'http://' . $this->getHost() . ':' . $this->getPort() . '/wd/hub'
		);

		return new Session($driver);
	}

	/**
	 * Resolves browser alias into corresponding browser configuration.
	 *
	 * @param array $parameters Browser configuration.
	 *
	 * @return array
	 * @throws \InvalidArgumentException When unable to resolve used browser alias.
	 */
	protected function resolveAlias(array $parameters)
	{
		if ( !isset($parameters['alias']) ) {
			return $parameters;
		}

		$browser_alias = $parameters['alias'];
		unset($parameters['alias']);

		if ( isset($this->_aliases[$browser_alias]) ) {
			$candidate_params = $this->arrayMergeRecursive($this->_aliases[$browser_alias], $parameters);

			return $this->resolveAlias($candidate_params);
		}

		throw new \InvalidArgumentException(sprintf('Unable to resolve "%s" browser alias', $browser_alias));
	}

	/**
	 * Similar to array_merge_recursive but keyed-valued are always overwritten.
	 *
	 * Priority goes to the 2nd array.
	 *
	 * @param mixed $array1 First array.
	 * @param mixed $array2 Second array.
	 *
	 * @return array
	 */
	protected function arrayMergeRecursive($array1, $array2)
	{
		if ( !is_array($array1) || !is_array($array2) ) {
			return $array2;
		}

		foreach ( $array2 as $array2_key => $array2_value ) {
			if ( isset($array1[$array2_key]) ) {
				$array1[$array2_key] = $this->arrayMergeRecursive($array1[$array2_key], $array2_value);
			}
			else {
				$array1[$array2_key] = $array2_value;
			}
		}

		return $array1;
	}

	/**
	 * Hook, called from "BrowserTestCase::setUp" method.
	 *
	 * @param BrowserTestCase $test_case Browser test case.
	 *
	 * @return self
	 */
	public function testSetUpHook(BrowserTestCase $test_case)
	{
		return $this;
	}

	/**
	 * Hook, called from "BrowserTestCase::run" method.
	 *
	 * @param BrowserTestCase               $test_case   Browser test case.
	 * @param \PHPUnit_Framework_TestResult $test_result Test result.
	 *
	 * @return self
	 */
	public function testAfterRunHook(BrowserTestCase $test_case, \PHPUnit_Framework_TestResult $test_result)
	{
		return $this;
	}

}
