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
	public function serviceDefinitionsDataProvider()
	{
		return array(
			array('application', 'aik099\\PHPUnit\\Application'),
			array('session_factory', 'aik099\\PHPUnit\\Session\\SessionFactory'),
			array('session_strategy_factory', 'aik099\\PHPUnit\\Session\\SessionStrategyFactory'),
			array('session_strategy_manager', 'aik099\\PHPUnit\\Session\\SessionStrategyManager'),
			array('remote_url', 'aik099\\PHPUnit\\RemoteCoverage\\RemoteUrl'),
			array('remote_coverage_helper', 'aik099\\PHPUnit\\RemoteCoverage\\RemoteCoverageHelper'),
			array('test_suite_factory', 'aik099\\PHPUnit\\TestSuite\\TestSuiteFactory'),
			array('regular_test_suite', 'aik099\\PHPUnit\\TestSuite\\RegularTestSuite'),
			array('browser_test_suite', 'aik099\\PHPUnit\\TestSuite\\BrowserTestSuite'),
			array('api_client_factory',	'aik099\\PHPUnit\\APIClient\\APIClientFactory'),
			array(
				'browser_configuration_factory',
				'aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfigurationFactory',
			),
			array('driver_factory_registry', 'aik099\\PHPUnit\\MinkDriver\\DriverFactoryRegistry'),
		);
	}

}
