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
class IsolatedSessionStrategy extends AbstractSessionStrategy
{

	/**
	 * @inheritDoc
	 */
	public function session(BrowserConfiguration $browser)
	{
		$session = new Session($browser->createDriver());
		$this->isFreshSession = true;

		return $session;
	}

	/**
	 * @inheritDoc
	 */
	public function onTestEnded(BrowserTestCase $test_case)
	{
		$session = $test_case->getSession(false);

		$this->stopSession($session);
	}

}
