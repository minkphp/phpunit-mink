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
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use aik099\PHPUnit\BrowserTestCase;
use Mockery as m;

abstract class ApiBrowserConfigurationTestCase extends BrowserConfigurationTest
{

	const PORT = 80;

	const AUTOMATIC_TEST_NAME = 'AUTOMATIC';

	/**
	 * Browser configuration class.
	 *
	 * @var string
	 */
	protected $browserConfigurationClass = '';

	/**
	 * Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory|MockInterface
	 */
	protected $browserConfigurationFactory;

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->testsRequireSubscriber[] = 'testTestSetupEvent';
		$this->testsRequireSubscriber[] = 'testTestEndedEvent';
		$this->testsRequireSubscriber[] = 'testTestEndedWithoutSession';
		$this->browserConfigurationFactory = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\IBrowserConfigurationFactory');

		parent::setUp();

		$this->setup['port'] = 80;
		$this->setup['api_username'] = 'UN';
		$this->setup['api_key'] = 'AK';
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetup()
	{
		parent::testSetup();

		$this->assertSame($this->setup['api_username'], $this->browser->getApiUsername());
		$this->assertSame($this->setup['api_key'], $this->browser->getApiKey());
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
		$browser = $this->createBrowserConfiguration(array(), false, true);

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
		$browser = $this->createBrowserConfiguration(array(), false, true);
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
		$browser = $this->createBrowserConfiguration(array(), false, true);
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
		$browser = $this->createBrowserConfiguration(array(), false, true);
		$this->assertSame($browser, $browser->setDesiredCapabilities($desired_capabilities));
		$this->assertSame($expected, $browser->getDesiredCapabilities());
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
	 * @dataProvider setupEventDataProvider
	 */
	public function testTestSetupEvent($session_strategy, $test_name, $build_env_name = null, $build_number = null)
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
		$test_case->shouldReceive('toString')->times($this->_isAutomaticTestName($test_name) ? 0 : 1)->andReturn($test_name);

		$event_dispatcher = new EventDispatcher();
		$event_dispatcher->addSubscriber($this->browser);

		if ( $this->_isAutomaticTestName($test_name) ) {
			$test_name = get_class($test_case);
		}

		$event_dispatcher->dispatch(
			BrowserTestCase::TEST_SETUP_EVENT,
			new TestEvent($test_case, m::mock('Behat\\Mink\\Session'))
		);

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
	 * Data provider for TestSetup event handler.
	 *
	 * @return array
	 */
	public function setupEventDataProvider()
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
				ISessionStrategyFactory::TYPE_SHARED, self::AUTOMATIC_TEST_NAME, 'TRAVIS_BUILD_NUMBER', 'TRAVIS ' . $seed,
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
	 * @dataProvider theTestEndedEventDataProvider
	 */
	public function testTestEndedEvent($driver_type)
	{
		$test_case = $this->createTestCase('TEST_NAME');

		$api_client = m::mock('aik099\\PHPUnit\\APIClient\\IAPIClient');
		$this->browserConfigurationFactory->shouldReceive('createAPIClient')->with($this->browser)->andReturn($api_client);

		if ( $driver_type == 'selenium' ) {
			$driver = m::mock('\\Behat\\Mink\\Driver\\Selenium2Driver');
			$driver->shouldReceive('getWebDriverSessionId')->once()->andReturn('SID');

			$api_client->shouldReceive('updateStatus')->with('SID', true)->once();
			$test_case->shouldReceive('hasFailed')->once()->andReturn(false); // For shared strategy.
		}
		else {
			$driver = m::mock('\\Behat\\Mink\\Driver\\DriverInterface');
			$this->setExpectedException('RuntimeException');
		}

		$session = m::mock('Behat\\Mink\\Session');
		$session->shouldReceive('getDriver')->once()->andReturn($driver);

		$event_dispatcher = new EventDispatcher();
		$event_dispatcher->addSubscriber($this->browser);

		$test_result = m::mock('PHPUnit_Framework_TestResult');

		$this->eventDispatcher->shouldReceive('removeSubscriber')->with($this->browser)->once();

		$event = $event_dispatcher->dispatch(
			BrowserTestCase::TEST_ENDED_EVENT,
			new TestEndedEvent($test_case, $test_result, $session)
		);

		$this->assertInstanceOf('aik099\\PHPUnit\\Event\\TestEndedEvent', $event);
	}

	/**
	 * Returns possible drivers for session creation.
	 *
	 * @return array
	 */
	public function theTestEndedEventDataProvider()
	{
		return array(
			array('selenium'),
			array('other'),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testTestEndedWithoutSession()
	{
		$test_case = $this->createTestCase('TEST_NAME');

		$event_dispatcher = new EventDispatcher();
		$event_dispatcher->addSubscriber($this->browser);

		$event = m::mock('aik099\\PHPUnit\\Event\\TestEndedEvent');
		$event->shouldReceive('getSession')->once();
		$event->shouldReceive('setDispatcher')->once(); // To remove with Symfony 3.0 release.
		$event->shouldReceive('setName')->once(); // To remove with Symfony 3.0 release.
		$event->shouldReceive('isPropagationStopped')->once()->andReturn(false);
		$event->shouldReceive('getTestCase')->andReturn($test_case);

		$this->eventDispatcher->shouldReceive('removeSubscriber')->with($this->browser)->once();
		$returned_event = $event_dispatcher->dispatch(BrowserTestCase::TEST_ENDED_EVENT, $event);

		$this->assertInstanceOf('aik099\\PHPUnit\\Event\\TestEndedEvent', $returned_event);
	}

	/**
	 * Create TestCase with Browser.
	 *
	 * @param string $name Test case name.
	 *
	 * @return BrowserTestCase
	 */
	protected function createTestCase($name)
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('setRemoteCoverageScriptUrl')->once();
		$test_case->shouldReceive('getName')->andReturn($name);
		$this->browser->attachToTestCase($test_case);

		return $test_case;
	}

	/**
	 * Creates instance of browser configuration.
	 *
	 * @param array   $aliases        Aliases.
	 * @param boolean $add_subscriber Expect addition of subscriber to event dispatcher.
	 * @param boolean $with_api       Include test API configuration.
	 *
	 * @return ApiBrowserConfiguration
	 */
	protected function createBrowserConfiguration(array $aliases = array(), $add_subscriber = false, $with_api = false)
	{
		/** @var ApiBrowserConfiguration $browser */
		$browser = new $this->browserConfigurationClass($this->eventDispatcher, $this->browserConfigurationFactory);
		$browser->setAliases($aliases);

		$this->eventDispatcher->shouldReceive('addSubscriber')->with($browser)->times($add_subscriber ? 1 : 0);

		if ( $with_api ) {
			$browser->setApiUsername('A');
			$browser->setApiKey('B');
		}

		return $browser;
	}

}
