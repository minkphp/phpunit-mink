<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Integration;


use aik099\PHPUnit\APIClient\APIClientFactory;
use aik099\PHPUnit\Application;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use aik099\PHPUnit\BrowserConfiguration\BrowserStackBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\DIContainer;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\MinkDriver\GoutteDriverFactory;
use aik099\PHPUnit\MinkDriver\SahiDriverFactory;
use aik099\PHPUnit\MinkDriver\Selenium2DriverFactory;
use aik099\PHPUnit\MinkDriver\WebdriverClassicFactory;
use aik099\PHPUnit\MinkDriver\ZombieDriverFactory;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\RemoteCoverage\RemoteUrl;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use aik099\PHPUnit\Session\SessionStrategyFactory;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\Session\SharedSessionStrategy;
use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use aik099\PHPUnit\TestSuite\TestSuiteFactory;
use Mockery as m;
use tests\aik099\PHPUnit\AbstractTestCase;

class DIContainerTest extends AbstractTestCase
{

	/**
	 * Container.
	 *
	 * @var DIContainer
	 */
	private $_container;

	/**
	 * Creates container for testing.
	 *
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_container = new DIContainer();
		$this->_container->setApplication(new Application());
	}

	/**
	 * Test description.
	 *
	 * @param string $service_id Service ID.
	 * @param string $class_name Class name.
	 *
	 * @return void
	 * @dataProvider serviceDefinitionsDataProvider
	 */
	public function testServiceDefinitions($service_id, $class_name)
	{
		$this->assertInstanceOf($class_name, $this->_container[$service_id]);
	}

	/**
	 * Provides expectations for service definitions.
	 *
	 * @return array
	 */
	public static function serviceDefinitionsDataProvider()
	{
		return array(
			array('application', Application::class),
			array('session_strategy_factory', SessionStrategyFactory::class),
			array('session_strategy_manager', SessionStrategyManager::class),
			array('remote_url', RemoteUrl::class),
			array('remote_coverage_helper', RemoteCoverageHelper::class),
			array('test_suite_factory', TestSuiteFactory::class),
			array('regular_test_suite', RegularTestSuite::class),
			array('browser_test_suite', BrowserTestSuite::class),
			array('api_client_factory', APIClientFactory::class),
			array('browser_configuration_factory', BrowserConfigurationFactory::class),
			array('driver_factory_registry', DriverFactoryRegistry::class),
		);
	}

	public function testSessionStrategyFactory()
	{
		/** @var SessionStrategyFactory $session_strategy_factory */
		$session_strategy_factory = $this->_container['session_strategy_factory'];

		$this->assertInstanceOf(
			IsolatedSessionStrategy::class,
			$session_strategy_factory->createStrategy(ISessionStrategyFactory::TYPE_ISOLATED)
		);

		$this->assertInstanceOf(
			SharedSessionStrategy::class,
			$session_strategy_factory->createStrategy(ISessionStrategyFactory::TYPE_SHARED)
		);
	}

	public function testDriverFactoryRegistry()
	{
		/** @var DriverFactoryRegistry $driver_factory_registry */
		$driver_factory_registry = $this->_container['driver_factory_registry'];

		$this->assertInstanceOf(Selenium2DriverFactory::class, $driver_factory_registry->get('selenium2'));
		$this->assertInstanceOf(WebdriverClassicFactory::class, $driver_factory_registry->get('webdriver-classic'));
		$this->assertInstanceOf(SahiDriverFactory::class, $driver_factory_registry->get('sahi'));
		$this->assertInstanceOf(GoutteDriverFactory::class, $driver_factory_registry->get('goutte'));
		$this->assertInstanceOf(ZombieDriverFactory::class, $driver_factory_registry->get('zombie'));
	}

	public function testBrowserConfigurationFactory()
	{
		$test_case = m::mock(BrowserTestCase::class);
		$test_case->shouldReceive('getBrowserAliases')->andReturn(array());

		/** @var BrowserConfigurationFactory $browser_configuration_factory */
		$browser_configuration_factory = $this->_container['browser_configuration_factory'];

		$this->assertInstanceOf(
			BrowserConfiguration::class,
			$browser_configuration_factory->createBrowserConfiguration(array('type' => 'default'), $test_case)
		);

		$this->assertInstanceOf(
			BrowserStackBrowserConfiguration::class,
			$browser_configuration_factory->createBrowserConfiguration(array('type' => 'browserstack'), $test_case)
		);

		$this->assertInstanceOf(
			SauceLabsBrowserConfiguration::class,
			$browser_configuration_factory->createBrowserConfiguration(array('type' => 'saucelabs'), $test_case)
		);
	}

}
