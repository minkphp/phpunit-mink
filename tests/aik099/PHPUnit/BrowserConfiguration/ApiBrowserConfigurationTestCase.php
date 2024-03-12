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


use aik099\PHPUnit\APIClient\IAPIClient;
use aik099\PHPUnit\BrowserConfiguration\ApiBrowserConfiguration;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use aik099\PHPUnit\Framework\TestResult;
use aik099\PHPUnit\BrowserTestCase;
use Mockery as m;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

abstract class ApiBrowserConfigurationTestCase extends BrowserConfigurationTest
{

	use ExpectException;

	const PORT = 80;

	const AUTOMATIC_TEST_NAME = 'AUTOMATIC';

	/**
	 * Desired capabilities use to configure the tunnel.
	 *
	 * @var array
	 */
	protected $tunnelCapabilities = array();

	/**
	 * API client.
	 *
	 * @var IAPIClient
	 */
	protected $apiClient;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		if ( $this->getName(false) === 'testOnTestEnded' ) {
			$this->mockBrowserMethods[] = 'getAPIClient';
		}

		parent::setUpTest();

		$this->setup['port'] = 80;
		$this->setup['apiUsername'] = 'UN';
		$this->setup['apiKey'] = 'AK';
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetup()
	{
		parent::testSetup();

		$this->assertSame($this->setup['apiUsername'], $this->browser->getApiUsername());
		$this->assertSame($this->setup['apiKey'], $this->browser->getApiKey());
	}

