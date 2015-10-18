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
use Behat\Mink\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Produces a new Session object shared for each test.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class IsolatedSessionStrategy implements ISessionStrategy
{

	/**
	 * Session factory.
	 *
	 * @var ISessionFactory
	 */
	private $_sessionFactory;

	/**
	 * Creates isolated session strategy instance.
	 *
	 * @param ISessionFactory $session_factory Session factory.
	 */
	public function __construct(ISessionFactory $session_factory)
	{
		$this->_sessionFactory = $session_factory;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return array(
			BrowserTestCase::TEST_ENDED_EVENT => array('onTestEnd', 0),
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
		return $this->_sessionFactory->createSession($browser);
	}

	/**
	 * Called, when test ends.
	 *
	 * @param TestEvent $event Test event.
	 *
	 * @return void
	 */
	public function onTestEnd(TestEvent $event)
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
