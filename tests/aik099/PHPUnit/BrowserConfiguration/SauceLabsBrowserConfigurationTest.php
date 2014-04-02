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


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SauceLabsBrowserConfigurationTest extends ApiBrowserConfigurationTestCase
{

	const HOST = ':@ondemand.saucelabs.com';

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->testsRequireSubscriber[] = 'testTunnelIdentifier';
		$this->browserConfigurationClass = 'aik099\\PHPUnit\\BrowserConfiguration\\SauceLabsBrowserConfiguration';

		parent::setUp();

		$this->setup['host'] = 'UN:AK@ondemand.saucelabs.com';
	}

	/**
	 * Test description.
	 *
	 * @param string|null $travis_job_number Travis Job Number.
	 *
	 * @return void
	 * @dataProvider tunnelIdentifierDataProvider
	 */
	public function testTunnelIdentifier($travis_job_number = null)
	{
		// Reset any global env vars that might be left from previous tests.
		putenv('TRAVIS_JOB_NUMBER');

		if ( isset($travis_job_number) ) {
			putenv('TRAVIS_JOB_NUMBER=' . $travis_job_number);
		}

		$this->browser->setSessionStrategy(ISessionStrategyFactory::TYPE_ISOLATED);

		$test_case = $this->createTestCase('TEST_NAME');
		$test_case->shouldReceive('toString')->andReturn('TEST_NAME');

		$event_dispatcher = new EventDispatcher();
		$event_dispatcher->addSubscriber($this->browser);

		$event_dispatcher->dispatch(
			BrowserTestCase::TEST_SETUP_EVENT,
			new TestEvent($test_case, m::mock('Behat\\Mink\\Session'))
		);

		$desired_capabilities = $this->browser->getDesiredCapabilities();

		if ( isset($travis_job_number) ) {
			$this->assertArrayHasKey('tunnel-identifier', $desired_capabilities);
			$this->assertEquals($travis_job_number, $desired_capabilities['tunnel-identifier']);
		}
		else {
			$this->assertArrayNotHasKey('tunnel-identifier', $desired_capabilities);
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
				array('version' => 'ver1', 'platform' => 'Windows 7'),
			),
		);
	}

}
