<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit;


use Mockery as m;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;

class BrowserTestCaseTest extends \PHPUnit_Framework_TestCase
{

	const MANAGER_CLASS = '\\aik099\\PHPUnit\\SessionStrategy\\SessionStrategyManager';

	const SESSION_STRATEGY_INTERFACE = '\\aik099\\PHPUnit\\SessionStrategy\\ISessionStrategy';

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategyManager()
	{
		/* @var $mock \aik099\PHPUnit\SessionStrategy\SessionStrategyManager */
		$mock = m::mock(self::MANAGER_CLASS);

		$test_case = new WithoutBrowserConfig();
		$test_case->setSessionStrategyManager($mock);

		$property = new \ReflectionProperty($test_case, 'sessionStrategyManager');
		$property->setAccessible(true);

		$this->assertSame($mock, $property->getValue($test_case));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSauceBrowserPatching()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserCorrect()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetBrowserNotSpecified()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetupSpecificBrowser()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategy()
	{
		/* @var $mock \aik099\PHPUnit\SessionStrategy\ISessionStrategy */
		$mock = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = new WithoutBrowserConfig();
		$this->assertSame($test_case, $test_case->setSessionStrategy($mock));
		$this->assertSame($mock, $test_case->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @depends testSetSessionStrategyManager
	 */
	public function testGetSessionStrategySharing()
	{
		/* @var $mock \aik099\PHPUnit\SessionStrategy\SessionStrategyManager */
		$mock = m::mock(self::MANAGER_CLASS);

		$mock->shouldReceive('getDefaultSessionStrategy')->twice()->andReturn('STRATEGY');

		$test_case1 = new WithoutBrowserConfig();
		$test_case1->setSessionStrategyManager($mock);

		$test_case2 = new WithBrowserConfig();
		$test_case2->setSessionStrategyManager($mock);

		$this->assertSame($test_case1->getSessionStrategy(), $test_case2->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testIsShared()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSession()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRun()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRunWithCoverage()
	{
//		// create coverage
//		$mock_coverage = m::mock('\\PHP_CodeCoverage');
//
//		// create result
//		$mock_result = m::mock('\PHPUnit_Framework_TestResult');
//		$mock_result->setCodeCoverage($mock_coverage);
//
//		$test_case = new WithoutBrowserConfig();
//		$result = $test_case->run($mock_result);

		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRunWithSauce()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetRemoteCodeCoverage()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetTestId()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSaucePassed()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSauceLabsConnectorCorrect()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSauceLabsConnectorIncorrect()
	{
		$this->markTestSkipped('TODO');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testWithSauce()
	{
		$this->markTestSkipped('TODO');
	}

}