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


use Mockery as m;
use tests\aik099\PHPUnit\Fixture\SetupEventFixture;

class EventDispatchingTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Test description.
	 *
	 * @return void
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

		$result = new \PHPUnit_Framework_TestResult();

		$suite = SetupEventFixture::suite('tests\\aik099\\PHPUnit\\Fixture\\SetupEventFixture');
		$suite->run($result);

		$this->assertTrue($result->wasSuccessful(), 'All sub-tests passed');
	}

}
