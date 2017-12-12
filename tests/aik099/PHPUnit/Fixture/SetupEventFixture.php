<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Fixture;


use aik099\PHPUnit\APIClient\IAPIClient;
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use Mockery as m;

class SetupEventFixture extends BrowserTestCase
{

	/**
	 * Creating browser configuration that would listen for events.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$api_client = m::mock(IAPIClient::class);
		$api_client->shouldReceive('updateStatus')->withAnyArgs()->once();

		/** @var IBrowserConfigurationFactory $factory */
		$factory = m::mock(IBrowserConfigurationFactory::class);

		$browser_config = array('apiUsername' => 'a', 'apiKey' => 'b');

		$browser = m::mock(
            SauceLabsBrowserConfiguration::class . '[getAPIClient]',
			array($this->readAttribute($this, '_eventDispatcher'), $this->createDriverFactoryRegistry())
		);

		// These magic methods can't be properly passed through to mocked object otherwise.
		$browser->shouldReceive('getSessionStrategy')->andReturn('isolated');
		$browser->shouldReceive('getDesiredCapabilities')->andReturn(array(
			SauceLabsBrowserConfiguration::NAME_CAPABILITY => 'something',
		));

		$browser->shouldReceive('getAPIClient')->once()->andReturn($api_client);

		$factory->shouldReceive('createBrowserConfiguration')
			->with($browser_config, $this)
			->once()
			->andReturn($browser);
		$this->setBrowserConfigurationFactory($factory);

		$this->setBrowserFromConfiguration($browser_config);

		parent::setUp();
	}

	/**
	 * Creates driver factory registry.
	 *
	 * @return DriverFactoryRegistry
	 */
	protected function createDriverFactoryRegistry()
	{
		$registry = m::mock(DriverFactoryRegistry::class);

		$driver_factory = m::mock(IMinkDriverFactory::class);
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
	public function testEvents()
	{
		$driver = m::mock(Selenium2Driver::class);
		$driver->shouldReceive('getWebDriverSessionId')->once()->andReturn('SID');

		$session = m::mock(Session::class);

		// For SauceLabsBrowserConfiguration::onTestEnded.
		$session->shouldReceive('getDriver')->once()->andReturn($driver);

		// For IsolatedSessionStrategy::onTestEnd (twice per each browser because
		// we have 2 strategies listening for test end + IsolatedSessionStrategyTest with 2 tests).
		$session->shouldReceive('stop')->once();
		$session->shouldReceive('isStarted')->andReturn(true);

		$this->_setSession($session);

		// For SauceLabsBrowserConfiguration::onTestSetup.
		$desired_capabilities = $this->getBrowser()->getDesiredCapabilities();
		$this->assertArrayHasKey(SauceLabsBrowserConfiguration::NAME_CAPABILITY, $desired_capabilities);
	}

	/**
	 * Replaces session with a given one.
	 *
	 * @param Session $session Session.
	 *
	 * @return void
	 */
	private function _setSession(Session $session)
	{
		$property = new \ReflectionProperty(BrowserTestCase::class, '_session');
		$property->setAccessible(true);
		$property->setValue($this, $session);
	}

}
