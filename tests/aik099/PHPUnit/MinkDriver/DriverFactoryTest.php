<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */


namespace tests\aik099\PHPUnit\MinkDriver;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\DIContainer;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;
use Mockery as m;

class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider driverDataProvider
	 */
	public function testProperDriverReturned($driver_class, $factory_class)
	{
		if ( !class_exists($driver_class) ) {
			$this->markTestSkipped(sprintf('Mink driver "%s" is not installed.', $driver_class));
		}

		/** @var IMinkDriverFactory $factory */
		$factory = new $factory_class();

		$this->assertInstanceOf($driver_class, $factory->createDriver($this->createBrowserConfiguration($factory)));
	}

	/**
	 * @dataProvider driverDataProvider
	 */
	public function testMinkDriverMissingError($driver_class, $factory_class)
	{
		if ( class_exists($driver_class) ) {
			$this->markTestSkipped(sprintf('Mink driver "%s" is installed.', $driver_class));
		}

		/** @var IMinkDriverFactory $factory */
		$factory = new $factory_class();
		$driver_class_parts = explode('\\', $driver_class);

		$this->setExpectedException(
			'\RuntimeException',
			'Install Mink' . end($driver_class_parts) . ' in order to use ' . $factory->getDriverName() . ' driver.'
		);
		$factory->createDriver($this->createBrowserConfiguration($factory));
	}

	/**
	 * Creates the browser configuration.
	 *
	 * @param IMinkDriverFactory $factory Driver factory.
	 *
	 * @return BrowserConfiguration
	 */
	protected function createBrowserConfiguration(IMinkDriverFactory $factory)
	{
		$di = new DIContainer();

		$event_dispatcher = m::mock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
		$browser_configuration = new BrowserConfiguration($event_dispatcher, $di['driver_factory_registry']);
		$browser_configuration->setDriver($factory->getDriverName());

		return $browser_configuration;
	}

	public function driverDataProvider()
	{
		return array(
			'goutte' => array(
				'Behat\\Mink\\Driver\\GoutteDriver',
				'aik099\PHPUnit\MinkDriver\GoutteDriverFactory',
			),
			'sahi' => array(
				'Behat\\Mink\\Driver\\SahiDriver',
				'aik099\PHPUnit\MinkDriver\SahiDriverFactory',
			),
			'selenium2' => array(
				'Behat\\Mink\\Driver\\Selenium2Driver',
				'aik099\PHPUnit\MinkDriver\Selenium2DriverFactory',
			),
			'zombie' => array(
				'Behat\\Mink\\Driver\\ZombieDriver',
				'aik099\PHPUnit\MinkDriver\ZombieDriverFactory',
			),
		);
	}

}
