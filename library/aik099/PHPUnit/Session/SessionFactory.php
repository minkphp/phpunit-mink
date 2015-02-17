<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\Session;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Session;

/**
 * Produces sessions.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class SessionFactory implements ISessionFactory
{

	/**
	 * The driver factory registry.
	 *
	 * @var IMinkDriverFactory[]
	 */
	private $_driverFactoryRegistry = array();

	/**
	 * The default driver factories. Only those present will be added to the $_driverFactoryRegistry array.
	 *
	 * @var string[]
	 */
	private $_defaultDriverFactories = array(
		'selenium2' => 'aik099\PHPUnit\MinkDriver\Selenium2DriverFactory',
		'sahi' => 'aik099\PHPUnit\MinkDriver\SahiDriverFactory',
		'goutte' => 'aik099\PHPUnit\MinkDriver\GoutteDriverFactory',
		'zombie' => 'aik099\PHPUnit\MinkDriver\ZombieDriverFactory',
	);

	/**
	 * Initialize the driver factory registry array.
	 */
	public function __construct()
	{
		foreach ( $this->_defaultDriverFactories as $alias => $factory_class ) {
			if ( class_exists($factory_class) ) {
				$this->registerDriverFactory($alias, new $factory_class());
			}
		}
	}

	/**
	 * Creates new session based on browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return Session
	 */
	public function createSession(BrowserConfiguration $browser)
	{
		return new Session($this->_createDriver($browser));
	}

	/**
	 * Creates driver based on browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return DriverInterface
	 */
	private function _createDriver(BrowserConfiguration $browser)
	{
		$driver_alias = $browser->getDriver();

		if ( !array_key_exists($driver_alias, $this->_driverFactoryRegistry) ) {
			throw new \OutOfBoundsException(sprintf('No driver factory for driver "%s"', $driver_alias));
		}

		$factory = $this->_getDriverFactory($driver_alias);

		return $factory->getInstance($browser);
	}

	/**
	 * Lazy instantiation of the driver factory.
	 *
	 * @param string $driver_alias The driver alias for the driver factory.
	 *
	 * @return IMinkDriverFactory
	 */
	private function _getDriverFactory($driver_alias)
	{
		if ( is_string($this->_driverFactoryRegistry[$driver_alias]) ) {
			$this->_driverFactoryRegistry[$driver_alias] = new $this->_driverFactoryRegistry[$driver_alias]();
		}

		return $this->_driverFactoryRegistry[$driver_alias];
	}

	/**
	 * Register a new mink driver factory by either specifying the class name or an instance.
	 *
	 * @param string             $driver_alias   The driver alias.
	 * @param IMinkDriverFactory $driver_factory The driver factory class or instance.
	 *
	 * @return void
	 * @throws \InvalidArgumentException If the driver alias is not a string.
	 */
	public function registerDriverFactory($driver_alias, IMinkDriverFactory $driver_factory)
	{
		$this->_validateDriverAlias($driver_alias);
		$this->_driverFactoryRegistry[$driver_alias] = $driver_factory;
	}

	/**
	 * Validate the driver alias.
	 *
	 * @param string $driver_alias The driver alias.
	 *
	 * @return void
	 */
	private function _validateDriverAlias($driver_alias)
	{
		if ( !is_string($driver_alias) ) {
			throw new \InvalidArgumentException('The Mink driver alias must be a string.');
		}
	}

}
