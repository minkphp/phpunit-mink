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


use Behat\Mink\Session;

/**
 * Specifies how to create Session objects for running tests.
 */
interface ISessionStrategy
{

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * 'host' - Selenium Server machine.
	 * 'port' - Selenium Server port.
	 * 'browser' => a browser name.
	 * 'baseUrl' => base URL to use during the test.
	 *
	 * @param array $parameters Browser configuration for a session.
	 *
	 * @return Session
	 * @access public
	 */
	public function session(array $parameters);

	/**
	 * Called, when test fails.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return void
	 * @access public
	 */
	public function notSuccessfulTest(\Exception $e);

	/**
	 * Called, when test ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 * @access public
	 */
	public function endOfTest(Session $session = null);

	/**
	 * Called, when test case ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 * @access public
	 */
	public function endOfTestCase(Session $session = null);

}
