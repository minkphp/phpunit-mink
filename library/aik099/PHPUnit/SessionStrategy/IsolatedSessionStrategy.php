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


use Behat\Mink\Session,
	Behat\Mink\Driver\Selenium2Driver;

/**
 * Produces a new Session object shared for each test.
 */
class IsolatedSessionStrategy implements ISessionStrategy
{

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * 'host' - Selenium Server machine.
	 * 'port' - Selenium Server port.
	 * 'browserName' => a browser name.
	 * 'baseUrl' => base URL to use during the test.
	 *
	 * @param array $parameters Browser configuration for a session.
	 *
	 * @return Session
	 * @access public
	 */
	public function session(array $parameters)
	{
		$capabilities = array_merge(
			$parameters['desiredCapabilities'],
			array('browserName' => $parameters['browserName'])
		);

		// TODO: maybe doesn't work
		ini_set('default_socket_timeout', $parameters['seleniumServerRequestsTimeout']);

		// create driver:
		$driver = new Selenium2Driver(
			$parameters['browserName'],
			$capabilities,
			'http://' . $parameters['host'] . ':' . $parameters['port'] . '/wd/hub'
		);

		// init session:
		$session = new Session($driver);

		$session->start();

		return $session;
	}

	/**
	 * Called, when test fails.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return void
	 * @access public
	 */
	public function notSuccessfulTest(\Exception $e)
	{

	}

	/**
	 * Called, when test ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 * @access public
	 */
	public function endOfTest(Session $session = null)
	{
		if ( $session !== null ) {
			$session->stop();
		}
	}

	/**
	 * Called, when test case ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 * @access public
	 */
	public function endOfTestCase(Session $session = null)
	{

	}

}
