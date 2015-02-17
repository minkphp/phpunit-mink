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
use Behat\Mink\Driver\SahiDriver;

class SahiDriverFactory implements IMinkDriverFactory
{

	/**
	 * Return a new SahiDriver instance
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return SahiDriver
	 */
	public function getInstance(BrowserConfiguration $browser)
	{
		$connection = new \Behat\SahiClient\Connection(null, $browser->getHost(), $browser->getPort());
		$driver = new SahiDriver($browser->getBrowserName(), new \Behat\SahiClient\Client($connection));

		return $driver;
	}

}
