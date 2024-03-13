<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Integration;


use ConsoleHelpers\PHPUnitCompat\Framework\TestResult;
use PHPUnit\Framework\TestFailure;
use tests\aik099\PHPUnit\AbstractTestCase;
use tests\aik099\PHPUnit\Fixture\SetupFixture;

class EventDispatchingTest extends AbstractTestCase
{

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		// Define the constant because this test is running PHPUnit testcases manually.
		if ( $this->isInIsolation() ) {
			define('PHPUNIT_TESTSUITE', true);
		}
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @large
	 * @runInSeparateProcess
	 */
	public function testSetupEvent()
	{
		/*
		 * BrowserTestCase::TEST_SETUP_EVENT
		 * - SauceLabsBrowserConfiguration::onTestSetup (called, verified)
		 *
		 * BrowserTestCase::TEST_ENDED_EVENT
		 * - IsolatedSessionStrategy::onTestEnd (called, verified)
		 * - SauceLabsBrowserConfiguration::onTestEnded (called, verified)
		 *
		 * BrowserTestCase::TEST_SUITE_ENDED_EVENT
		 * - SharedSessionStrategy::onTestSuiteEnd
		 *
		 * BrowserTestCase::TEST_FAILED_EVENT
		 * - SharedSessionStrategy::onTestFailed
		 */

		$result = new TestResult();

		$suite = SetupFixture::suite('tests\\aik099\\PHPUnit\\Fixture\\SetupFixture');
		$suite->run($result);

		$error_msgs = array();

		if ( $result->errorCount() > 0 ) {
			foreach ( $result->errors() as $error ) {
				$error_msgs[] = $this->prepareErrorMsg($error);
			}
		}

		if ( $result->failureCount() > 0 ) {
			foreach ( $result->failures() as $failure ) {
				$error_msgs[] = $this->prepareErrorMsg($failure);
			}
		}

		if ( $error_msgs ) {
			$this->fail(
				'The "SetupFixture" tests failed:' . \PHP_EOL . \PHP_EOL . implode(\PHP_EOL . ' * ', $error_msgs)
			);
		}

		$this->assertTrue(true);
	}

	/**
	 * Prepares an error msg.
	 *
	 * @param TestFailure $test_failure Exception.
	 *
	 * @return string
	 */
	protected function prepareErrorMsg(TestFailure $test_failure)
	{
		$ret = $test_failure->toString() . \PHP_EOL;
		$ret .= 'Trace:' . \PHP_EOL . $test_failure->thrownException()->getTraceAsString();

		return $ret;
	}

}
