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

abstract class SauceLabsAwareTestCase extends BrowserTestCase
{

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array('alias' => 'saucelabs'),
	);

	/**
	 * Set session meta-info for "Sauce Labs".
	 *
	 * @return void
	 */
	protected function setUp()
	{
		if ( !getenv('SAUCE_USERNAME') || !getenv('SAUCE_ACCESS_KEY') ) {
			$this->markTestSkipped('SauceLabs integration is not configured');
		}

		parent::setUp();
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
			'saucelabs' => array(
				'type' => 'saucelabs',
				'apiUsername' => getenv('SAUCE_USERNAME'),
				'apiKey' => getenv('SAUCE_ACCESS_KEY'),

				'browserName' => 'chrome',
				'desiredCapabilities' => array('version' => 28),
				'baseUrl' => 'http://www.google.com',
			),
		);
	}

}
