<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\SessionStrategy;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use Behat\Mink\Session;

/**
 * Produces a new Session object shared for each test.
 *
 * @method \Mockery\Expectation shouldReceive
 */
class IsolatedSessionStrategy implements ISessionStrategy
{

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration for a session.
	 *
	 * @return Session
	 */
	public function session(BrowserConfiguration $browser)
	{
		$session = $browser->createSession();
		$session->start();

		return $session;
	}

	/**
	 * Called, when test fails.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return self
	 */
	public function notSuccessfulTest(\Exception $e)
	{
		return $this;
	}

	/**
	 * Called, when test ends.
	 *
	 * @param Session|null $session Session.
	 *
	 * @return self
	 */
	public function endOfTest(Session $session = null)
	{
		if ( $session !== null ) {
			$session->stop();
		}

		return $this;
	}

	/**
	 * Called, when test case ends.
	 *
	 * @param Session|null $session Session.
	 *
	 * @return self
	 */
	public function endOfTestCase(Session $session = null)
	{
		return $this;
	}

}
