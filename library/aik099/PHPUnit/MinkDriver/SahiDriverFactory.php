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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use Behat\Mink\Driver\DriverInterface;

class SahiDriverFactory implements IMinkDriverFactory
{

	/**
	 * Returns driver name, that can be used in browser configuration.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'sahi';
	}

	/**
	 * Returns default values for browser configuration.
	 *
	 * @return array
	 */
	public function getDriverDefaults()
	{
		return array(
			'port' => 9999,
			'driverOptions' => array(
				'sid' => null,
				'limit' => 600,
				'browser' => null,
			),
		);
	}

	/**
	 * Returns a new driver instance according to the browser configuration.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return DriverInterface
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		if ( !class_exists('Behat\Mink\Driver\SahiDriver') ) {
			throw new \RuntimeException(
				'Install MinkSahiDriver in order to use sahi driver.'
			);
		}

		$driver_options = $browser->getDriverOptions();

		$connection = new \Behat\SahiClient\Connection(
			$driver_options['sid'],
			$browser->getHost(),
			$browser->getPort(),
			$driver_options['browser'],
			$driver_options['limit']
		);

		return new \Behat\Mink\Driver\SahiDriver(
			$browser->getBrowserName(),
			new \Behat\SahiClient\Client($connection)
		);
	}

}
