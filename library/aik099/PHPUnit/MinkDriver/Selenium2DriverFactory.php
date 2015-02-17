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
use Behat\Mink\Driver\Selenium2Driver;

class Selenium2DriverFactory implements IMinkDriverFactory
{

	/**
	 * Instantiate and return the selenium 2 driver instance.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return Selenium2Driver
	 */
	public function getInstance(BrowserConfiguration $browser)
	{
		$browser_name = $browser->getBrowserName();
		$capabilities = $browser->getDesiredCapabilities();
		$capabilities['browserName'] = $browser_name;

		// TODO: maybe doesn't work!
		ini_set('default_socket_timeout', $browser->getTimeout());

		$driver = new Selenium2Driver(
			$browser_name,
			$capabilities,
			'http://' . $browser->getHost() . ':' . $browser->getPort() . '/wd/hub'
		);

		return $driver;
	}

}
