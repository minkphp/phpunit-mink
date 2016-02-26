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

class ZombieDriverFactory implements IMinkDriverFactory
{

	/**
	 * Returns driver name, that can be used in browser configuration.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'zombie';
	}

	/**
	 * Returns default values for browser configuration.
	 *
	 * @return array
	 */
	public function getDriverDefaults()
	{
		return array(
			'port' => 8124,
			'driverOptions' => array(
				'node_bin' => 'node',
				'server_path' => null,
				'threshold' => 2000000,
				'node_modules_path' => '',
			),
		);
	}

	/**
	 * Returns a new driver instance according to the browser configuration.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return DriverInterface
	 * @throws \RuntimeException When driver isn't installed.
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		if ( !class_exists('Behat\Mink\Driver\ZombieDriver') ) {
			throw new \RuntimeException(
				'Install MinkZombieDriver in order to use zombie driver.'
			);
		}

		$driver_options = $browser->getDriverOptions();

		return new \Behat\Mink\Driver\ZombieDriver(
			new \Behat\Mink\Driver\NodeJS\Server\ZombieServer(
				$browser->getHost(),
				$browser->getPort(),
				$driver_options['node_bin'],
				$driver_options['server_path'],
				$driver_options['threshold'],
				$driver_options['node_modules_path']
			)
		);
	}

}
