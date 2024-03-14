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


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use aik099\PHPUnit\TestSuite\RegularTestSuite;
use ConsoleHelpers\PHPUnitCompat\Framework\TestResult;
use Mockery as m;
use PHPUnit\Runner\Version;
use tests\aik099\PHPUnit\AbstractTestCase;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;
use aik099\PHPUnit\Session\ISessionStrategy;

class SuiteBuildingTest extends AbstractTestCase
{

	const SUITE_CLASS = RegularTestSuite::class;

	const BROWSER_SUITE_CLASS = BrowserTestSuite::class;

	const TEST_CASE_WITH_CONFIG = WithBrowserConfig::class;

	const TEST_CASE_WITHOUT_CONFIG = WithoutBrowserConfig::class;

	/**
	 * Tests, that suite is built correctly in case, when static $browsers array is filled-in in test case class.
	 *
	 * @return void
	 */
	public function testWithBrowserConfiguration()
	{
		$suite = WithBrowserConfig::suite(self::TEST_CASE_WITH_CONFIG);

		$this->assertInstanceOf(self::SUITE_CLASS, $suite);

		/** @var BrowserTestSuite[] $test_suites */
		$test_suites = $suite->tests();

		$this->checkArray($test_suites, 2, self::BROWSER_SUITE_CLASS);

		$property = new \ReflectionProperty(BrowserTestCase::class, '_sessionStrategy');
		$property->setAccessible(true);

		foreach ( $test_suites as $test_suite ) {
			/** @var BrowserTestCase[] $suite_tests */
			$suite_tests = $test_suite->tests();
			$this->checkArray($suite_tests, 2, self::TEST_CASE_WITH_CONFIG);

			$this->assertInstanceOf(ISessionStrategy::class, $property->getValue($suite_tests[0]));
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

		$result = new TestResult(); // Can't mock, because it's a final class.

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

		$suite->shouldReceive('run')->once()->andReturnUsing(function ($result = null) {
			return $result;
		});
		$suite->shouldReceive('onTestSuiteEnded')->once();

		$phpunit_version = $this->getPhpUnitVersion();

		// Pretend, that mocked test suite has 1 test inside it.
		if ( version_compare($phpunit_version, '4.0.0', '>=') ) {
			$suite->shouldReceive('count')->once()->andReturn(1);
		}

		if ( version_compare($phpunit_version, '5.0.0', '>=') ) {
			$suite->shouldReceive('setBeStrictAboutChangesToGlobalState');
		}

		return $suite;
	}

	/**
	 * Returns PHPUnit version.
	 *
	 * @return string
	 */
	protected function getPhpUnitVersion()
	{
		if ( \class_exists(Version::class) ) {
			return Version::id();
		}

		return \PHPUnit_Runner_Version::id();
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
