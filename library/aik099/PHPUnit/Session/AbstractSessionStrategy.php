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


use aik099\PHPUnit\BrowserTestCase;
use Behat\Mink\Session;

abstract class AbstractSessionStrategy implements ISessionStrategy
{

	/**
	 * Determines if the session was just started.
	 *
	 * @var boolean|null
	 */
	protected $isFreshSession;

	/**
	 * @inheritDoc
	 */
	public function isFreshSession()
	{
		return $this->isFreshSession;
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

	}

	/**
	 * @inheritDoc
	 */
	public function onTestSuiteEnded(BrowserTestCase $test_case)
	{

	}

	/**
	 * Stops the session.
	 *
	 * @param Session|null $session Session.
	 *
	 * @return void
	 */
	protected function stopSession(Session $session = null)
	{
		if ( $session !== null && $session->isStarted() ) {
			$session->stop();
			$this->isFreshSession = null;
		}
	}

}
