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
use aik099\PHPUnit\IEventDispatcherAware;
use Behat\Mink\Session;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Specifies how to create Session objects for running tests.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
interface ISessionStrategy extends EventSubscriberInterface, IEventDispatcherAware
{

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration for a session.
	 *
	 * @return Session
	 */
	public function session(BrowserConfiguration $browser);

}
