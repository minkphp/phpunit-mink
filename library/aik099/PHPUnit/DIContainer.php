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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use aik099\PHPUnit\Session\SessionFactory;
use aik099\PHPUnit\Session\SessionStrategyFactory;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\Session\SharedSessionStrategy;
use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use aik099\PHPUnit\TestSuite\TestSuiteBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DIContainer extends \Pimple implements ITestApplicationAware
{

	/**
	 * Application.
	 *
	 * @var TestApplication
	 */
	protected $application;

	/**
	 * Sets application.
	 *
	 * @param TestApplication $application The application.
	 *
	 * @return void
	 */
	public function setApplication(TestApplication $application)
	{
		$this->application = $application;
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

		$this['event_dispatcher'] = function ($c) {
			return new EventDispatcher();
		};

		$this['session_factory'] = function ($c) {
			return new SessionFactory();
		};

		$this['session_strategy_factory'] = function (DIContainer $c) {
			$session_strategy_factory = new SessionStrategyFactory();
			$session_strategy_factory->setApplication($c->getApplication());

			return $session_strategy_factory;
		};

		$this['session_strategy_manager'] = function ($c) {
			return new SessionStrategyManager($c['session_strategy_factory']);
		};

		$this['isolated_session_strategy'] = $this->factory(function ($c) {
			$session_strategy = new IsolatedSessionStrategy($c['session_factory']);
			$session_strategy->setEventDispatcher($c['event_dispatcher']);

			return $session_strategy;
		});

		$this['shared_session_strategy'] = $this->factory(function ($c) {
			$session_strategy = new SharedSessionStrategy($c['isolated_session_strategy']);
			$session_strategy->setEventDispatcher($c['event_dispatcher']);

			return $session_strategy;
		});

		$this['test_suite_builder'] = function (DIContainer $c) {
			$test_suite_builder = new TestSuiteBuilder($c['session_strategy_manager'], $c['browser_configuration_factory']);
			$test_suite_builder->setApplication($c->getApplication());

			return $test_suite_builder;
		};

		$this['regular_test_suite'] = $this->factory(function (DIContainer $c) {
			$test_suite = new RegularTestSuite();
			$test_suite->setEventDispatcher($c['event_dispatcher']);

			return $test_suite;
		});

		$this['browser_test_suite'] = $this->factory(function (DIContainer $c) {
			$test_suite = new BrowserTestSuite();
			$test_suite->setEventDispatcher($c['event_dispatcher']);

			return $test_suite;
		});

		$this['browser_configuration_factory'] = function (DIContainer $c) {
			$browser_configuration_factory = new BrowserConfigurationFactory();
			$browser_configuration_factory->setApplication($c->getApplication());

			return $browser_configuration_factory;
		};

		$this['browser_configuration'] = $this->factory(function ($c) {
			$browser = new BrowserConfiguration();
			$browser->setEventDispatcher($c['event_dispatcher']);

			return $browser;
		});

		$this['sauce_labs_browser_configuration'] = $this->factory(function ($c) {
			$browser = new SauceLabsBrowserConfiguration($c['browser_configuration_factory']);
			$browser->setEventDispatcher($c['event_dispatcher']);

			return $browser;
		});
	}

	/**
	 * Returns application.
	 *
	 * @return TestApplication
	 */
	protected function getApplication()
	{
		return $this->application;
	}

}
