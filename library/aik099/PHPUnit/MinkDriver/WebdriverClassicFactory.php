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

class WebdriverClassicFactory extends AbstractDriverFactory
{

	/**
	 * Returns driver name, that can be used in browser configuration.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'webdriver-classic';
	}

	/**
	 * @inheritDoc
	 */
	public function getDriverPackageUrl()
	{
		return 'https://packagist.org/packages/mink/webdriver-classic-driver';
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
		$this->assertInstalled('Mink\WebdriverClassicDriver\WebdriverClassicDriver');

		$browser_name = $browser->getBrowserName();
		$capabilities = $browser->getDesiredCapabilities();
		$capabilities['browserName'] = $browser_name;

		// TODO: Maybe doesn't work!
		ini_set('default_socket_timeout', $browser->getTimeout());

		$driver = new \Mink\WebdriverClassicDriver\WebdriverClassicDriver(
			$browser_name,
			$capabilities,
			'http://' . $browser->getHost() . ':' . $browser->getPort() . '/wd/hub'
		);

		return $driver;
	}

}
