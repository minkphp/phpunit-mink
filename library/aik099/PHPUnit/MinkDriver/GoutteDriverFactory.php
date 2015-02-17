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
use Behat\Mink\Driver\GoutteDriver;

class GoutteDriverFactory implements IMinkDriverFactory
{

	/**
	 * Return a new instance of the Goutte driver.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return GoutteDriver
	 */
	public function getInstance(BrowserConfiguration $browser)
	{
		return new GoutteDriver();
	}

}
