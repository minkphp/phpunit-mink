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

class Selenium2DriverFactory implements IMinkDriverFactory
{

	/**
	 * Returns driver name, that can be used in browser configuration.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'selenium2';
	}

	/**
	 * Returns default values for browser configuration.
	 *
	 * @return array
	 */
	public function getDriverDefaults()
	{
		return array(
			'port' => 4444,
			'driverOptions' => array(),
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
		if ( !class_exists('Behat\Mink\Driver\Selenium2Driver') ) {
			throw new \RuntimeException(
				'Install MinkSelenium2Driver in order to use selenium2 driver.'
			);
		}

		$browser_name = $browser->getBrowserName();
		$capabilities = $browser->getDesiredCapabilities();
		$capabilities['browserName'] = $browser_name;

		// TODO: Maybe doesn't work!
		ini_set('default_socket_timeout', $browser->getTimeout());

		$driver = new \Behat\Mink\Driver\Selenium2Driver(
			$browser_name,
			$capabilities,
			'http://' . $browser->getHost() . ':' . $browser->getPort() . '/wd/hub'
		);

		return $driver;
	}

}