	public function testSnakeCaseParameters()
	{
		$this->browser->setup(array(
			'api_username' => 'old-user',
			'api_key' => 'old-key',
		));

		$this->assertEquals('old-user', $this->browser->getApiUsername());
		$this->assertEquals('old-key', $this->browser->getApiKey());

		$this->browser->setup(array(
			'api_username' => 'old-user',
			'api_key' => 'old-key',
			'apiUsername' => 'new-user',
			'apiKey' => 'new-key',
		));

		$this->assertEquals('old-user', $this->browser->getApiUsername());
		$this->assertEquals('old-key', $this->browser->getApiKey());

		$this->browser->setup(array(
			'apiUsername' => 'new-user',
			'apiKey' => 'new-key',
		));

		$this->assertEquals('new-user', $this->browser->getApiUsername());
		$this->assertEquals('new-key', $this->browser->getApiKey());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetAPICorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$this->assertEmpty($browser->getApiUsername());
		$this->assertEmpty($browser->getApiKey());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetHostCorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setApiUsername('A');
		$browser->setApiKey('B');

		$this->assertSame($browser, $browser->setHost('EXAMPLE_HOST'));
		$this->assertSame('A:B@ondemand.saucelabs.com', $browser->getHost());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetPortCorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setApiUsername('A');
		$browser->setApiKey('B');

		$this->assertSame($browser, $browser->setPort(5555));
		$this->assertSame(80, $browser->getPort());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserNameCorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setApiUsername('A');
		$browser->setApiKey('B');

		$this->assertSame($browser, $browser->setBrowserName(''));
		$this->assertSame('chrome', $browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @param array|null $desired_capabilities Desired capabilities.
	 * @param array|null $expected             Expected capabilities.
	 *
	 * @return void
	 * @dataProvider desiredCapabilitiesDataProvider
	 */
	public function testSetDesiredCapabilitiesCorrect(array $desired_capabilities = null, array $expected = null)
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setApiUsername('A');
		$browser->setApiKey('B');

		$this->assertSame($browser, $browser->setDesiredCapabilities($desired_capabilities));
		$this->assertSame($expected, $browser->getDesiredCapabilities(), 'Failed changing the desired capabilities.');
	}

	/**
	 * Desired capability data provider.
	 *
	 * @return array
	 */
	abstract public function desiredCapabilitiesDataProvider();

	/**
	 * Test description.
	 *
	 * @param string $session_strategy Session strategy.
	 * @param string $test_name        Expected job name.
	 * @param string $build_env_name   Name of ENV variable to set build number to.
	 * @param string $build_number     Build number.
	 *
	 * @return void
	 * @dataProvider setupProcessDataProvider
	 */
	public function testTestSetupProcess($session_strategy, $test_name, $build_env_name = null, $build_number = null)
	{
		// Reset any global env vars that might be left from previous tests.
		$hhvm_hack = defined('HHVM_VERSION') ? '=' : '';
		putenv('BUILD_NUMBER' . $hhvm_hack);
		putenv('TRAVIS_BUILD_NUMBER' . $hhvm_hack);

		if ( isset($build_number) ) {
			putenv($build_env_name . '=' . $build_number);
		}

		$this->browser->setSessionStrategy($session_strategy);

		$test_case = $this->createTestCase($test_name);
		$test_case->shouldReceive('toString')
			->times($this->_isAutomaticTestName($test_name) ? 0 : 1)
			->andReturn($test_name);

		if ( $this->_isAutomaticTestName($test_name) ) {
			$test_name = get_class($test_case);
		}

		$this->browser->onTestSetup($test_case);

		$desired_capabilities = $this->browser->getDesiredCapabilities();

		$this->assertArrayHasKey(ApiBrowserConfiguration::NAME_CAPABILITY, $desired_capabilities);
		$this->assertEquals($test_name, $desired_capabilities[ApiBrowserConfiguration::NAME_CAPABILITY]);

		if ( isset($build_number) ) {
			$this->assertArrayHasKey(ApiBrowserConfiguration::BUILD_NUMBER_CAPABILITY, $desired_capabilities);
			$this->assertEquals($build_number, $desired_capabilities[ApiBrowserConfiguration::BUILD_NUMBER_CAPABILITY]);
		}
		else {
			$this->assertArrayNotHasKey(ApiBrowserConfiguration::BUILD_NUMBER_CAPABILITY, $desired_capabilities);
		}
	}

	/**
	 * Checks that test name is automatic.
	 *
	 * @param string $test_name Expected job name.
	 *
	 * @return boolean
	 */
	private function _isAutomaticTestName($test_name)
	{
		return $test_name == self::AUTOMATIC_TEST_NAME;
	}

	/**
	 * Data provider for setup process test.
	 *
	 * @return array
	 */
	public function setupProcessDataProvider()
	{
		$seed = uniqid();

		return array(
			'isolated, name, jenkins' => array(
				ISessionStrategyFactory::TYPE_ISOLATED, 'TEST_NAME', 'BUILD_NUMBER', 'JENKINS ' . $seed,
			),
			'shared, no name, jenkins' => array(
				ISessionStrategyFactory::TYPE_SHARED, self::AUTOMATIC_TEST_NAME, 'BUILD_NUMBER', 'JENKINS ' . $seed,
			),
			'isolated, name, travis' => array(
				ISessionStrategyFactory::TYPE_ISOLATED, 'TEST_NAME', 'TRAVIS_BUILD_NUMBER', 'TRAVIS ' . $seed,
			),
			'shared, no name, travis' => array(
				ISessionStrategyFactory::TYPE_SHARED,
				self::AUTOMATIC_TEST_NAME,
				'TRAVIS_BUILD_NUMBER',
				'TRAVIS ' . $seed,
			),
			'isolated, name, no build' => array(ISessionStrategyFactory::TYPE_ISOLATED, 'TEST_NAME'),
			'shared, no name, no build' => array(ISessionStrategyFactory::TYPE_SHARED, self::AUTOMATIC_TEST_NAME),
		);
	}

	/**
	 * Test description.
	 *
	 * @param string $driver_type Driver.
	 *
	 * @return void
	 * @dataProvider onTestEndedDataProvider
	 */
	public function testOnTestEnded($driver_type)
	{
		$test_case = $this->createTestCase('TEST_NAME');

		$api_client = m::mock('aik099\\PHPUnit\\APIClient\\IAPIClient');
		$this->browser->shouldReceive('getAPIClient')->andReturn($api_client);

		if ( $driver_type == 'selenium' ) {
			$driver = m::mock('\\Behat\\Mink\\Driver\\Selenium2Driver');
			$driver->shouldReceive('getWebDriverSessionId')->once()->andReturn('SID');

			$api_client->shouldReceive('updateStatus')->with('SID', true, 'test status message')->once();
			$test_case->shouldReceive('hasFailed')->once()->andReturn(false); // For shared strategy.
			$test_case->shouldReceive('getStatusMessage')->once()->andReturn('test status message'); // For shared strategy.
		}
		else {
			$driver = m::mock('\\Behat\\Mink\\Driver\\DriverInterface');
			$this->expectException('RuntimeException');
		}

		$session = m::mock('Behat\\Mink\\Session');
		$session->shouldReceive('getDriver')->once()->andReturn($driver);
		$session->shouldReceive('isStarted')->once()->andReturn(true);

		$test_case->shouldReceive('getSession')->with(false)->once()->andReturn($session);

		$test_result = new TestResult(); // Can't mock, because it's a final class.

		$this->browser->onTestEnded($test_case, $test_result);
	}

	/**
	 * Returns possible drivers for session creation.
	 *
	 * @return array
	 */
	public function onTestEndedDataProvider()
	{
		return array(
			array('selenium'),
			array('other'),
		);
	}

	/**
	 * @dataProvider sessionStateDataProvider
	 */
	public function testTestEndedWithoutSession($stopped_or_missing)
	{
		$test_case = $this->createTestCase('TEST_NAME');

		if ( $stopped_or_missing ) {
			$session = m::mock('Behat\\Mink\\Session');
			$session->shouldReceive('isStarted')->once()->andReturn(false);
			$test_case->shouldReceive('getSession')->with(false)->once()->andReturn($session);
		}
		else {
			$test_case->shouldReceive('getSession')->with(false)->once();
		}

		$test_result = new TestResult(); // Can't mock, because it's a final class.

		$this->browser->onTestEnded($test_case, $test_result);
	}

	public function sessionStateDataProvider()
	{
		return array(
			'session stopped/missing' => array(true),
			'session started' => array(false),
		);
	}

	/**
	 * Create TestCase with Browser.
	 *
	 * @param string $name Test case name.
	 *
	 * @return BrowserTestCase|m\MockInterface
	 */
	protected function createTestCase($name)
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getName')->andReturn($name);

		return $test_case;
	}

