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
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use Behat\Mink\Driver\DriverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Browser configuration for browser.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 * @method string getDriver() Returns Mink driver name.
 * @method array getDriverOptions() Returns Mink driver options.
 * @method string getHost() Returns hostname from browser configuration.
 * @method integer getPort() Returns port from browser configuration.
 * @method string getBrowserName() Returns browser name from browser configuration.
 * @method string getBaseUrl() Returns default browser url from browser configuration.
 * @method array getDesiredCapabilities() Returns desired capabilities from browser configuration.
 * @method integer getTimeout() Returns server timeout.
 * @method string getSessionStrategy() Returns session strategy name.
 */
class BrowserConfiguration implements EventSubscriberInterface
{
	const TYPE = 'default';

	/**
	 * User defaults.
	 *
	 * @var array
	 */
	protected $defaults = array(
		// Driver related.
		'host' => 'localhost',
		'driver' => 'selenium2',
		'driverOptions' => array(),

		// TODO: Move under 'driverOptions' of 'selenium2' driver (BC break).
		'desiredCapabilities' => array(),
		'timeout' => 60,

		// Browser related.
		'browserName' => 'firefox', // Have no effect on headless drivers.
		'baseUrl' => '',

		// Test related.
		'sessionStrategy' => ISessionStrategyFactory::TYPE_ISOLATED,
	);

	/**
	 * User defaults merged with driver defaults.
	 *
	 * @var array
	 */
	private $_mergedDefaults = array();

	/**
	 * Manually set browser configuration parameters.
	 *
	 * @var array
	 */
	private $_parameters = array();

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
	 * Driver factory registry.
	 *
	 * @var DriverFactoryRegistry
	 */
	private $_driverFactoryRegistry;

	/**
	 * Driver factory.
	 *
	 * @var IMinkDriverFactory
	 */
	private $_driverFactory;

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
	 *
	 * @param EventDispatcherInterface $event_dispatcher        Event dispatcher.
	 * @param DriverFactoryRegistry    $driver_factory_registry Driver factory registry.
	 */
	public function __construct(
		EventDispatcherInterface $event_dispatcher,
		DriverFactoryRegistry $driver_factory_registry
	) {
		$this->_eventDispatcher = $event_dispatcher;
		$this->_driverFactoryRegistry = $driver_factory_registry;

		if ( $this->defaults['driver'] ) {
			$this->setDriver($this->defaults['driver']);
		}
	}

