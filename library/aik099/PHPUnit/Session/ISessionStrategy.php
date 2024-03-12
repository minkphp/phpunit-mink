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
 * Specifies how to create Session objects for running tests.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
interface ISessionStrategy
{

	/**
	 * Returns Mink session with given browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration for a session.
	 *
	 * @return Session
	 */
	public function session(BrowserConfiguration $browser);

	/**
	 * Hook, called from "BrowserTestCase::tearDownTest" method.
	 *
	 * @param BrowserTestCase $test_case Test case.
	 *
	 * @return   void
	 * @internal
	 */
	public function onTestEnded(BrowserTestCase $test_case);

	/**
	 * Hook, called from "BrowserTestCase::onNotSuccessfulTestCompatibilized" method.
	 *
	 * @param BrowserTestCase       $test_case Test case.
	 * @param \Exception|\Throwable $exception Exception.
	 *
	 * @return   void
	 * @internal
	 */
	public function onTestFailed(BrowserTestCase $test_case, $exception);

	/**
	 * Hook, called from "BrowserTestCase::onTestSuiteEnded" method.
	 *
	 * @param BrowserTestCase $test_case Test case.
	 *
	 * @return   void
	 * @internal
	 */
	public function onTestSuiteEnded(BrowserTestCase $test_case);

}
