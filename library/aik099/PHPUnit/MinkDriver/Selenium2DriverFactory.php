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

class Selenium2DriverFactory extends AbstractDriverFactory
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
	 * @inheritDoc
	 */
	public function getDriverPackageUrl()
	{
		return 'https://packagist.org/packages/behat/mink-selenium2-driver';
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
	 * @inheritDoc
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		$this->assertInstalled('Behat\Mink\Driver\Selenium2Driver');

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
