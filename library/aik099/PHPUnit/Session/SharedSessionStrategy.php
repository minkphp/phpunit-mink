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
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Framework\IncompleteTestError;
use aik099\PHPUnit\Framework\SkippedTestError;
use Behat\Mink\Session;

/**
 * Keeps a Session object shared between test runs to save time.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
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
	 * Remembers if last test failed.
	 *
	 * @var boolean
	 */
	private $_lastTestFailed = false;

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
		if ( $this->_lastTestFailed ) {
			$this->stopSession();
			$this->_lastTestFailed = false;
		}

		if ( $this->_session === null ) {
			$this->_session = $this->_originalStrategy->session($browser);
		}
		else {
			$this->_switchToMainWindow();
		}

		return $this->_session;
	}

	/**
	 * Stops session.
	 *
	 * @return void
	 */
	protected function stopSession()
	{
		if ( $this->_session === null ) {
			return;
		}

		$this->_session->stop();
		$this->_session = null;
	}

	/**
	 * Switches to window, that was created upon session creation.
	 *
	 * @return void
	 */
	private function _switchToMainWindow()
	{
		$this->_session->switchToWindow(null);
	}

	/**
	 * @inheritDoc
	 */
	public function onTestEnded(BrowserTestCase $test_case)
	{

	}

	/**
	 * @inheritDoc
	 */
	public function onTestFailed(BrowserTestCase $test_case, $exception)
	{
		if ( $exception instanceof IncompleteTestError || $exception instanceof SkippedTestError ) {
			return;
		}

		$this->_lastTestFailed = true;
	}

	/**
	 * @inheritDoc
	 */
	public function onTestSuiteEnded(BrowserTestCase $test_case)
	{
		$session = $test_case->getSession(false);

		if ( $session !== null && $session->isStarted() ) {
			$session->stop();
		}
	}

}
