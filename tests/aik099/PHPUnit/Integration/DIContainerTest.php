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


use aik099\PHPUnit\Application;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use aik099\PHPUnit\DIContainer;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\RemoteCoverage\RemoteUrl;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use aik099\PHPUnit\Session\SessionFactory;
use aik099\PHPUnit\Session\SessionStrategyFactory;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\Session\SharedSessionStrategy;
use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use aik099\PHPUnit\TestSuite\TestSuiteFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DIContainerTest extends MockeryTestCase
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
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

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
	public function serviceDefinitionsDataProvider()
	{
		return array(
			array('application', Application::class),
			array('event_dispatcher', EventDispatcher::class),
			array('session_factory', SessionFactory::class),
			array('session_strategy_factory', SessionStrategyFactory::class),
			array('session_strategy_manager', SessionStrategyManager::class),
			array('isolated_session_strategy', IsolatedSessionStrategy::class),
			array('shared_session_strategy', SharedSessionStrategy::class),
			array('remote_url', RemoteUrl::class),
			array('remote_coverage_helper', RemoteCoverageHelper::class),
			array('test_suite_factory', TestSuiteFactory::class),
			array('regular_test_suite', RegularTestSuite::class),
			array('browser_test_suite', BrowserTestSuite::class),
			array('browser_configuration_factory', BrowserConfigurationFactory::class),
			array('driver_factory_registry', DriverFactoryRegistry::class),
		);
	}

}
