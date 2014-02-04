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
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Session\ISessionStrategy;
use aik099\PHPUnit\Session\SessionStrategyManager;
use Mockery as m;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;

class BrowserTestCaseTest extends \PHPUnit_Framework_TestCase
{

	const BROWSER_CLASS = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

	const MANAGER_CLASS = '\\aik099\\PHPUnit\\Session\\SessionStrategyManager';

	const SESSION_STRATEGY_INTERFACE = '\\aik099\\PHPUnit\\Session\\ISessionStrategy';

	/**
	 *  Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory|MockInterface
	 */
	protected $browserConfigurationFactory;

	/**
	 * Event dispatcher.
	 *
	 * @var EventDispatcherInterface|MockInterface
	 */
	protected $eventDispatcher;

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->browserConfigurationFactory = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\IBrowserConfigurationFactory');
		$this->eventDispatcher = m::mock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategyManager()
	{
		/* @var $manager SessionStrategyManager */
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
	public function testSetBrowserCorrect()
	{
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		/* @var $session_strategy ISessionStrategy */

		$test_case = $this->getFixture($session_strategy);

		$browser = new BrowserConfiguration();
		$browser->setEventDispatcher($this->eventDispatcher);
		$this->eventDispatcher->shouldReceive('addSubscriber')->with($browser)->once();

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

		$browser = $this->getBrowser(0);
		$browser_config = array('browserName' => 'safari');

		$this->browserConfigurationFactory
			->shouldReceive('createBrowserConfiguration')
			->with($browser_config, $test_case)
			->once()
			->andReturn($browser);

		$this->assertSame($test_case, $test_case->setBrowserFromConfiguration($browser_config));
		$this->assertInstanceOf(self::BROWSER_CLASS, $test_case->getBrowser());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategy()
	{
		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = new WithoutBrowserConfig();
		$this->assertSame($test_case, $test_case->setSessionStrategy($session_strategy));
		$this->assertSame($session_strategy, $test_case->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @depends testSetSessionStrategyManager
	 */
	public function testGetSessionStrategySharing()
	{
		/* @var $manager SessionStrategyManager */
		$manager = m::mock(self::MANAGER_CLASS);

		$manager->shouldReceive('getDefaultSessionStrategy')->twice()->andReturn('STRATEGY');

		$test_case1 = new WithoutBrowserConfig();
		$test_case1->setSessionStrategyManager($manager);

		$test_case2 = new WithBrowserConfig();
		$test_case2->setSessionStrategyManager($manager);

		$this->assertSame($test_case1->getSessionStrategy(), $test_case2->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSession()
	{
		$browser = $this->getBrowser(0);

		$expected_session1 = m::mock('\\Behat\\Mink\\Session');
		$expected_session1->shouldReceive('isStarted')->withNoArgs()->once()->andReturn(false);

		$expected_session2 = m::mock('\\Behat\\Mink\\Session');
		$expected_session2->shouldReceive('isStarted')->withNoArgs()->once()->andReturn(true);

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		$session_strategy->shouldReceive('session')->with($browser)->andReturn($expected_session1, $expected_session2);

		$test_case = $this->getFixture($session_strategy);
		$test_case->setBrowser($browser);
		$test_case->setTestResultObject($this->getTestResult($test_case, 0));

		// create session when missing
		$session1 = $test_case->getSession();
		$this->assertSame($expected_session1, $session1);

		// create session when present, but stopped
		$session2 = $test_case->getSession();
		$this->assertSame($expected_session2, $session2);

		// reuse created session, when started
		$session3 = $test_case->getSession();
		$this->assertSame($session2, $session3);
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
		$browser = $this->getBrowser(1);

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
	 * @param integer $times How much times configuration should be read.
	 *
	 * @return BrowserConfiguration
	 */
	protected function getBrowser($times)
	{
		$browser = m::mock(self::BROWSER_CLASS);
		$browser->shouldReceive('getHost')->times($times)->andReturnNull();
		$browser->shouldReceive('getPort')->times($times)->andReturnNull();
		$browser->shouldReceive('attachToTestCase')->once()->andReturn($browser);

		return $browser;
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetCollectCodeCoverageInformationSuccess()
	{
		$test_case = $this->getFixture();
		$test_result = $this->getTestResult($test_case, 0, true);
		$test_case->setTestResultObject($test_result);

		$this->assertTrue($test_case->getCollectCodeCoverageInformation());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \RuntimeException
	 */
	public function testGetCollectCodeCoverageInformationFailure()
	{
		$this->getFixture()->getCollectCodeCoverageInformation();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRun()
	{
		/* @var $test_case BrowserTestCase */
		list($test_case,) = $this->prepareForRun();
		$result = $this->getTestResult($test_case, 1);

		$this->assertSame($result, $test_case->run($result));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRunCreateResult()
	{
		/* @var $test_case BrowserTestCase */
		list($test_case,) = $this->prepareForRun();

		$this->assertInstanceOf('\\PHPUnit_Framework_TestResult', $test_case->run());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRunWithCoverage()
	{
		/* @var $test_case BrowserTestCase */
		/* @var $session_strategy ISessionStrategy */
		list($test_case, $session_strategy) = $this->prepareForRun(array('getRemoteCodeCoverageInformation'));
		$test_case->setName('getTestId');

		$expected_coverage = array('test1' => 'test2');
		$test_case->shouldReceive('getRemoteCodeCoverageInformation')->andReturn($expected_coverage);

		$code_coverage = m::mock('\\PHP_CodeCoverage');
		$code_coverage->shouldReceive('append')->with($expected_coverage, $test_case)->once()->andReturnNull();

		$result = $this->getTestResult($test_case, 1, true);
		$result->shouldReceive('getCodeCoverage')->once()->andReturn($code_coverage);

		$test_id = $test_case->getTestId();
		$this->assertEmpty($test_id);

		$browser = $test_case->getBrowser();
		$browser->shouldReceive('getBaseUrl')->once()->andReturn('A');

		$session = m::mock('\\Behat\\Mink\\Session');
		$session->shouldReceive('visit')->with('A')->once()->andReturnNull();
		$session->shouldReceive('setCookie')->with('PHPUNIT_SELENIUM_TEST_ID', null)->once()->andReturnNull();
		$session->shouldReceive('setCookie')->with('PHPUNIT_SELENIUM_TEST_ID', m::not(''))->once()->andReturnNull();

		$session_strategy->shouldReceive('session')->once()->andReturn($session);

		$test_case->run($result);

		$this->assertNotEmpty($test_case->getTestId());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestCase()
	{
		$this->expectEvent(BrowserTestCase::TEST_CASE_ENDED_EVENT);

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = new WithoutBrowserConfig();
		$test_case->setEventDispatcher($this->eventDispatcher);
		$test_case->setSessionStrategy($session_strategy);

		$this->assertSame($test_case, $test_case->endOfTestCase());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \Exception
	 * @expectedExceptionMessage MSG_TEST
	 */
	public function testOnTestFailed()
	{
		$this->expectEvent(BrowserTestCase::TEST_FAILED_EVENT);

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = $this->getFixture($session_strategy);
		$test_case->setSessionStrategy($session_strategy);

		$reflection_method = new \ReflectionMethod($test_case, 'onNotSuccessfulTest');
		$reflection_method->setAccessible(true);

		$reflection_method->invokeArgs($test_case, array(new \Exception('MSG_TEST')));
	}

	/**
	 * Prepares test case to be used by "run" method.
	 *
	 * @param array $mock_methods Method names to mock.
	 *
	 * @return array
	 */
	protected function prepareForRun(array $mock_methods = array())
	{
		$this->expectEvent(BrowserTestCase::TEST_SETUP_EVENT);
		$this->expectEvent(BrowserTestCase::TEST_ENDED_EVENT);

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = $this->getFixture($session_strategy, $mock_methods);
		$test_case->setName('testSuccess');

		$browser = $this->getBrowser(0);
		$test_case->setBrowser($browser);

		return array($test_case, $session_strategy);
	}

	/**
	 * Returns test result.
	 *
	 * @param BrowserTestCase $test_case        Browser test case.
	 * @param integer         $run_count        Test run count.
	 * @param boolean         $collect_coverage Should collect coverage information.
	 *
	 * @return \PHPUnit_Framework_TestResult|MockInterface
	 */
	protected function getTestResult(BrowserTestCase $test_case, $run_count, $collect_coverage = false)
	{
		$result = m::mock('\\PHPUnit_Framework_TestResult');
		$result->shouldReceive('getCollectCodeCoverageInformation')->withNoArgs()->andReturn($collect_coverage);

		$result->shouldReceive('run')->with($test_case)->times($run_count)->andReturnUsing(function () use ($test_case) {
			$test_case->runBare();
		});

		return $result;
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

		/* @var $manager SessionStrategyManager */
		$manager = m::mock(self::MANAGER_CLASS);
		$manager->shouldReceive('getSessionStrategy')->andReturn($session_strategy);

		if ( $mock_methods ) {
			$test_case = m::mock('\\aik099\\PHPUnit\\BrowserTestCase[' . implode(',', $mock_methods) . ']');
		}
		else {
			$test_case = new WithoutBrowserConfig();
		}

		$test_case->setEventDispatcher($this->eventDispatcher);
		$test_case->setBrowserConfigurationFactory($this->browserConfigurationFactory);
		$test_case->setSessionStrategyManager($manager);

		return $test_case;
	}

	/**
	 * Expects a specific event to be called.
	 *
	 * @param string $event_name Event name.
	 *
	 * @return void
	 */
	protected function expectEvent($event_name)
	{
		$this->eventDispatcher
			->shouldReceive('dispatch')
			->with($event_name, m::any())
			->once();
	}

}
