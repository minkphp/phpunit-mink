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


class DataProviderTest extends BrowserStackAwareTestCase
{

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array(
			'alias' => 'default',
			'desiredCapabilities' => array('build' => BUILD_NAME, 'name' => 'DataProviderTest'),
		),
	);

	public function sampleDataProvider()
	{
		return array(
			array('case1'),
			array('case2'),
		);
	}

	/**
	 * @dataProvider sampleDataProvider
	 */
	public function testDataProvider($case)
	{
		$this->customMethod();

		if ( $case === 'case1' || $case === 'case2' ) {
			$this->assertTrue(true);
		}
		else {
			$this->fail('Unknown $case: ' . $case);
		}
	}

	protected function customMethod()
	{
		return 5;
	}

}
