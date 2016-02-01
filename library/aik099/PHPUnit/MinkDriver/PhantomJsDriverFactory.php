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

class PhantomJsDriverFactory implements IMinkDriverFactory
{

	/**
	 * Returns driver name, that can be used in browser configuration.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'phantomjs';
	}

	/**
	 * Returns default values for browser configuration.
	 *
	 * @return array
	 */
	public function getDriverDefaults()
	{
		return array(
			'host' => 'localhost',
			'driver' => 'phantomjs',
			'driverOptions' => array(
				'api_endpoint' => '/api',
			),
			'browserName' => 'phantomjs',
			'port' => 8510,
		);
	}

	/**
	 * Returns a new driver instance according to the browser configuration.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 * @throws \RuntimeException
	 * @return DriverInterface
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		if ( !class_exists('Zumba\Mink\Driver\PhantomJSDriver') ) {
			throw new \RuntimeException(
				'Install MinkPhantomJSDriver in order to use phantomjs driver.'
			);
		}

		$driver_options = $browser->getDriverOptions();

		return new \Zumba\Mink\Driver\PhantomJSDriver(sprintf('http://%s:%s%s',
			$browser->getHost(),
			$browser->getPort(),
			$driver_options['api_endpoint'])
		);
	}

}
