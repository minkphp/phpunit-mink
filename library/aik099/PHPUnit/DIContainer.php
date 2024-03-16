<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit;


use aik099\PHPUnit\APIClient\APIClientFactory;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use aik099\PHPUnit\BrowserConfiguration\BrowserStackBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use aik099\PHPUnit\MinkDriver\GoutteDriverFactory;
use aik099\PHPUnit\MinkDriver\SahiDriverFactory;
use aik099\PHPUnit\MinkDriver\Selenium2DriverFactory;
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
use PimpleCopy\Pimple\Container;

class DIContainer extends Container implements IApplicationAware
{

	/**
	 * Sets application.
	 *
	 * @param Application $application The application.
	 *
	 * @return void
	 */
	public function setApplication(Application $application)
	{
		$this['application'] = $application;
	}

	/**
	 * Instantiate the container.
	 *
	 * Objects and parameters can be passed as argument to the constructor.
	 *
	 * @param array $values The parameters or objects.
	 */
	public function __construct(array $values = array())
	{
		parent::__construct($values);

		$this['session_strategy_factory'] = function ($c) {
			$session_strategy_factory = new SessionStrategyFactory();

			$session_strategy_factory->register(
				ISessionStrategyFactory::TYPE_ISOLATED,
				new IsolatedSessionStrategy()
			);

			$session_strategy_factory->register(
				ISessionStrategyFactory::TYPE_SHARED,
				new SharedSessionStrategy(
					new IsolatedSessionStrategy()
				)
			);

			return $session_strategy_factory;
		};

		$this['session_strategy_manager'] = function ($c) {
			return new SessionStrategyManager($c['session_strategy_factory']);
		};

		$this['remote_url'] = function () {
			return new RemoteUrl();
		};

		$this['remote_coverage_helper'] = function ($c) {
			return new RemoteCoverageHelper($c['remote_url']);
		};

		$this['test_suite_factory'] = function ($c) {
			$test_suite_factory = new TestSuiteFactory(
				$c['session_strategy_manager'],
				$c['browser_configuration_factory'],
				$c['remote_coverage_helper']
			);
			$test_suite_factory->setApplication($c['application']);

			return $test_suite_factory;
		};

		$this['regular_test_suite'] = $this->factory(function ($c) {
			return new RegularTestSuite();
		});

		$this['browser_test_suite'] = $this->factory(function ($c) {
			return new BrowserTestSuite();
		});

		$this['driver_factory_registry'] = function () {
			$registry = new DriverFactoryRegistry();

			$registry->add(new Selenium2DriverFactory());
			$registry->add(new SahiDriverFactory());
			$registry->add(new GoutteDriverFactory());
			$registry->add(new ZombieDriverFactory());

			return $registry;
		};

		$this['api_client_factory'] = function ($c) {
			return new APIClientFactory();
		};

		$this['browser_configuration_factory'] = function ($c) {
			$browser_configuration_factory = new BrowserConfigurationFactory();

			$browser_configuration_factory->register(
				new BrowserConfiguration($c['driver_factory_registry'])
			);
			$browser_configuration_factory->register(
				new SauceLabsBrowserConfiguration($c['driver_factory_registry'], $c['api_client_factory'])
			);
			$browser_configuration_factory->register(
				new BrowserStackBrowserConfiguration($c['driver_factory_registry'], $c['api_client_factory'])
			);

			return $browser_configuration_factory;
		};
	}

}
