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
use tests\aik099\PHPUnit\AbstractTestCase;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;
use aik099\PHPUnit\Session\SessionStrategyManager;
use aik099\PHPUnit\BrowserConfiguration\IBrowserConfigurationFactory;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use ConsoleHelpers\PHPUnitCompat\Framework\Test;

class RegularTestSuiteTest extends AbstractTestCase
{

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testAddTestMethods()
	{
		$suite = $this->_createSuite();

		$actual = $suite->addTestMethods(WithoutBrowserConfig::class);
		$this->assertSame($suite, $actual, 'The fluid interface doesn\'t work.');

		$this->assertCount(2, $actual->tests(), 'Not all tests were added.');
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
		$test->shouldReceive('setSessionStrategyManager')->with($manager)->once();
		$test->shouldReceive('setBrowserConfigurationFactory')->with($factory)->once();
		$test->shouldReceive('setRemoteCoverageHelper')->with($helper)->once();

		$suite = $this->_createSuite();
		$suite->addTest($test);
		$this->assertSame(
			$suite,
			$suite->setTestDependencies($manager, $factory, $helper),
			'The fluid interface doesn\'t work.'
		);
	}

	/**
	 * Creates suite.
	 *
	 * @return RegularTestSuite
	 */
	private function _createSuite()
	{
		return new RegularTestSuite();
	}

}