	/**
	 * Test description.
	 *
	 * @param string|null $tunnel_id Tunnel ID.
	 *
	 * @return void
	 * @dataProvider tunnelIdentifierDataProvider
	 */
	public function testTunnelIdentifier($tunnel_id = null)
	{
		// Reset any global env vars that might be left from previous tests.
		$hhvm_hack = defined('HHVM_VERSION') ? '=' : '';

		putenv('PHPUNIT_MINK_TUNNEL_ID' . $hhvm_hack);

		if ( isset($tunnel_id) ) {
			putenv('PHPUNIT_MINK_TUNNEL_ID=' . $tunnel_id);
		}

		$this->browser->setSessionStrategy(ISessionStrategyFactory::TYPE_ISOLATED);

		$test_case = $this->createTestCase('TEST_NAME');
		$test_case->shouldReceive('toString')->andReturn('TEST_NAME');

		$this->browser->onTestSetup($test_case);

		$desired_capabilities = $this->browser->getDesiredCapabilities();

		if ( isset($tunnel_id) ) {
			foreach ( $this->tunnelCapabilities as $name => $value ) {
				if ( substr($value, 0, 4) === 'env:' ) {
					$value = getenv(substr($value, 4));
				}

				$this->assertArrayHasKey($name, $desired_capabilities);
				$this->assertEquals($value, $desired_capabilities[$name]);
			}
		}
		else {
			foreach ( array_keys($this->tunnelCapabilities) as $name ) {
				$this->assertArrayNotHasKey($name, $desired_capabilities);
			}
		}
	}

	/**
	 * Provides Travis job numbers.
	 *
	 * @return array
	 */
	public function tunnelIdentifierDataProvider()
	{
		return array(
			array('AAA'),
			array(null),
		);
	}

}
