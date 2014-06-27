<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\Session;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;

/**
 * Produces sessions.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class SessionFactory implements ISessionFactory
{

	/**
	 * Creates new session based on browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return Session
	 */
	public function createSession(BrowserConfiguration $browser)
	{
		return new Session($this->_createDriver($browser));
	}

	/**
	 * Creates driver based on browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return DriverInterface
	 */
	private function _createDriver(BrowserConfiguration $browser)
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
