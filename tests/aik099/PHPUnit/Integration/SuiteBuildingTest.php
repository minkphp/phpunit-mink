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


use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;
use Mockery as m;

class SuiteBuildingTest extends \PHPUnit_Framework_TestCase
{

	const SUITE_CLASS = '\\aik099\\PHPUnit\\TestSuite\\RegularTestSuite';

	const BROWSER_SUITE_CLASS = '\\aik099\\PHPUnit\\TestSuite\\BrowserTestSuite';

	const TEST_CASE_WITH_CONFIG = '\\tests\\aik099\\PHPUnit\\Fixture\\WithBrowserConfig';

	const TEST_CASE_WITHOUT_CONFIG = '\\tests\\aik099\\PHPUnit\\Fixture\\WithoutBrowserConfig';

	/**
	 * Tests, that suite is built correctly in case, when static $browsers array is filled-in in test case class.
	 *
	 * @return void
	 */
	public function testWithBrowserConfiguration()
	{
		$suite = WithBrowserConfig::suite(self::TEST_CASE_WITH_CONFIG);

		$this->assertInstanceOf(self::SUITE_CLASS, $suite);

		$tests = $suite->tests();
		/* @var $tests BrowserTestSuite[] */

		$this->checkArray($tests, 2, self::BROWSER_SUITE_CLASS);

		foreach ( $tests as $test ) {
			$this->checkArray($test->tests(), 2, self::TEST_CASE_WITH_CONFIG);
		}
	}

	/**
	 * Tests, that suite is built correctly in case, when no browser configuration is defined ahead.
	 *
	 * @return void
	 */
	public function testWithoutBrowserConfiguration()
	{
		$suite = WithoutBrowserConfig::suite(self::TEST_CASE_WITHOUT_CONFIG);

		$this->assertInstanceOf(self::SUITE_CLASS, $suite);

		$this->checkArray($suite->tests(), 2, self::TEST_CASE_WITHOUT_CONFIG);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSuiteTearDown()
	{
		$sub_browser_suite = $this->createTestSuite(self::BROWSER_SUITE_CLASS);
		$sub_test_suite = $this->createTestSuite(self::SUITE_CLASS);

		$suite = new RegularTestSuite();
		$suite->setName(self::TEST_CASE_WITH_CONFIG);
		$suite->addTest($sub_browser_suite);
		$suite->addTest($sub_test_suite);

		$result = m::mock('\\PHPUnit_Framework_TestResult');
		$result->shouldReceive('startTestSuite');
		$result->shouldReceive('shouldStop')->andReturn(false);
		$result->shouldReceive('endTestSuite');

		$this->assertSame($result, $suite->run($result));
	}

	/**
	 * Creates test suite.
	 *
	 * @param string $class_name Class name.
	 *
	 * @return RegularTestSuite
	 */
	protected function createTestSuite($class_name)
	{
		$suite = m::mock($class_name);

		$suite->shouldReceive('getGroups')->once()->andReturn(array());
		$suite->shouldReceive('setDisallowChangesToGlobalState'); // Since PHPUnit 4.6.0.
		$suite->shouldReceive('setBackupGlobals');
		$suite->shouldReceive('setBackupStaticAttributes');
		$suite->shouldReceive('setRunTestInSeparateProcess');

		$suite->shouldReceive('run')->once();
		$suite->shouldReceive('onTestSuiteEnded')->once();

		if ( version_compare(\PHPUnit_Runner_Version::id(), '4.0.0', '>=') ) {
			$suite->shouldReceive('count')->once()->andReturn(1);
		}

		return $suite;
	}

	/**
	 * Checks, that array has a desired structure & contents.
	 *
	 * @param array   $array          Array to test.
	 * @param integer $expected_count Expected element count.
	 * @param string  $class_name     Class name.
	 *
	 * @return void
	 */
	protected function checkArray(array $array, $expected_count, $class_name)
	{
		$this->assertCount($expected_count, $array);

		for ( $i = 0; $i < $expected_count; $i++ ) {
			$this->assertArrayHasKey($i, $array);
			$this->assertInstanceOf($class_name, $array[$i]);
		}
	}

}
