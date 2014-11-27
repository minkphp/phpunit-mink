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
use Mockery as m;

class DataProviderTest extends BrowserTestCase
{

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array('alias' => 'saucelabs'),
		// array('alias' => 'browserstack'),
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

	/**
	 * Gets browser configuration aliases.
	 *
	 * Allows to decouple actual test server connection details from test cases.
	 *
	 * @return array
	 */
	public function getBrowserAliases()
	{
		return array(
			'saucelabs' => array(
				'type' => 'saucelabs',
				'api_username' => getenv('SAUCE_USERNAME'),
				'api_key' => getenv('SAUCE_ACCESS_KEY'),

				'browserName' => 'chrome',
				'desiredCapabilities' => array('version' => 28),
				'baseUrl' => 'http://www.google.com',
			),
			/*'browserstack' => array(
				'type' => 'browserstack',
			),*/
		);
	}

}
