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


use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;
use Mockery as m;

class SauceLabsBrowserConfigurationTest extends BrowserConfigurationTest
{

	const HOST = ':@ondemand.saucelabs.com';

	const PORT = 80;

	const AUTOMATIC_TEST_NAME = 'AUTOMATIC';

	/**
	 * Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory|MockInterface
	 */
	private $_browserConfigurationFactory;

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->_browserConfigurationFactory = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\IBrowserConfigurationFactory');

		parent::setUp();

		$this->setup['host'] = 'UN:AK@ondemand.saucelabs.com';
		$this->setup['port'] = 80;
		$this->setup['sauce'] = array('username' => 'UN', 'api_key' => 'AK');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetup()
	{
		parent::testSetup();

		$this->assertSame($this->setup['sauce'], $this->browser->getSauce());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSauceIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setSauce(array());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSauceCorrect()
	{
		$expected = array('username' => '', 'api_key' => '');
		$browser = $this->createBrowserConfiguration();

		$this->assertSame($browser, $browser->setSauce($expected));
		$this->assertSame($expected, $browser->getSauce());
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
	public function desiredCapabilitiesDataProvider()
	{
		return array(
			array(
				array('platform' => 'pl1'),
				array('platform' => 'pl1', 'version' => ''),
			),
			array(
				array('version' => 'ver1'),
				array('version' => 'ver1', 'platform' => 'Windows XP'),
			),
		);
	}

	/**
	 * Test description.
	 *
	 * @param string $session_strategy Session strategy.
	 * @param string $test_name        Expected job name.
	 * @param string $build_number     Build number.
	 *
	 * @return void
	 * @dataProvider setupEventDataProvider
	 */
	public function testTestSetupEvent($session_strategy, $test_name, $build_number = null)
	{
		putenv('BUILD_NUMBER' . ($build_number ? '=' . $build_number : ''));

		$this->browser->setSessionStrategy($session_strategy);

		$event_dispatcher = new EventDispatcher();
		$event_dispatcher->addSubscriber($this->browser);

		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('toString')->times($this->_isAutomaticTestName($test_name) ? 0 : 1)->andReturn($test_name);

		if ( $this->_isAutomaticTestName($test_name) ) {
			$test_name = get_class($test_case);
		}

		$event_dispatcher->dispatch(
			BrowserTestCase::TEST_SETUP_EVENT,
			new TestEvent($test_case, m::mock('Behat\\Mink\\Session'))
		);

		$desired_capabilities = $this->browser->getDesiredCapabilities();

		$this->assertArrayHasKey(SauceLabsBrowserConfiguration::NAME_CAPABILITY, $desired_capabilities);
		$this->assertEquals($test_name, $desired_capabilities[SauceLabsBrowserConfiguration::NAME_CAPABILITY]);

		if ( isset($build_number) ) {
			$this->assertArrayHasKey(SauceLabsBrowserConfiguration::BUILD_NUMBER_CAPABILITY, $desired_capabilities);
			$this->assertEquals($build_number, $desired_capabilities[SauceLabsBrowserConfiguration::BUILD_NUMBER_CAPABILITY]);
		}
		else {
			$this->assertArrayNotHasKey(SauceLabsBrowserConfiguration::BUILD_NUMBER_CAPABILITY, $desired_capabilities);
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
		return array(
			'isolated, name, build' => array(ISessionStrategyFactory::TYPE_ISOLATED, 'TEST_NAME', 'BUILD_NUMBER'),
			'shared, no name, build' => array(ISessionStrategyFactory::TYPE_SHARED, self::AUTOMATIC_TEST_NAME, 'BUILD_NUMBER'),
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
		$test_case = m::mock(self::TEST_CASE_CLASS);

		$sauce_rest = m::mock('WebDriver\\SauceLabs\\SauceRest');
		$this->_browserConfigurationFactory->shouldReceive('createAPIClient')->with($this->browser)->andReturn($sauce_rest);

		if ( $driver_type == 'selenium' ) {
			$driver = m::mock('\\Behat\\Mink\\Driver\\Selenium2Driver');
			$driver->shouldReceive('getWebDriverSessionId')->once()->andReturn('SID');

			$sauce_rest->shouldReceive('updateJob')->with('SID', array('passed' => true))->once();
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
		$event_dispatcher = new EventDispatcher();
		$event_dispatcher->addSubscriber($this->browser);

		$event = m::mock('aik099\\PHPUnit\\Event\\TestEndedEvent');
		$event->shouldReceive('getSession')->once();
		$event->shouldReceive('setDispatcher')->once(); // To remove with Symfony 3.0 release.
		$event->shouldReceive('setName')->once(); // To remove with Symfony 3.0 release.
		$event->shouldReceive('isPropagationStopped')->once()->andReturn(false);
		$event->shouldReceive('getTestCase')->never();

		$this->eventDispatcher->shouldReceive('removeSubscriber')->with($this->browser)->once();
		$returned_event = $event_dispatcher->dispatch(BrowserTestCase::TEST_ENDED_EVENT, $event);

		$this->assertInstanceOf('aik099\\PHPUnit\\Event\\TestEndedEvent', $returned_event);
	}

	/**
	 * Creates instance of browser configuration.
	 *
	 * @param array   $aliases        Aliases.
	 * @param boolean $add_subscriber Expect addition of subscriber to event dispatcher.
	 * @param boolean $with_sauce     Include test sauce configuration.
	 *
	 * @return SauceLabsBrowserConfiguration
	 */
	protected function createBrowserConfiguration(array $aliases = array(), $add_subscriber = false, $with_sauce = false)
	{
		$browser = new SauceLabsBrowserConfiguration($this->_browserConfigurationFactory);
		$browser->setAliases($aliases);

		$browser->setEventDispatcher($this->eventDispatcher);
		$this->eventDispatcher->shouldReceive('addSubscriber')->with($browser)->times($add_subscriber ? 1 : 0);

		if ( $with_sauce ) {
			$browser->setSauce(array('username' => 'A', 'api_key' => 'B'));
		}

		return $browser;
	}

}
