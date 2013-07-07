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
 * Specifies how to create Session objects for running tests.
 */
interface ISessionStrategy
{

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration for a session.
	 *
	 * @return Session
	 */
	public function session(BrowserConfiguration $browser);

	/**
	 * Called, when test fails.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return self
	 */
	public function notSuccessfulTest(\Exception $e);

	/**
	 * Called, when test ends.
	 *
	 * @param Session|null $session Session.
	 *
	 * @return self
	 */
	public function endOfTest(Session $session = null);

	/**
	 * Called, when test case ends.
	 *
	 * @param Session|null $session Session.
	 *
	 * @return self
	 */
	public function endOfTestCase(Session $session = null);

}
