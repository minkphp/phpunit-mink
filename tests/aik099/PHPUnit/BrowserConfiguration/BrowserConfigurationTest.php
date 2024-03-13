<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\BrowserConfiguration;


use aik099\PHPUnit\BrowserConfiguration\ApiBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use ConsoleHelpers\PHPUnitCompat\Framework\TestResult;
use Mockery as m;
use tests\aik099\PHPUnit\AbstractTestCase;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;

class BrowserConfigurationTest extends AbstractTestCase
{

	use ExpectException;

	const TEST_CASE_CLASS = BrowserTestCase::class;

	const HOST = 'example_host';

	const PORT = 1234;

	/**
	 * Browser configuration class.
	 *
	 * @var string
	 */
	protected $browserConfigurationClass = '';

	/**
	 * Hostname.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Port.
	 *
	 * @var integer
	 */
	protected $port;

	/**
	 * Complete setup.
	 *
	 * @var array
	 */
	protected $setup = array();

	/**
	 * Browser configuration.
	 *
	 * @var BrowserConfiguration|ApiBrowserConfiguration
	 */
	protected $browser;

	/**
	 * Browser methods, that needs to be mocked.
	 *
	 * @var array
	 */
	protected $mockBrowserMethods = array();

	/**
	 * Driver factory registry.
	 *
	 * @var DriverFactoryRegistry|m\MockInterface
	 */
	protected $driverFactoryRegistry;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		if ( !$this->browserConfigurationClass ) {
			$this->browserConfigurationClass = BrowserConfiguration::class;
		}

		$this->setup = array(
			'host' => self::HOST,
			'port' => self::PORT,
			'timeout' => 500,
			'browserName' => 'safari',
			'desiredCapabilities' => array('platform' => 'Windows 10', 'version' => 10),
			'baseUrl' => 'http://other-host',
			'sessionStrategy' => ISessionStrategyFactory::TYPE_SHARED,
			'driver' => 'zombie',
			'driverOptions' => array('customSetting' => 'customValue'),
		);

		$this->driverFactoryRegistry = $this->createDriverFactoryRegistry();

