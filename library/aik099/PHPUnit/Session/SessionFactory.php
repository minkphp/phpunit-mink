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
		return new Session($browser->createDriver());
	}

}
