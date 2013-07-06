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
 * Keeps a Session object shared between test runs to save time.
 *
 * @method \Mockery\Expectation shouldReceive
 */
class SharedSessionStrategy implements ISessionStrategy
{

	/**
	 * Original session strategy.
	 *
	 * @var ISessionStrategy
	 */
	private $_originalStrategy;

	/**
	 * Reference to created session.
	 *
	 * @var Session
	 */
	private $_session;

	/**
	 * Window name, which was opened upon session creation.
	 *
	 * @var string
	 */
	private $_mainWindow;

	/**
	 * Remembers if last test failed.
	 *
	 * @var boolean
	 */
	private $_lastTestWasNotSuccessful = false;

	/**
	 * Remembers original session strategy upon shared strategy creation.
	 *
	 * @param ISessionStrategy $original_strategy Original session strategy.
	 */
	public function __construct(ISessionStrategy $original_strategy)
	{
		$this->_originalStrategy = $original_strategy;
	}

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration for a session.
	 *
	 * @return Session
	 */
	public function session(BrowserConfiguration $browser)
	{
		if ( $this->_lastTestWasNotSuccessful ) {
			if ( $this->_session !== null ) {
				$this->_session->stop();
				$this->_session = null;
			}

			$this->_lastTestWasNotSuccessful = false;
		}

		if ( $this->_session === null ) {
			$this->_session = $this->_originalStrategy->session($browser);
			$this->rememberMainWindow();
		}
		else {
			// if session is reused, then switch to window, that was created along with session creation
			$this->restoreMainWindow();
		}

		return $this->_session;
	}

	/**
	 * Remember window name, which was created along with session.
	 *
	 * @return self
	 */
	protected function rememberMainWindow()
	{
		$driver = $this->_session->getDriver();
		/* @var $driver \Behat\Mink\Driver\Selenium2Driver */

		$wd_session = $driver->getWebDriverSession();
		$this->_mainWindow = $wd_session->window_handle();

		return $this;
	}

	/**
	 * Switches to window, that was created upon session creation.
	 *
	 * @return self
	 */
	protected function restoreMainWindow()
	{
		$this->_session->switchToWindow($this->_mainWindow);

		return $this;
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
		if ( $e instanceof \PHPUnit_Framework_IncompleteTestError ) {
			return $this;
		}
		elseif ( $e instanceof \PHPUnit_Framework_SkippedTestError ) {
			return $this;
		}

		$this->_lastTestWasNotSuccessful = true;

		return $this;
	}

	/**
	 * Called, when test ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return self
	 */
	public function endOfTest(Session $session = null)
	{
		return $this;
	}

	/**
	 * Called, when test case ends.
	 *
	 * @param Session $session Session.
	 *
	 * @return self
	 */
	public function endOfTestCase(Session $session = null)
	{
		if ( $session !== null ) {
			$session->stop();
		}

		return $this;
	}

}