		$this->browser = $this->createBrowserConfiguration();
	}

	/**
	 * Creates driver factory registry.
	 *
	 * @return DriverFactoryRegistry
	 */
	protected function createDriverFactoryRegistry()
	{
		$registry = m::mock(DriverFactoryRegistry::class);

		$selenium2_driver_factory = m::mock(IMinkDriverFactory::class);
		$selenium2_driver_factory->shouldReceive('getDriverDefaults')->andReturn(array(
			'baseUrl' => 'http://www.super-url.com',
			'driverOptions' => array(
				'driverParam1' => 'driverParamValue1',
			),
		));
		$registry
			->shouldReceive('get')
			->with('selenium2')
			->andReturn($selenium2_driver_factory);

		$zombie_driver_factory = m::mock(IMinkDriverFactory::class);
		$zombie_driver_factory->shouldReceive('getDriverDefaults')->andReturn(array());
		$registry
			->shouldReceive('get')
			->with('zombie')
			->andReturn($zombie_driver_factory);

		return $registry;
	}

	/**
	 * Test description.
	 *
	 * @param array $aliases         Test case aliases.
	 * @param array $browser_config  Browser config.
	 * @param array $expected_config Expected browser config.
	 *
	 * @return void
	 * @dataProvider aliasResolutionDataProvider
	 */
	public function testAliasResolution(array $aliases, array $browser_config, array $expected_config)
	{
		$this->assertSame($this->browser, $this->browser->setAliases($aliases));
		$this->browser->setup($browser_config);

		$this->assertEquals($expected_config['host'], $this->browser->getHost());
		$this->assertEquals($expected_config['port'], $this->browser->getPort());
		$this->assertEquals($expected_config['browserName'], $this->browser->getBrowserName());
		$this->assertEquals($expected_config['baseUrl'], $this->browser->getBaseUrl());
	}

	/**
	 * Alias resolution checking data provider.
	 *
	 * @return array
	 */
	public static function aliasResolutionDataProvider()
	{
		return array(
			'single alias' => array(
				array(
					'a1' => array('host' => static::HOST, 'port' => static::PORT),
				),
				array('alias' => 'a1'),
				array(
					'host' => static::HOST, 'port' => static::PORT, 'browserName' => 'firefox',
					'baseUrl' => 'http://www.super-url.com', // Comes from driver defaults.
				),
			),
			'recursive alias' => array(
				array(
					'a1' => array('alias' => 'a2', 'host' => static::HOST, 'port' => static::PORT),
					'a2' => array('browserName' => 'safari', 'baseUrl' => 'http://example_host'),
				),
				array('alias' => 'a1'),
				array(
					'host' => static::HOST,
					'port' => static::PORT,
					'browserName' => 'safari',
					'baseUrl' => 'http://example_host',
				),
			),
			'alias merging' => array(
				array(
					'a1' => array('host' => static::HOST, 'port' => static::PORT),
				),
				array('alias' => 'a1', 'browserName' => 'firefox'),
				array(
					'host' => static::HOST, 'port' => static::PORT, 'browserName' => 'firefox',
					'baseUrl' => 'http://www.super-url.com', // Comes from driver defaults.
				),
			),
			'with overwrite' => array(
				array(
					'a1' => array('host' => 'alias-host', 'port' => static::PORT),
				),
				array('alias' => 'a1', 'host' => static::HOST),
				array(
					'host' => static::HOST, 'port' => static::PORT, 'browserName' => 'firefox',
					'baseUrl' => 'http://www.super-url.com', // Comes from driver defaults.
				),
			),
			'without alias given' => array(
				array(),
				array('host' => static::HOST, 'port' => static::PORT, 'browserName' => 'safari'),
				array(
					'host' => static::HOST, 'port' => static::PORT, 'browserName' => 'safari',
					'baseUrl' => 'http://www.super-url.com', // Comes from driver defaults.
				),
			),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveAliasesUsingIncorrectAlias()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setup(array('alias' => 'not_found'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetup()
	{
		$this->assertSame($this->browser, $this->browser->setup($this->setup));
		$this->assertSame($this->setup['host'], $this->browser->getHost());
		$this->assertSame($this->setup['port'], $this->browser->getPort());
		$this->assertSame($this->setup['timeout'], $this->browser->getTimeout());
		$this->assertSame($this->setup['browserName'], $this->browser->getBrowserName());
		$this->assertSame(
			$this->setup['desiredCapabilities'],
			$this->browser->getDesiredCapabilities(),
			'The browser\'s desired capabilities weren\'t set to the test case.'
		);
		$this->assertSame($this->setup['baseUrl'], $this->browser->getBaseUrl());
		$this->assertSame($this->setup['sessionStrategy'], $this->browser->getSessionStrategy());
		$this->assertSame($this->setup['driver'], $this->browser->getDriver());
		$this->assertSame(
			$this->setup['driverOptions'],
			$this->browser->getDriverOptions(),
			'The browser\'s driver options weren\'t set to the test case.'
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetupScreamsAboutUnknownParameters()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setup(array('unknown-parameter' => 'value'));
	}

	public function testGetType()
	{
		$this->assertEquals('default', $this->browser->getType());
	}

	public function testSetDriverIncorrect()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('The Mink driver name must be a string');

		$this->browser->setDriver(array());
	}

	public function testSetDriverCorrect()
	{
		$expected = 'zombie';
		$this->assertEquals($this->browser, $this->browser->setDriver($expected));
		$this->assertSame($expected, $this->browser->getDriver());
	}

	public function testSetDriverOptionsCorrect()
	{
		$user_driver_options = array('o1' => 'v1', 'o2' => 'v2');
		$expected_driver_options = array('driverParam1' => 'driverParamValue1') + $user_driver_options;

		$this->assertSame($this->browser, $this->browser->setDriverOptions($user_driver_options));
		$this->assertSame(
			$expected_driver_options,
			$this->browser->getDriverOptions(),
			'The browser driver options wren\'t set correctly.'
		);
	}

	public function testDriverFactoryDefaultsApplied()
	{
		$this->browser->setup(array('driver' => 'selenium2'));

		$this->assertEquals('http://www.super-url.com', $this->browser->getBaseUrl());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetHostIncorrect()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setHost(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetHostCorrect()
	{
		$expected = 'EXAMPLE_HOST';
		$this->assertSame($this->browser, $this->browser->setHost($expected));
		$this->assertSame($expected, $this->browser->getHost());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetPortIncorrect()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setPort('5555');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetPortCorrect()
	{
		$expected = 5555;
		$this->assertSame($this->browser, $this->browser->setPort($expected));
		$this->assertSame($expected, $this->browser->getPort());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserNameIncorrect()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setBrowserName(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserNameCorrect()
	{
		$expected = 'firefox';
		$this->assertSame($this->browser, $this->browser->setBrowserName($expected));
		$this->assertSame($expected, $this->browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBaseUrlIncorrect()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setBaseUrl(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBaseUrlCorrect()
	{
		$expected = 'http://some-url';
		$this->assertSame($this->browser, $this->browser->setBaseUrl($expected));
		$this->assertSame($expected, $this->browser->getBaseUrl());
	}

	/**
	 * Test description.
	 *
	 * @param array|null $desired_capabilities Desired capabilities.
	 * @param array|null $expected             Expected capabilities.
	 *
	 * @return void
	 * @see    SauceLabsBrowserConfigurationTest::testSetDesiredCapabilitiesCorrect()
	 */
	public function testSetDesiredCapabilitiesCorrect(array $desired_capabilities = null, array $expected = null)
	{
		$expected = array('k1' => 'v1', 'k2' => 'v2');
		$this->assertSame($this->browser, $this->browser->setDesiredCapabilities($expected));
		$this->assertSame(
			$expected,
			$this->browser->getDesiredCapabilities(),
			'The browser desired capabilities wren\'t set correctly.'
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetTimeoutIncorrect()
	{
		$this->expectException('InvalidArgumentException');

		$this->browser->setTimeout('5555');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetTimeoutCorrect()
	{
		$expected = 1000;
		$this->assertSame($this->browser, $this->browser->setTimeout($expected));
		$this->assertSame($expected, $this->browser->getTimeout());
	}

	/**
	 * Test description.
	 *
	 * @param string $expected Expected strategy.
	 *
	 * @return void
	 * @dataProvider sessionSharingDataProvider
	 */
	public function testSetSessionStrategy($expected)
	{
		$this->assertSame($this->browser, $this->browser->setSessionStrategy($expected));
		$this->assertSame($expected, $this->browser->getSessionStrategy());
	}

	public function testGetParameterIncorrect()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Unable to get unknown parameter "nonExisting"');

		$this->browser->getNonExisting();
	}

	public function testCreateDriver()
	{
		/** @var m\MockInterface $driver_factory */
		$driver_factory = $this->driverFactoryRegistry->get('selenium2');
		$driver_factory->shouldReceive('createDriver')->with($this->browser)->once()->andReturn('OK');

		$this->assertEquals('OK', $this->browser->createDriver());
	}

	public function testNonExistingMethod()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage(
			'Method "nonExistingMethod" does not exist on ' . get_class($this->browser) . ' class'
		);

		$this->browser->nonExistingMethod();
	}

	/**
	 * Test description.
	 *
	 * @param string $session_strategy Session strategy name.
	 *
	 * @return void
	 * @dataProvider sessionSharingDataProvider
	 */
	public function testGetSessionStrategyHashBrowserSharing($session_strategy)
	{
		/** @var BrowserTestCase $test_case */
		$test_case = m::mock(self::TEST_CASE_CLASS);

		$browser1 = $this->createBrowserConfiguration();
		$browser1->setSessionStrategy($session_strategy);

		$browser2 = $this->createBrowserConfiguration();
		$browser2->setSessionStrategy($session_strategy);

		$this->assertSame(
			$browser1->getSessionStrategyHash($test_case),
			$browser2->getSessionStrategyHash($test_case)
		);
	}

	/**
	 * Provides test data for session strategy hash sharing testing.
	 *
	 * @return array
	 */
	public static function sessionSharingDataProvider()
	{
		return array(
			array(ISessionStrategyFactory::TYPE_ISOLATED),
			array(ISessionStrategyFactory::TYPE_SHARED),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionStrategyHashNotSharing()
	{
		$test_case1 = new WithBrowserConfig('test name');
		$browser1 = $this->createBrowserConfiguration();
		$browser1->setSessionStrategy(ISessionStrategyFactory::TYPE_SHARED);

		$test_case2 = new WithoutBrowserConfig('test name');
		$browser2 = $this->createBrowserConfiguration();
		$browser2->setSessionStrategy(ISessionStrategyFactory::TYPE_SHARED);

		$this->assertNotSame(
			$browser1->getSessionStrategyHash($test_case1),
			$browser2->getSessionStrategyHash($test_case2)
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetTestStatusIsolated()
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('hasFailed')->once()->andReturn(false);
		$test_result = new TestResult(); // Can't mock, because it's a final class.

		$this->browser->setSessionStrategy(ISessionStrategyFactory::TYPE_ISOLATED);
		$this->assertTrue($this->browser->getTestStatus($test_case, $test_result));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetTestStatusShared()
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_result = new TestResult(); // Can't mock, because it's a final class.

		$this->browser->setSessionStrategy(ISessionStrategyFactory::TYPE_SHARED);
		$this->assertTrue($this->browser->getTestStatus($test_case, $test_result));
	}

	public function testChecksumMatch()
	{
		$browser1 = $this->createBrowserConfiguration();
		$browser1->setBrowserName('opera');

		$browser2 = $this->createBrowserConfiguration();
		$browser2->setBrowserName('opera');

		$this->assertSame($browser1->getChecksum(), $browser2->getChecksum());
	}

	public function testChecksumMismatch()
	{
		$browser1 = $this->createBrowserConfiguration();
		$browser1->setBrowserName('opera');

		$browser2 = $this->createBrowserConfiguration();
		$browser2->setBrowserName('firefox');

		$this->assertNotSame($browser1->getChecksum(), $browser2->getChecksum());
	}

	/**
	 * Creates instance of browser configuration.
	 *
	 * @return BrowserConfiguration
	 */
	protected function createBrowserConfiguration()
	{
		/** @var BrowserConfiguration $browser */
		$browser = new $this->browserConfigurationClass($this->driverFactoryRegistry);
		$browser->setAliases();

		return $browser;
	}

}
