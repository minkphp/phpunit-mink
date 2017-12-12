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
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use Mockery as m;
use PHPUnit\Framework\Test;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;
use tests\aik099\PHPUnit\TestCase\EventDispatcherAwareTestCase;

class RegularTestSuiteTest extends EventDispatcherAwareTestCase
{

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testAddTestMethods()
	{
		$suite = $this->_createSuite(array('addTest'));
		$suite->shouldReceive('addTest')->twice();

		$actual = $suite->addTestMethods(WithoutBrowserConfig::class);
		$this->assertSame($suite, $actual);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetTestDependencies()
	{
		$manager = m::mock(SessionStrategyManager::class);
		$factory = m::mock(IBrowserConfigurationFactory::class);
		$helper = m::mock(RemoteCoverageHelper::class);

		$test = m::mock(Test::class);
		$test->shouldReceive('setEventDispatcher')->with($this->eventDispatcher)->once();
		$test->shouldReceive('setSessionStrategyManager')->with($manager)->once();
		$test->shouldReceive('setBrowserConfigurationFactory')->with($factory)->once();
		$test->shouldReceive('setRemoteCoverageHelper')->with($helper)->once();

		$suite = $this->_createSuite();
		$suite->addTest($test);
		$this->assertSame($suite, $suite->setTestDependencies($manager, $factory, $helper));
	}

	/**
	 * Creates suite.
	 *
	 * @param array $mock_methods Mock methods.
	 *
	 * @return RegularTestSuite
	 */
	private function _createSuite(array $mock_methods = array())
	{
		if ( $mock_methods ) {
			$suite = m::mock(RegularTestSuite::class . '[' . implode(',', $mock_methods) . ']');
		}
		else {
			$suite = new RegularTestSuite();
		}

		$suite->setEventDispatcher($this->eventDispatcher);

		return $suite;
	}

}
