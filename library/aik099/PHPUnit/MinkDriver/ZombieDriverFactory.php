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

class ZombieDriverFactory extends AbstractDriverFactory
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
	 * @inheritDoc
	 */
	public function getDriverPackageUrl()
	{
		return 'https://packagist.org/packages/behat/mink-zombie-driver';
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
	 * @inheritDoc
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		$this->assertInstalled('Behat\Mink\Driver\ZombieDriver');

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
