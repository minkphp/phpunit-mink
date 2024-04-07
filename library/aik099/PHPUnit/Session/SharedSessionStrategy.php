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
use Behat\Mink\Session;
use ConsoleHelpers\PHPUnitCompat\Framework\IncompleteTestError;
use ConsoleHelpers\PHPUnitCompat\Framework\SkippedTestError;

/**
 * Keeps a Session object shared between test runs to save time.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class SharedSessionStrategy extends AbstractSessionStrategy
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
	 * @inheritDoc
	 */
	public function session(BrowserConfiguration $browser)
	{
		if ( $this->_lastTestFailed ) {
			$this->stopAndForgetSession();
			$this->_lastTestFailed = false;
		}

		if ( $this->_session === null ) {
			$this->_session = $this->_originalStrategy->session($browser);
			$this->isFreshSession = $this->_originalStrategy->isFreshSession();
		}
		else {
			$this->isFreshSession = false;
			$this->_switchToMainWindow();
		}

		return $this->_session;
	}

	/**
	 * Stops and forgets a session.
	 *
	 * @return void
	 */
	protected function stopAndForgetSession()
	{
		if ( $this->_session !== null ) {
			$this->stopSession($this->_session);
			$this->_session = null;
		}
	}

	/**
	 * Switches to window, that was created upon session creation.
	 *
	 * @return void
	 */
	private function _switchToMainWindow()
	{
		$this->_session->switchToWindow();
		$actual_initial_window_name = $this->_session->getWindowName(); // Account for initial window rename.

		foreach ( $this->_session->getWindowNames() as $name ) {
			if ( $name === $actual_initial_window_name ) {
				continue;
			}

			$this->_session->switchToWindow($name);
			$this->_session->executeScript('window.close();');
			$this->_session->switchToWindow();
		}
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

		$this->stopSession($session);
	}

}
