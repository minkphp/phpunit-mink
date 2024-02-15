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

abstract class BrowserStackAwareTestCase extends BrowserTestCase
{

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array(
			'alias' => 'default',
		),
	);

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		if ( !getenv('BS_USERNAME') || !getenv('BS_ACCESS_KEY') ) {
			$this->markTestSkipped('BrowserStack integration is not configured');
		}
	}

	/**
	 * Whatever or not code coverage information should be gathered.
	 *
	 * @return boolean
	 * @throws \RuntimeException When used before test is started.
	 */
	public function getCollectCodeCoverageInformation()
	{
		// FIXME: Workaround for https://github.com/minkphp/phpunit-mink/issues/35 bug.
		return false;
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
			'default' => array(
				'type' => 'browserstack',
				'api_username' => getenv('BS_USERNAME'),
				'api_key' => getenv('BS_ACCESS_KEY'),

				'browserName' => 'chrome',
				'desiredCapabilities' => array('browser_version' => 28),
				'baseUrl' => 'http://www.google.com',
			),
		);
	}

}
