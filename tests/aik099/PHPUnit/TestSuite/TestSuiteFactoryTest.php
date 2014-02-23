<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\TestSuite;


use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\TestSuite\TestSuiteFactory;
use Mockery as m;
use tests\aik099\PHPUnit\TestCase\ApplicationAwareTestCase;

class TestSuiteFactoryTest extends ApplicationAwareTestCase
{

	/**
	 * Suite.
	 *
	 * @var TestSuiteFactory
	 */
	private $_factory;

	/**
	 * Session strategy manager.
	 *
	 * @var SessionStrategyManager
	 */
	private $_manager;

	/**
	 * Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory
	 */
	private $_browserFactory;

	/**
	 * Remote coverage helper.
	 *
	 * @var RemoteCoverageHelper
	 */
	private $_remoteCoverageHelper;

	/**
	 * Creates suite for testing.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_manager = m::mock('aik099\\PHPUnit\\Session\\SessionStrategyManager');
		$this->_browserFactory = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\IBrowserConfigurationFactory');
		$this->_remoteCoverageHelper = m::mock('aik099\\PHPUnit\\RemoteCoverage\\RemoteCoverageHelper');

		$this->_factory = new TestSuiteFactory($this->_manager, $this->_browserFactory, $this->_remoteCoverageHelper);
		$this->_factory->setApplication($this->application);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSuiteFromTestCaseWithoutBrowsers()
	{
		$suite_class_name = 'aik099\\PHPUnit\\TestSuite\\RegularTestSuite';
		$test_case_class_name = 'tests\\aik099\\PHPUnit\\Fixture\\WithoutBrowserConfig';

		$suite = m::mock($suite_class_name);
		$suite->shouldReceive('setName')->with($test_case_class_name)->once();
		$suite->shouldReceive('addTestMethods')->with($test_case_class_name)->once();
		$suite
			->shouldReceive('setTestDependencies')
			->with($this->_manager, $this->_browserFactory, $this->_remoteCoverageHelper)
			->once();
		$this->expectFactoryCall('regular_test_suite', $suite);

		$actual_suite = $this->_factory->createSuiteFromTestCase($test_case_class_name);
		$this->assertInstanceOf($suite_class_name, $actual_suite);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSuiteFromTestCaseWithBrowsers()
	{
		$suite_class_name = 'aik099\\PHPUnit\\TestSuite\\RegularTestSuite';
		$test_case_class_name = 'tests\\aik099\\PHPUnit\\Fixture\\WithBrowserConfig';

		$browser_suite1 = $this->_createBrowserTestSuiteMock($test_case_class_name, array(
			'browserName' => 'firefox', 'host' => 'localhost',
		));
		$browser_suite2 = $this->_createBrowserTestSuiteMock($test_case_class_name, array(
			'browserName' => 'chrome', 'host' => '127.0.0.1',
		));
		$this->expectFactoryCall('browser_test_suite', array($browser_suite1, $browser_suite2));

		$suite = m::mock($suite_class_name);
		$suite->shouldReceive('setName')->with($test_case_class_name)->once();
		$suite->shouldReceive('addTest')->with($browser_suite1)->once();
		$suite->shouldReceive('addTest')->with($browser_suite2)->once();
		$this->expectFactoryCall('regular_test_suite', $suite);

		$actual_suite = $this->_factory->createSuiteFromTestCase($test_case_class_name);
		$this->assertInstanceOf($suite_class_name, $actual_suite);
	}

	/**
	 * Creates browser suite mock.
	 *
	 * @param string $class_name Descendant of TestCase class.
	 * @param array  $browser    Browser configuration.
	 *
	 * @return BrowserTestSuite
	 */
	private function _createBrowserTestSuiteMock($class_name, array $browser)
	{
		$suite = m::mock('aik099\\PHPUnit\\TestSuite\\BrowserTestSuite');
		$suite->shouldReceive('nameFromBrowser')->with($browser)->once()->andReturn('OK');
		$suite->shouldReceive('setName')->with($class_name . ': OK')->once();
		$suite->shouldReceive('addTestMethods')->with($class_name)->once();
		$suite
			->shouldReceive('setTestDependencies')
			->with($this->_manager, $this->_browserFactory, $this->_remoteCoverageHelper)
			->once();
		$suite->shouldReceive('setBrowserFromConfiguration')->with($browser)->once();

		return $suite;
	}

}
