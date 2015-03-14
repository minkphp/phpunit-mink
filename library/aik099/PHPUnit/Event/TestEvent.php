<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\Event;


use aik099\PHPUnit\BrowserTestCase;
use Behat\Mink\Session;
use Symfony\Component\EventDispatcher\Event;

class TestEvent extends Event
{

	/**
	 * Test case.
	 *
	 * @var BrowserTestCase
	 */
	private $_testCase;

	/**
	 * Session.
	 *
	 * @var Session
	 */
	private $_session;

	/**
	 * Creates test event.
	 *
	 * @param BrowserTestCase $test_case Test case.
	 * @param Session         $session   Session.
	 */
	public function __construct(BrowserTestCase $test_case, Session $session = null)
	{
		$this->_testCase = $test_case;
		$this->_session = $session;
	}

	/**
	 * Returns test case.
	 *
	 * @return BrowserTestCase
	 */
	public function getTestCase()
	{
		return $this->_testCase;
	}

	/**
	 * Returns session.
	 *
	 * @return Session
	 */
	public function getSession()
	{
		return $this->_session;
	}

	/**
	 * Determines if received event is designed for given test case.
	 *
	 * @param BrowserTestCase $test_case Test case for comparison.
	 *
	 * @return boolean
	 */
	public function validateSubscriber(BrowserTestCase $test_case)
	{
		$event_test_case = $this->getTestCase();

		return get_class($event_test_case) === get_class($test_case)
			&& $event_test_case->getName() === $test_case->getName()
			&& $event_test_case->getBrowser()->getChecksum() === $test_case->getBrowser()->getChecksum();
	}

}
