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
 * Keeps a Session object shared between test runs to save time.

 */
class SharedSessionStrategy implements ISessionStrategy
{

	/**
	 * Original session strategy.
	 *
	 * @var ISessionStrategy
	 */
	private $_original;

	/**
	 * Reference to created session.
	 *
	 * @var Session
	 * @access private
	 */
	private $_session;

	/**
	 * Window name, which was opened upon session creation.
	 *
	 * @var string
	 */
	private $_mainWindowName;

	/**
	 * Remembers if last test failed.
	 *
	 * @var boolean
	 * @access private
	 */
	private $_lastTestWasNotSuccessful = false;

	/**
	 * Remembers original session strategy upon shared strategy creation.
	 *
	 * @param ISessionStrategy $original_strategy Original session strategy.
	 */
	public function __construct(ISessionStrategy $original_strategy)
	{
		$this->_original = $original_strategy;
	}

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
	 */
	public function session(array $parameters)
	{
		if ( $this->_lastTestWasNotSuccessful ) {
			if ( $this->_session !== null ) {
				$this->_session->stop();
				$this->_session = null;
			}

			$this->_lastTestWasNotSuccessful = false;
		}

		if ( $this->_session === null ) {
			$this->_session = $this->_original->session($parameters);

			$driver = $this->_session->getDriver();
			/* @var $driver \Behat\Mink\Driver\Selenium2Driver */

			$wd_session = $driver->getWebDriverSession();
			$this->_mainWindowName = $wd_session->window_handle();
		}
		else {
			// if session is reused, then switch to window, that was created along with session creation
			$this->_session->switchToWindow($this->_mainWindowName);
		}

		return $this->_session;
	}

	/**
	 * Called, when test fails.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return void
	 */
	public function notSuccessfulTest(\Exception $e)
	{
		if ( $e instanceof \PHPUnit_Framework_IncompleteTestError ) {
			return;
		}
		elseif ( $e instanceof \PHPUnit_Framework_SkippedTestError ) {
			return;
		}

		$this->_lastTestWasNotSuccessful = true;
	}

	/**
	 * Called, when test ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 */
	public function endOfTest(Session $session = null)
	{

	}

	/**
	 * Called, when test case ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 */
	public function endOfTestCase(Session $session = null)
	{
		if ( $session !== null ) {
			$session->stop();
		}
	}

}
