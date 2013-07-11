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
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\SessionStrategy\ISessionStrategy;
use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;
use Behat\Mink\Session;
use Mockery as m;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;

class BrowserTestCaseTest extends \PHPUnit_Framework_TestCase
{

	const BROWSER_CLASS = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

	const MANAGER_CLASS = '\\aik099\\PHPUnit\\SessionStrategy\\SessionStrategyManager';

	const SESSION_STRATEGY_INTERFACE = '\\aik099\\PHPUnit\\SessionStrategy\\ISessionStrategy';

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategyManager()
	{
		/* @var $manager \aik099\PHPUnit\SessionStrategy\SessionStrategyManager */
		$manager = m::mock(self::MANAGER_CLASS);

		$test_case = new WithoutBrowserConfig();
		$test_case->setSessionStrategyManager($manager);

		$property = new \ReflectionProperty($test_case, 'sessionStrategyManager');
		$property->setAccessible(true);

		$this->assertSame($manager, $property->getValue($test_case));
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
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		/* @var $session_strategy ISessionStrategy */

		$browser = new BrowserConfiguration();
		$test_case = $this->getFixture($session_strategy);

		$this->assertSame($test_case, $test_case->setBrowser($browser));
		$this->assertSame($browser, $test_case->getBrowser());
		$this->assertSame($session_strategy, $test_case->getSessionStrategy());
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
	public function testSetBrowserFromConfigurationDefault()
	{
		$test_case = $this->getFixture();
		$this->assertSame($test_case, $test_case->setBrowserFromConfiguration(array(
			'browserName' => 'safari',
		)));

		$this->assertInstanceOf(self::BROWSER_CLASS, $test_case->getBrowser());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserFromConfigurationWithSauce()
	{
		$test_case = $this->getFixture();
		$this->assertSame($test_case, $test_case->setBrowserFromConfiguration(array(
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
	public function testSetBrowserFromConfigurationStrategy()
	{
		/* @var $test_case BrowserTestCase */
		$test_case = m::mock('\\aik099\\PHPUnit\\BrowserTestCase[setBrowser]');
		$test_case->shouldReceive('setBrowser')->once()->andReturn($test_case);

		$this->assertSame($test_case, $test_case->setBrowserFromConfiguration(array(
			'browserName' => 'safari',
		)));
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
	public function testGetSession()
	{
		/* @var $expected_session Session */
		$expected_session = m::mock('\\Behat\\Mink\\Session');

		$browser = $this->getBrowser();

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		$session_strategy->shouldReceive('session')->with($browser)->andReturn($expected_session);

		$test_case = $this->getFixture($session_strategy);
		$test_case->setBrowser($browser);

		$session1 = $test_case->getSession();
		$session2 = $test_case->getSession();

		$this->assertSame($expected_session, $session1);
		$this->assertSame($session1, $session2);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \Exception
	 * @expectedExceptionMessage MSG_SKIP
	 */
	public function testGetSessionDriverError()
	{
		$browser = $this->getBrowser();

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		$session_strategy->shouldReceive('session')->andThrow('\Behat\Mink\Exception\DriverException');

		$test_case = $this->getFixture($session_strategy, array('markTestSkipped'));
		$test_case->setBrowser($browser);

		$test_case->shouldReceive('markTestSkipped')->once()->andThrow('\Exception', 'MSG_SKIP');
		$test_case->getSession();
	}

	/**
	 * Returns browser mock.
	 *
	 * @return BrowserConfiguration
	 */
	protected function getBrowser()
	{
		$browser = m::mock(self::BROWSER_CLASS);
		$browser->shouldReceive('getSessionStrategy')->once()->andReturnNull();
		$browser->shouldReceive('getSessionStrategyHash')->once()->andReturnNull();
		$browser->shouldReceive('getHost')->andReturnNull();
		$browser->shouldReceive('getPort')->andReturnNull();

		return $browser;
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
		$test_case = $this->getFixture();

//		$remote_coverage = $test_case->getRemoteCodeCoverage();

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
	 * Returns test case fixture.
	 *
	 * @param ISessionStrategy|null $session_strategy Session strategy.
	 * @param array                 $mock_methods     Method names to mock.
	 *
	 * @return WithoutBrowserConfig
	 */
	protected function getFixture(ISessionStrategy $session_strategy = null, array $mock_methods = array())
	{
		if ( !isset($session_strategy) ) {
			$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		}

		/* @var $manager \aik099\PHPUnit\SessionStrategy\SessionStrategyManager */
		$manager = m::mock(self::MANAGER_CLASS);
		$manager->shouldReceive('getSessionStrategy')->andReturn($session_strategy);

		if ( $mock_methods ) {
			$test_case = m::mock('\\aik099\\PHPUnit\\BrowserTestCase[' . implode(',', $mock_methods) . ']');
		}
		else {
			$test_case = new WithoutBrowserConfig();
		}

		$test_case->setSessionStrategyManager($manager);

		return $test_case;
	}

}
