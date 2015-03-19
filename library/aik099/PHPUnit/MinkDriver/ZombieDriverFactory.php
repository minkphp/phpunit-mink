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
use Behat\Mink\Driver\NodeJS\Server\ZombieServer;
use Behat\Mink\Driver\ZombieDriver;

class ZombieDriverFactory implements IMinkDriverFactory
{

	/**
	 * Return a new instance of the Zombie driver.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return ZombieDriver
	 */
	public function getInstance(BrowserConfiguration $browser)
	{
		$options = $browser->getDriverOptions();
		$node_bin = isset($options['node_binary']) ? $options['node_binary'] : null;

		return new ZombieDriver(
			new ZombieServer($browser->getHost(), $browser->getPort(), $node_bin)
		);
	}

}
