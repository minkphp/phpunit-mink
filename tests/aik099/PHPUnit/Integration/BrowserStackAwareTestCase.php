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
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Session;

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
	 * Visit specified URL and automatically start session if not already running.
	 *
	 * @param Session $session Session.
	 * @param string  $url     Url of the page.
	 *
	 * @return void
	 * @throws DriverException
	 */
	protected function openPageWithBackoff(Session $session, $url)
	{
		try {
			$session->visit($url);
		}
		catch ( DriverException $e ) {
			if ( strpos($e->getMessage(), '[BROWSERSTACK_QUEUE_SIZE_EXCEEDED]') !== false ) {
				sleep(30);
				$this->openPageWithBackoff($session, $url);

				return;
			}

			throw $e;
		}
	}

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
				'desiredCapabilities' => array('browser_version' => 110),
				'baseUrl' => 'http://www.google.com',
			),
		);
	}

}
