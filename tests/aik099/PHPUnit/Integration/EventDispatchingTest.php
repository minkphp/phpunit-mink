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


use aik099\PHPUnit\AbstractPHPUnitCompatibilityTestCase;
use aik099\PHPUnit\Framework\TestResult;
use tests\aik099\PHPUnit\Fixture\SetupFixture;

class EventDispatchingTest extends AbstractPHPUnitCompatibilityTestCase
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

		$this->assertTrue($result->wasSuccessful(), 'All sub-tests passed');
	}

}
