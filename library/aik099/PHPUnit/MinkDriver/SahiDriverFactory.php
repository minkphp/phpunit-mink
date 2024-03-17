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

class SahiDriverFactory extends AbstractDriverFactory
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
	 * @inheritDoc
	 */
	public function getDriverPackageUrl()
	{
		return 'https://packagist.org/packages/behat/mink-sahi-driver';
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
	 * @inheritDoc
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		$this->assertInstalled('Behat\Mink\Driver\SahiDriver');

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
