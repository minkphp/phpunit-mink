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


use aik099\PHPUnit\APIClient\APIClientFactory;
use aik099\PHPUnit\BrowserConfiguration\ApiBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use Behat\Mink\Session;
use Mockery as m;
use tests\aik099\PHPUnit\TVerifyTestExpectations;
use aik099\PHPUnit\APIClient\IAPIClient;
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;
use Behat\Mink\Driver\Selenium2Driver;

class SetupFixture extends BrowserTestCase
{

	use TVerifyTestExpectations;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$api_client = m::mock(IAPIClient::class);
		$api_client->shouldReceive('updateStatus')->withAnyArgs()->once();

		/** @var APIClientFactory $api_client_factory */
		$api_client_factory = m::mock(APIClientFactory::class);

		$browser = new SauceLabsBrowserConfiguration($this->createDriverFactoryRegistry(), $api_client_factory);

		$desired_capabilities = $browser->getDesiredCapabilities();
		$desired_capabilities[ApiBrowserConfiguration::NAME_CAPABILITY] = 'something';
		$browser->setDesiredCapabilities($desired_capabilities);

		$api_client_factory->shouldReceive('getAPIClient')->with($browser)->once()->andReturn($api_client);

		$browser_config = array('apiUsername' => 'a', 'apiKey' => 'b');

		$factory = m::mock(IBrowserConfigurationFactory::class);

		$factory->shouldReceive('createBrowserConfiguration')
			->with($browser_config, $this)
			->once()
			->andReturn($browser);
		$this->setBrowserConfigurationFactory($factory);

		$this->setBrowserFromConfiguration($browser_config);

		parent::setUpTest();
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

		// For ApiBrowserConfiguration::onTestEnded.
		$session->shouldReceive('getDriver')->once()->andReturn($driver);

		$session->shouldReceive('stop')->once();
		$session->shouldReceive('isStarted')->andReturn(true);

		$this->_setSession($session);

		// For ApiBrowserConfiguration::onTestSetup.
		$desired_capabilities = $this->getBrowser()->getDesiredCapabilities();
		$this->assertArrayHasKey(ApiBrowserConfiguration::NAME_CAPABILITY, $desired_capabilities);
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
