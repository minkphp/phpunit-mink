<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit;


use aik099\PHPUnit\BrowserSuite;
use tests\aik099\PHPUnit\Fixture\WithBrowserConfig;
use tests\aik099\PHPUnit\Fixture\WithoutBrowserConfig;

class SuiteBuildingTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Tests, that suite is built correctly in case, when static $browsers array is filled-in in test case class.
	 *
	 * @return void
	 */
	public function testWithBrowserConfiguration()
	{
		$test_case_class = '\\tests\\aik099\\PHPUnit\\Fixture\\WithBrowserConfig';
		$suite = WithBrowserConfig::suite($test_case_class);

		$this->assertInstanceOf('\\aik099\\PHPUnit\\TestSuite', $suite);

		$tests = $suite->tests();
		/* @var $tests BrowserSuite[] */

		$this->checkArray($tests, 2, '\\aik099\\PHPUnit\\BrowserSuite');

		foreach ( $tests as $test ) {
			$this->checkArray($test->tests(), 2, $test_case_class);
		}
	}

	/**
	 * Tests, that suite is built correctly in case, when no browser configuration is defined ahead.
	 *
	 * @return void
	 */
	public function testWithoutBrowserConfiguration()
	{
		$test_case_class = '\\tests\\aik099\\PHPUnit\\Fixture\\WithoutBrowserConfig';
		$suite = WithoutBrowserConfig::suite($test_case_class);

		$this->assertInstanceOf('\\aik099\\PHPUnit\\TestSuite', $suite);

		$this->checkArray($suite->tests(), 2, $test_case_class);
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
