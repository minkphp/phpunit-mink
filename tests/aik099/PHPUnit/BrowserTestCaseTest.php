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


use aik099\PHPUnit\AbstractPHPUnitCompatibilityTestCase;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageTool;
use aik099\PHPUnit\Session\ISessionStrategy;
use aik099\PHPUnit\Session\SessionStrategyManager;
use Mockery as m;
use Mockery\MockInterface;
use aik099\PHPUnit\Framework\TestResult;
use aik099\SebastianBergmann\CodeCoverage\CodeCoverage;
use aik099\SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\ProcessedCodeCoverageData;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class BrowserTestCaseTest extends AbstractPHPUnitCompatibilityTestCase
{

	use ExpectException;

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
	 * @before
	 */
	protected function setUpTest()
	{
		// Define the constant because this test is running PHPUnit testcases manually.
		if ( $this->isInIsolation() ) {
			define('PHPUNIT_TESTSUITE', true);
		}

		$this->browserConfigurationFactory = m::mock(
			'aik099\\PHPUnit\\BrowserConfiguration\\IBrowserConfigurationFactory'
		);
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

		$browser = new BrowserConfiguration($this->createDriverFactoryRegistry());

		$this->assertSame($test_case, $test_case->setBrowser($browser));
		$this->assertSame($browser, $test_case->getBrowser());
		$this->assertSame($session_strategy, $test_case->getSessionStrategy());
	}

	/**
	 * Creates driver factory registry.
	 *
	 * @return DriverFactoryRegistry
	 */
	protected function createDriverFactoryRegistry()
	{
		$registry = m::mock('\\aik099\\PHPUnit\\MinkDriver\\DriverFactoryRegistry');

		$driver_factory = m::mock('\\aik099\\PHPUnit\\MinkDriver\\IMinkDriverFactory');
		$driver_factory->shouldReceive('getDriverDefaults')->andReturn(array());

		$registry
			->shouldReceive('get')
			->with('selenium2')
			->andReturn($driver_factory);

		return $registry;
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetBrowserNotSpecified()
	{
		$this->expectException('RuntimeException');

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
		$expected_session2 = m::mock('\\Behat\\Mink\\Session');

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);
		$session_strategy->shouldReceive('session')->with($browser)->andReturn($expected_session1, $expected_session2);

		$test_case = $this->getFixture($session_strategy);
		$test_case->setBrowser($browser);
		$test_case->setTestResultObject(new TestResult());

		// Create session when missing.
		$session1 = $test_case->getSession();
		$this->assertSame($expected_session1, $session1);

		// Always reuse created session.
		$session2 = $test_case->getSession();
		$this->assertSame($session1, $session2);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionDriverError()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('MSG_SKIP');

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
		$browser->shouldReceive('getHost')->times($times);
		$browser->shouldReceive('getPort')->times($times);

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

		$test_result = new TestResult();

		// Can't mock due to class being marked as final.
		if ( \interface_exists('\PHP_CodeCoverage_Driver') ) {
			$code_coverage = new CodeCoverage(
				m::mock('\PHP_CodeCoverage_Driver'),
				new Filter()
			);
		}
		else {
			$code_coverage = new CodeCoverage(
				m::mock('\\aik099\\SebastianBergmann\\CodeCoverage\\Driver\\Driver'),
				new Filter()
			);
		}

		$test_result->setCodeCoverage($code_coverage);
		$test_case->setTestResultObject($test_result);

		$this->assertTrue($test_case->getCollectCodeCoverageInformation());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @large
	 * @runInSeparateProcess
	 */
	public function testRun()
	{
		/* @var $test_case BrowserTestCase */
		list($test_case,) = $this->prepareForRun();
		$result = new TestResult();

		$this->assertSame($result, $test_case->run($result));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @large
	 * @runInSeparateProcess
	 */
	public function testRunCreateResult()
	{
		/* @var $test_case BrowserTestCase */
		list($test_case,) = $this->prepareForRun();

		$this->assertInstanceOf('\\aik099\\PHPUnit\\Framework\\TestResult', $test_case->run());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @large
	 * @runInSeparateProcess
	 */
	public function testRunWithCoverageWithoutRemoteUrl()
	{
		/* @var $test_case BrowserTestCase */
		/* @var $session_strategy ISessionStrategy */
		list($test_case, $session_strategy) = $this->prepareForRun();
		$test_case->setName('getTestId');
		$test_case->setRemoteCoverageHelper($this->getRemoteCoverageHelperMock());

		$result = new TestResult();
		$code_coverage = $this->getCodeCoverageMock(array(
			$this->getCoverageFixtureFile() => array (
				7 => -1, // Means line not executed.
				8 => -1,
				9 => -1,
				12 => -1,
				13 => -1,
				14 => -1,
			),
		));
		$result->setCodeCoverage($code_coverage);

		$test_id = $test_case->getTestId();
		$this->assertEmpty($test_id);

		$browser = $test_case->getBrowser();
		$browser->shouldReceive('getBaseUrl')->once()->andReturn('A');

		$session = m::mock('\\Behat\\Mink\\Session');
		$session->shouldReceive('visit')->with('A')->once();
		$session->shouldReceive('setCookie')->with(RemoteCoverageTool::TEST_ID_VARIABLE, null)->once();
		$session->shouldReceive('setCookie')->with(RemoteCoverageTool::TEST_ID_VARIABLE, m::not(''))->once();

		$session_strategy->shouldReceive('session')->once()->andReturn($session);

		$test_case->run($result);

		if ( \class_exists('\SebastianBergmann\CodeCoverage\ProcessedCodeCoverageData') ) {
			$actual_coverage = $code_coverage->getData();
			$expected_coverage = new ProcessedCodeCoverageData();
			$expected_coverage->setLineCoverage(array(
				$this->getCoverageFixtureFile() => array(
					8 => array(), // First "return null;" statement.
					13 => array(), // Second "return null;" statement.
				),
			));
			$this->assertEquals($expected_coverage, $actual_coverage);
		}
		else {
			$actual_coverage = $code_coverage->getData();
			$expected_coverage = array(
				$this->getCoverageFixtureFile() => array (
					7 => array(), // Means, that this test hasn't executed a tested code.
					8 => array(),
					9 => array(),
					12 => array(),
					13 => array(),
					14 => array(),
				),
			);
			$this->assertEquals($expected_coverage, $actual_coverage);
		}

		$this->assertNotEmpty($test_case->getTestId());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @large
	 * @runInSeparateProcess
	 */
	public function testRunWithCoverage()
	{
		$expected_coverage = array(
			$this->getCoverageFixtureFile() => array (
				7 => 1, // Means line executed.
				8 => -1, // Means, that this test hasn't executed a tested code.
				9 => 1,
				12 => 1,
				13 => 1,
				14 => 1,
			),
		);

		/* @var $test_case BrowserTestCase */
		/* @var $session_strategy ISessionStrategy */
		list($test_case, $session_strategy) = $this->prepareForRun();
		$test_case->setName('getTestId');
		$test_case->setRemoteCoverageHelper($this->getRemoteCoverageHelperMock($expected_coverage));
		$test_case->setRemoteCoverageScriptUrl('some-url');

		$result = new TestResult();
		$code_coverage = $this->getCodeCoverageMock($expected_coverage);
		$result->setCodeCoverage($code_coverage);

		$test_id = $test_case->getTestId();
		$this->assertEmpty($test_id);

		$browser = $test_case->getBrowser();
		$browser->shouldReceive('getBaseUrl')->once()->andReturn('A');

		$session = m::mock('\\Behat\\Mink\\Session');
		$session->shouldReceive('visit')->with('A')->once();
		$session->shouldReceive('setCookie')->with(RemoteCoverageTool::TEST_ID_VARIABLE, null)->once();
		$session->shouldReceive('setCookie')->with(RemoteCoverageTool::TEST_ID_VARIABLE, m::not(''))->once();

		$session_strategy->shouldReceive('session')->once()->andReturn($session);

		$test_case->run($result);

		$covered_by_test = 'tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig::getTestId';

		if ( \class_exists('\SebastianBergmann\CodeCoverage\ProcessedCodeCoverageData') ) {
			$expected_coverage = new ProcessedCodeCoverageData();
			$expected_coverage->setLineCoverage(array(
				$this->getCoverageFixtureFile() => array(
					8 => array(), // Means, that this test hasn't executed a tested code.
					13 => array($covered_by_test, $covered_by_test), // Means, covered by this test twice.
				),
			));
			$actual_coverage = $code_coverage->getData();
			$this->assertEquals($expected_coverage, $actual_coverage);
		}
		else {
			$actual_coverage = $code_coverage->getData();
			$expected_coverage = array(
				$this->getCoverageFixtureFile() => array (
					7 => array($covered_by_test), // Means, covered by this test.
					8 => array(), // Means, that this test hasn't executed a tested code.
					9 => array($covered_by_test),
					12 => array($covered_by_test),
					13 => array($covered_by_test),
					14 => array($covered_by_test),
				),
			);
			$this->assertEquals($expected_coverage, $actual_coverage);
		}

		$this->assertNotEmpty($test_case->getTestId());
	}

	/**
	 * Returns the coverage fixture file.
	 *
	 * @return string
	 */
	protected function getCoverageFixtureFile()
	{
		return \realpath(__DIR__ . '/Fixture/DummyClass.php');
	}

	/**
	 * Returns remote coverage helper mock.
	 *
	 * @param array|null $expected_coverage Expected coverage.
	 *
	 * @return RemoteCoverageHelper
	 */
	protected function getRemoteCoverageHelperMock(array $expected_coverage = null)
	{
		$remote_coverage_helper = m::mock('aik099\\PHPUnit\\RemoteCoverage\\RemoteCoverageHelper');

		if ( $expected_coverage !== null ) {
			if ( \class_exists('\SebastianBergmann\CodeCoverage\RawCodeCoverageData') ) {
				$remote_coverage_helper
					->shouldReceive('get')
					->with('some-url', 'tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig__getTestId')
					->andReturn(RawCodeCoverageData::fromXdebugWithoutPathCoverage($expected_coverage));
			}
			else {
				$remote_coverage_helper
					->shouldReceive('get')
					->with('some-url', 'tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig__getTestId')
					->andReturn($expected_coverage);
			}
		}

		if ( \class_exists('\SebastianBergmann\CodeCoverage\RawCodeCoverageData') ) {
			$remote_coverage_helper
				->shouldReceive('getEmpty')
				->andReturn(RawCodeCoverageData::fromXdebugWithoutPathCoverage(array()));
		}
		else {
			$remote_coverage_helper
				->shouldReceive('getEmpty')
				->andReturn(array());
		}

		return $remote_coverage_helper;
	}

	/**
	 * Returns code coverage mock.
	 *
	 * @param array $expected_coverage Expected coverage.
	 *
	 * @return CodeCoverage
	 */
	protected function getCodeCoverageMock(array $expected_coverage)
	{
		if ( \interface_exists('\PHP_CodeCoverage_Driver') ) {
			$driver = m::mock('\PHP_CodeCoverage_Driver');
		}
		else {
			$driver = m::mock('\\aik099\\SebastianBergmann\\CodeCoverage\\Driver\\Driver');
		}

		$driver->shouldReceive('start')->once();

		// Can't assert call count, because expectations are verified prior to coverage being queried.
		if ( \class_exists('\SebastianBergmann\CodeCoverage\RawCodeCoverageData') ) {
			$driver->shouldReceive('stop')
				/*->once()*/
				->andReturn(
					RawCodeCoverageData::fromXdebugWithoutPathCoverage($expected_coverage)
				);
		}
		else {
			$driver->shouldReceive('stop')->/*once()->*/andReturn($expected_coverage);
		}

		$filter = new Filter();

		if ( \method_exists($filter, 'addFileToWhitelist') ) {
			$filter->addFileToWhitelist($this->getCoverageFixtureFile());
		}
		elseif ( \method_exists($filter, 'includeFile') ) {
			$filter->includeFile($this->getCoverageFixtureFile());
		}

		return new CodeCoverage($driver, $filter);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestCase()
	{
		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = new WithoutBrowserConfig();
		$test_case->setSessionStrategy($session_strategy);

		$session_strategy->shouldReceive('onTestSuiteEnded')->with($test_case)->once();

		$this->assertSame($test_case, $test_case->onTestSuiteEnded());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testOnTestFailed()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('MSG_TEST');

		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = $this->getFixture($session_strategy);
		$test_case->setSessionStrategy($session_strategy);

		$exception = new \Exception('MSG_TEST');
		$session_strategy->shouldReceive('onTestFailed')->with($test_case, $exception)->once();

		$reflection_method = new \ReflectionMethod($test_case, 'onNotSuccessfulTest');
		$reflection_method->setAccessible(true);

		$reflection_method->invokeArgs($test_case, array($exception));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetBrowserAliases()
	{
		$test_case = $this->getFixture();

		$this->assertEmpty($test_case->getBrowserAliases(), 'Browser configuration aliases are empty by default');
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
		/* @var $session_strategy ISessionStrategy */
		$session_strategy = m::mock(self::SESSION_STRATEGY_INTERFACE);

		$test_case = $this->getFixture($session_strategy, $mock_methods);
		$test_case->setName('testSuccess');

		$session_strategy->shouldReceive('onTestEnded')->with($test_case)->once();

		$browser = $this->getBrowser(0);
		$browser->shouldReceive('onTestSetup')->with($test_case)->once();
		$browser->shouldReceive('onTestEnded')->with($test_case, m::type(TestResult::class))->once();

		$test_case->setBrowser($browser);

		return array($test_case, $session_strategy);
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

		$test_case->setBrowserConfigurationFactory($this->browserConfigurationFactory);
		$test_case->setSessionStrategyManager($manager);

		return $test_case;
	}

}
