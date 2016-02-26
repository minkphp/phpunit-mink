<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */


namespace aik099\PHPUnit\MinkDriver;


class DriverFactoryRegistry
{

	/**
	 * Driver factory registry.
	 *
	 * @var IMinkDriverFactory[]
	 */
	private $_registry = array();

	/**
	 * Registers Mink driver factory.
	 *
	 * @param IMinkDriverFactory $driver_factory Driver factory.
	 *
	 * @return void
	 * @throws \LogicException When driver factory is already registered.
	 */
	public function add(IMinkDriverFactory $driver_factory)
	{
		$driver_name = $driver_factory->getDriverName();

		if ( isset($this->_registry[$driver_name]) ) {
			throw new \LogicException('Driver factory for "' . $driver_name . '" driver is already registered.');
		}

		$this->_registry[$driver_name] = $driver_factory;
	}

	/**
	 * Looks up driver factory by name of the driver it can create.
	 *
	 * @param string $driver_name Driver name.
	 *
	 * @return IMinkDriverFactory
	 * @throws \OutOfBoundsException When driver not found.
	 */
	public function get($driver_name)
	{
		if ( !isset($this->_registry[$driver_name]) ) {
			throw new \OutOfBoundsException(sprintf('No driver factory for "%s" driver.', $driver_name));
		}

		return $this->_registry[$driver_name];
	}

}
