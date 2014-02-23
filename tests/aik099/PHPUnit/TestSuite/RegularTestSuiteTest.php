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


use aik099\PHPUnit\TestSuite\RegularTestSuite;
use Mockery as m;
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

		$actual = $suite->addTestMethods('tests\\aik099\\PHPUnit\\Fixture\\WithoutBrowserConfig');
		$this->assertSame($suite, $actual);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetTestDependencies()
	{
		$manager = m::mock('aik099\\PHPUnit\\Session\\SessionStrategyManager');
		$factory = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\IBrowserConfigurationFactory');
		$helper = m::mock('aik099\\PHPUnit\\RemoteCoverage\\RemoteCoverageHelper');

		$test = m::mock('PHPUnit_Framework_Test');
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
			$suite = m::mock('aik099\\PHPUnit\\TestSuite\\RegularTestSuite[' . implode(',', $mock_methods) . ']');
		}
		else {
			$suite = new RegularTestSuite();
		}

		$suite->setEventDispatcher($this->eventDispatcher);

		return $suite;
	}

}