	/**
	 * Returns type of browser configuration.
	 *
	 * @return string
	 */
	public function getType()
	{
		return static::TYPE;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return array(
			BrowserTestCase::TEST_SETUP_EVENT => array('onTestSetup', 100),
			BrowserTestCase::TEST_ENDED_EVENT => array('onTestEnded', 100),
		);
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
	 * Detaches listeners.
	 *
	 * @return void
	 */
	protected function detachFromTestCase()
	{
		$this->_testCase = null;
		$this->_eventDispatcher->removeSubscriber($this);
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
	 * @return self
	 */
	public function setAliases(array $aliases = array())
	{
		$this->aliases = $aliases;

		return $this;
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

		// Make sure, that 'driver' parameter is handled first.
		if ( isset($parameters['driver']) ) {
			$this->setDriver($parameters['driver']);
			unset($parameters['driver']);
		}

		foreach ( $parameters as $name => $value ) {
			$method = 'set' . ucfirst($name);

			if ( !method_exists($this, $method) ) {
				throw new \InvalidArgumentException('Unable to set unknown parameter "' . $name . '"');
			}

			$this->$method($value);
		}

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
		return array_merge($this->_parameters, self::resolveAliases($parameters, $this->aliases));
	}

	/**
	 * Sets Mink driver to browser configuration.
	 *
	 * @param string $driver_name Mink driver name.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When Mink driver name is not a string.
	 */
	public function setDriver($driver_name)
	{
		if ( !is_string($driver_name) ) {
			throw new \InvalidArgumentException('The Mink driver name must be a string');
		}

		$this->_driverFactory = $this->_driverFactoryRegistry->get($driver_name);
		$this->_mergedDefaults = self::arrayMergeRecursive($this->defaults, $this->_driverFactory->getDriverDefaults());

		return $this->setParameter('driver', $driver_name);
	}

	/**
	 * Sets Mink driver options to browser configuration.
	 *
	 * @param array $driver_options Mink driver options.
	 *
	 * @return self
	 */
	public function setDriverOptions(array $driver_options)
	{
		return $this->setParameter('driverOptions', $driver_options);
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

		return $this->setParameter('host', $host);
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

		return $this->setParameter('port', $port);
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

		return $this->setParameter('browserName', $browser_name);
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

		return $this->setParameter('baseUrl', $base_url);
	}

	/**
	 * Sets desired capabilities to browser configuration.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param array $capabilities Desired capabilities.
	 *
	 * @return self
	 * @link   http://code.google.com/p/selenium/wiki/JsonWireProtocol
	 */
	public function setDesiredCapabilities(array $capabilities)
	{
		return $this->setParameter('desiredCapabilities', $capabilities);
	}

	/**
	 * Sets server timeout.
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

		return $this->setParameter('timeout', $timeout);
	}

	/**
	 * Sets session strategy name.
	 *
	 * @param string $session_strategy Session strategy name.
	 *
	 * @return self
	 */
	public function setSessionStrategy($session_strategy)
	{
		return $this->setParameter('sessionStrategy', $session_strategy);
	}

	/**
	 * Tells if browser configuration requires a session, that is shared across tests in a test case.
	 *
	 * @return boolean
	 */
	public function isShared()
	{
		return $this->getSessionStrategy() == ISessionStrategyFactory::TYPE_SHARED;
	}

	/**
	 * Sets parameter.
	 *
	 * @param string $name  Parameter name.
	 * @param mixed  $value Parameter value.
	 *
	 * @return self
	 * @throws \LogicException When driver wasn't set upfront.
	 */
	protected function setParameter($name, $value)
	{
		if ( !isset($this->_driverFactory) ) {
			throw new \LogicException('Please set "driver" parameter first.');
		}

		$this->_parameters[$name] = $value;

		return $this;
	}

	/**
	 * Returns parameter value.
	 *
	 * @param string $name Name.
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException When unknown parameter was requested.
	 */
	protected function getParameter($name)
	{
		$merged = self::arrayMergeRecursive($this->_mergedDefaults, $this->_parameters);

		if ( array_key_exists($name, $merged) ) {
			return $merged[$name];
		}

		throw new \InvalidArgumentException('Unable to get unknown parameter "' . $name . '"');
	}

	/**
	 * Creates driver based on browser configuration.
	 *
	 * @return DriverInterface
	 */
	public function createDriver()
	{
		$factory = $this->_driverFactoryRegistry->get($this->getDriver());

		return $factory->createDriver($this);
	}

	/**
	 * Returns session strategy hash based on given test case and current browser configuration.
	 *
	 * @return string
	 */
	public function getSessionStrategyHash()
	{
		$ret = $this->getChecksum();

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
			// All tests in a test case use same session -> failed even if 1 test fails.
			return $test_result->wasSuccessful();
		}

		// Each test in a test case are using it's own session -> failed if test fails.
		return !$test_case->hasFailed();
	}

	/**
	 * Returns checksum from current configuration.
	 *
	 * @return integer
	 */
	public function getChecksum()
	{
		ksort($this->_parameters);

		return crc32(serialize($this->_parameters));
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

	/**
	 * Allows to retrieve a parameter by name.
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 *
	 * @return mixed
	 * @throws \BadMethodCallException When non-parameter getter method is invoked.
	 */
	public function __call($method, array $args)
	{
		if ( substr($method, 0, 3) === 'get' ) {
			return $this->getParameter(lcfirst(substr($method, 3)));
		}

		throw new \BadMethodCallException(
			'Method "' . $method . '" does not exist on ' . get_class($this) . ' class'
		);
	}

	/**
	 * Hook, called from "BrowserTestCase::setUp" method.
	 *
	 * @param TestEvent $event Test event.
	 *
	 * @return void
	 */
	public function onTestSetup(TestEvent $event)
	{
		if ( !$event->validateSubscriber($this->getTestCase()) ) {
			return;
		}

		// Place code here.
	}

	/**
	 * Hook, called from "BrowserTestCase::run" method.
	 *
	 * @param TestEndedEvent $event Test ended event.
	 *
	 * @return void
	 */
	public function onTestEnded(TestEndedEvent $event)
	{
		if ( !$event->validateSubscriber($this->getTestCase()) ) {
			return;
		}

		$this->detachFromTestCase();
	}

}
