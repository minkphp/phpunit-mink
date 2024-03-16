<?php

namespace PHPSTORM_META {
	override(\aik099\PHPUnit\Application::getObject(), map([
		'session_strategy_factory' => \aik099\PHPUnit\Session\SessionStrategyFactory::class,
		'session_strategy_manager' => \aik099\PHPUnit\Session\SessionStrategyManager::class,
		'remote_url' => \aik099\PHPUnit\RemoteCoverage\RemoteUrl::class,
		'remote_coverage_helper' => \aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper::class,
		'test_suite_factory' => \aik099\PHPUnit\TestSuite\TestSuiteFactory::class,
		'regular_test_suite' => \aik099\PHPUnit\TestSuite\RegularTestSuite::class,
		'browser_test_suite' => \aik099\PHPUnit\TestSuite\BrowserTestSuite::class,
		'driver_factory_registry' => \aik099\PHPUnit\MinkDriver\DriverFactoryRegistry::class,
		'browser_configuration_factory' => \aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory::class,
	]));
}
