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
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\Event\TestFailedEvent;
use Behat\Mink\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return array(
			BrowserTestCase::TEST_FAILED_EVENT => array('onTestFailed', 0),
			BrowserTestCase::TEST_SUITE_ENDED_EVENT => array('onTestSuiteEnd', 0),
		);
	}

	/**
	 * Sets event dispatcher.
	 *
	 * @param EventDispatcherInterface $event_dispatcher Event dispatcher.
	 *
	 * @return void
	 */
	public function setEventDispatcher(EventDispatcherInterface $event_dispatcher)
	{
		$event_dispatcher->addSubscriber($this);
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
	 * Called, when test fails.
	 *
	 * @param TestFailedEvent $event Test failed event.
	 *
	 * @return void
	 */
	public function onTestFailed(TestFailedEvent $event)
	{
		if ( $event->getException() instanceof \PHPUnit_Framework_IncompleteTestError ) {
			return;
		}
		elseif ( $event->getException() instanceof \PHPUnit_Framework_SkippedTestError ) {
			return;
		}

		$this->_lastTestFailed = true;
	}

	/**
	 * Called, when test case ends.
	 *
	 * @param TestEvent $event Test event.
	 *
	 * @return void
	 */
	public function onTestSuiteEnd(TestEvent $event)
	{
		if ( !$this->_isEventForMe($event) ) {
			return;
		}

		$session = $event->getSession();

		if ( $session !== null && $session->isStarted() ) {
			$session->stop();
		}
	}

	/**
	 * Checks, that event can be handled by this class.
	 *
	 * @param TestEvent $event Test event.
	 *
	 * @return boolean
	 */
	private function _isEventForMe(TestEvent $event)
	{
		return $event->getTestCase()->getSessionStrategy() instanceof self;
	}

}
