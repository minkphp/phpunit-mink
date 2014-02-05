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
use aik099\PHPUnit\IEventDispatcherAware;
use aik099\PHPUnit\Session\SessionStrategyManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Browser configuration for browser.
 *
 * @method \Mockery\Expectation shouldReceive
 */
class BrowserConfiguration implements EventSubscriberInterface, IEventDispatcherAware
{

	/**
	 * Default browser configuration.
	 *
	 * @var array
	 */
	protected $defaultParameters = array(
		// server related
		'host' => 'localhost',
		'port' => 4444,
		'timeout' => 60,

		// browser related
		'browserName' => 'firefox',
		'desiredCapabilities' => array(),
		'baseUrl' => '',

		// test related
		'sessionStrategy' => SessionStrategyManager::ISOLATED_STRATEGY,
	);

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
	protected $aliases;

	/**
	 * Test case.
	 *
	 * @var BrowserTestCase
	 */
	private $_testCase;

	/**
	 * Event dispatcher.
	 *
	 * @var EventDispatcherInterface
	 */
	private $_eventDispatcher;

	/**
	 * Resolves browser alias into corresponding browser configuration.
	 *
	 * @param array $parameters Browser configuration.
	 * @param array $aliases    Browser configuration aliases.
	 *
	 * @return array
	 * @throws \InvalidArgumentException When unable to resolve used browser alias.
	 */
	public static function resolveAliases(array $parameters, array $aliases)
	{
		if ( !isset($parameters['alias']) ) {
			return $parameters;
		}

		$browser_alias = $parameters['alias'];
		unset($parameters['alias']);

		if ( isset($aliases[$browser_alias]) ) {
			$candidate_params = self::arrayMergeRecursive($aliases[$browser_alias], $parameters);

			return self::resolveAliases($candidate_params, $aliases);
		}

		throw new \InvalidArgumentException(sprintf('Unable to resolve "%s" browser alias', $browser_alias));
	}

	/**
	 * Creates browser configuration.
	 */
	public function __construct()
	{
		$this->parameters = $this->defaultParameters;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return array();
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
	 * Attaches listeners.
	 *
	 * @param BrowserTestCase $test_case Test case.
	 *
	 * @return self
	 */
	public function attachToTestCase(BrowserTestCase $test_case)
	{
		$this->_testCase = $test_case;
		$this->_eventDispatcher->addSubscriber($this);

		return $this;
	}

	/**
	 * Returns associated test case.
	 *
	 * @return BrowserTestCase
	 * @throws \RuntimeException When test case not attached.
	 */
	public function getTestCase()
	{
		if ( $this->_testCase === null ) {
			throw new \RuntimeException('Test Case not attached, use "attachToTestCase" method');
		}

		return $this->_testCase;
	}

	/**
	 * Sets aliases.
	 *
	 * @param array $aliases Browser configuration aliases.
	 *
	 * @return void
	 */
	public function setAliases(array $aliases = array())
	{
		$this->aliases = $aliases;
	}

	/**
	 * Initializes a browser with given configuration.
	 *
	 * @param array $parameters Browser configuration parameters.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When unknown parameter is discovered.
	 */
	public function setup(array $parameters)
	{
		$parameters = $this->prepareParameters($parameters);
		$unknown_parameters = array_diff(array_keys($parameters), array_keys($this->defaultParameters));

		if ( $unknown_parameters ) {
			throw new \InvalidArgumentException(
				'Following parameter(-s) are unknown: "' . implode('", "', $unknown_parameters) . '"'
			);
		}

		$this->setHost($parameters['host'])->setPort($parameters['port'])->setTimeout($parameters['timeout']);
		$this->setBrowserName($parameters['browserName'])->setDesiredCapabilities($parameters['desiredCapabilities']);
		$this->setBaseUrl($parameters['baseUrl']);
		$this->setSessionStrategy($parameters['sessionStrategy']);

		return $this;
	}

	/**
	 * Merges together default, given parameter and resolves aliases along the way.
	 *
	 * @param array $parameters Browser configuration parameters.
	 *
	 * @return array
	 */
	protected function prepareParameters(array $parameters)
	{
		return array_merge($this->parameters, self::resolveAliases($parameters, $this->aliases));
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
	public function setTimeout($timeout)
	{
		if ( !is_int($timeout) ) {
			throw new \InvalidArgumentException('Timeout must be an integer');
		}

		$this->parameters['timeout'] = $timeout;

		return $this;
	}

	/**
	 * Returns server timeout.
	 *
	 * @return integer
	 */
	public function getTimeout()
	{
		return $this->parameters['timeout'];
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
	 * @return integer
	 */
	public function getSessionStrategyHash()
	{
		$ret = $this->getBrowserHash();

		if ( $this->isShared() ) {
			$ret .= '::' . get_class($this->getTestCase());
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
	 * Similar to array_merge_recursive but keyed-valued are always overwritten.
	 *
	 * Priority goes to the 2nd array.
	 *
	 * @param mixed $array1 First array.
	 * @param mixed $array2 Second array.
	 *
	 * @return array
	 */
	protected static function arrayMergeRecursive($array1, $array2)
	{
		if ( !is_array($array1) || !is_array($array2) ) {
			return $array2;
		}

		foreach ( $array2 as $array2_key => $array2_value ) {
			if ( isset($array1[$array2_key]) ) {
				$array1[$array2_key] = self::arrayMergeRecursive($array1[$array2_key], $array2_value);
			}
			else {
				$array1[$array2_key] = $array2_value;
			}
		}

		return $array1;
	}

}
