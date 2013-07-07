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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\SessionStrategy\ISessionStrategy;
use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;
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
		$test_case = new WithoutBrowserConfig();
		$browser = new BrowserConfiguration();

		$this->assertSame($test_case, $test_case->setBrowser($browser));
		$this->assertSame($browser, $test_case->getBrowser());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \RuntimeException
	 */
	public function testGetBrowserNotSpecified()
	{
		$test_case = new WithoutBrowserConfig();
		$test_case->getBrowser();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetupSpecificBrowserDefault()
	{
		$test_case = $this->getFixture();
		$this->assertSame($test_case, $test_case->setupSpecificBrowser(array(
			'browserName' => 'safari',
		)));

		$expected = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';
		$this->assertInstanceOf($expected, $test_case->getBrowser());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetupSpecificBrowserWithSauce()
	{
		$test_case = $this->getFixture();
		$this->assertSame($test_case, $test_case->setupSpecificBrowser(array(
			'browserName' => 'safari', 'sauce' => array('username' => 'test-user', 'api_key' => 'ABC'),
		)));

		$expected = '\\aik099\\PHPUnit\\BrowserConfiguration\\SauceLabsBrowserConfiguration';
		$this->assertInstanceOf($expected, $test_case->getBrowser());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetupSpecificBrowserStrategy()
	{
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		/* @var $session_strategy ISessionStrategy */

		$test_case = $this->getFixture($session_strategy);
		$this->assertSame($test_case, $test_case->setupSpecificBrowser(array(
			'browserName' => 'safari', 'sessionStrategy' => SessionStrategyManager::ISOLATED_STRATEGY,
		)));

		$this->assertSame($session_strategy, $test_case->getSessionStrategy());
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
	 * @param string  $strategy_class Strategy class.
	 * @param boolean $shared         Is shared.
	 *
	 * @return void
	 * @dataProvider isSharedDataProvider
	 */
	public function testIsShared($strategy_class, $shared)
	{
		$test_case = new WithoutBrowserConfig();

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock($strategy_class);

		$test_case->setSessionStrategy($session_strategy);

		$this->assertSame($shared, $test_case->isShared());
	}

	/**
	 * Provides test data for IsShared method.
	 *
	 * @return array
	 */
	public function isSharedDataProvider()
	{
		return array(
			array('\\aik099\\PHPUnit\\SessionStrategy\\IsolatedSessionStrategy', false),
			array('\\aik099\\PHPUnit\\SessionStrategy\\SharedSessionStrategy', true),
		);
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

	/**
	 * Returns test case fixture.
	 *
	 * @param ISessionStrategy|null $session_strategy Session strategy.
	 *
	 * @return WithoutBrowserConfig
	 */
	protected function getFixture(ISessionStrategy $session_strategy = null)
	{
		/* @var $manager_mock \aik099\PHPUnit\SessionStrategy\SessionStrategyManager */
		$manager_mock = m::mock(self::MANAGER_CLASS);

		if ( !isset($session_strategy) ) {
			$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		}

		$test_case = new WithoutBrowserConfig();
		$manager_mock->shouldReceive('getSessionStrategy')->with($test_case)->andReturn($session_strategy);

		$test_case->setSessionStrategyManager($manager_mock);

		return $test_case;
	}

}