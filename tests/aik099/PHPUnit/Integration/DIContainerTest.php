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
use aik099\PHPUnit\DIContainer;
use tests\aik099\PHPUnit\AbstractTestCase;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\APIClient\APIClientFactory;
use aik099\PHPUnit\TestSuite\TestSuiteFactory;
use aik099\PHPUnit\RemoteCoverage\RemoteUrl;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\Session\SessionStrategyFactory;
use aik099\PHPUnit\Session\SessionFactory;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;

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
			array('session_factory', SessionFactory::class),
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

}
