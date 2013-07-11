<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Common;


use aik099\PHPUnit\Common\RemoteCoverage;
use Mockery as m;

class RemoteCoverageTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testIncorrectScriptUrl()
	{
		$remote_coverage = new RemoteCoverage('', 'test_id');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetFetchUrl()
	{
		$remote_coverage = new RemoteCoverage('A', 'B');

		$this->assertEquals('A?PHPUNIT_SELENIUM_TEST_ID=B', $remote_coverage->getFetchUrl());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGet()
	{
		$remote_coverage = m::mock('\\aik099\\PHPUnit\\Common\\RemoteCoverage[getFetchUrl]', array('url', 'test_id'));
		/* @var $remote_coverage RemoteCoverage */

		$remote_coverage->shouldReceive('getFetchUrl')->andReturn(__DIR__ . '/../Fixture/coverage_data.txt');

		$content = $remote_coverage->get();
		$class_source_file = realpath(__DIR__ . '/../Fixture/DummyClass.php');

		$expected = array(
			3 => 1,
			6 => 1,
			7 => -2,
			11 => -1,
			12 => -2,
			14 => 1,
		);

		$this->assertEquals($expected, $content[$class_source_file]);
	}

}
