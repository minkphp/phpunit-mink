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
use tests\aik099\PHPUnit\AbstractTestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;
use aik099\PHPUnit\MinkDriver\GoutteDriverFactory;
use aik099\PHPUnit\MinkDriver\SahiDriverFactory;
use aik099\PHPUnit\MinkDriver\Selenium2DriverFactory;
use aik099\PHPUnit\MinkDriver\ZombieDriverFactory;

class DriverFactoryTest extends AbstractTestCase
{

	use ExpectException;

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

		$this->expectException('RuntimeException');
		$this->expectExceptionMessage(
			sprintf(
				'The "%s" driver package is not installed. Please follow installation instructions at %s.',
				$factory->getDriverName(),
				$factory->getDriverPackageUrl()
			)
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

		$browser_configuration = new BrowserConfiguration($di['driver_factory_registry']);
		$browser_configuration->setDriver($factory->getDriverName());

		return $browser_configuration;
	}

	public static function driverDataProvider()
	{
		return array(
			'goutte' => array(
				'\Behat\Mink\Driver\GoutteDriver',
				GoutteDriverFactory::class,
			),
			'sahi' => array(
				'\Behat\Mink\Driver\SahiDriver',
				SahiDriverFactory::class,
			),
			'selenium2' => array(
				'\Behat\Mink\Driver\Selenium2Driver',
				Selenium2DriverFactory::class,
			),
			'zombie' => array(
				'\Behat\Mink\Driver\ZombieDriver',
				ZombieDriverFactory::class,
			),
		);
	}

}
