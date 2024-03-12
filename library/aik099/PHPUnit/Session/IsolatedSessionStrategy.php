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
	 * @inheritDoc
	 */
	public function onTestEnded(BrowserTestCase $test_case)
	{
		$session = $test_case->getSession(false);

		if ( $session !== null && $session->isStarted() ) {
			$session->stop();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onTestFailed(BrowserTestCase $test_case, $exception)
	{

	}

	/**
	 * @inheritDoc
	 */
	public function onTestSuiteEnded(BrowserTestCase $test_case)
	{

	}

}
