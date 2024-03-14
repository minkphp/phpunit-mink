<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Fixture;


use aik099\PHPUnit\BrowserConfiguration\ApiBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserStackBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;

class ApiIntegrationFixture extends BrowserTestCase
{

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array('alias' => 'saucelabs'),
		array('alias' => 'browserstack'),
	);

	/**
	 * Record IDs of WebDriver session, that needs to be verified.
	 *
	 * @var array
	 */
	private $_sessionIds = array();

	/**
	 * @inheritDoc
	 *
	 * @after
	 */
	protected function tearDownTest()
	{
		$this->recordSessionId();

		parent::tearDownTest();

		$this->verifyRemoteAPICalls();
	}

	/**
	 * Record WebDriver session ID of the test.
	 *
	 * @return void
	 */
	public function recordSessionId()
	{
		if ( $this->_getTestSkipMessage() ) {
			return;
		}

		$session = $this->getSession(false);

		if ( $session === null ) {
			$this->markTestSkipped('Unable to connect to SauceLabs/BrowserStack. Please check Internet connection.');
		}

		$this->_sessionIds[$this->getName(false)] = $session->getDriver()->getWebDriverSessionId();
	}

	/**
	 * Verify how the API calls were made.
	 *
	 * @return void
	 */
	public function verifyRemoteAPICalls()
	{
		$test_name = $this->getName(false);

		if ( !isset($this->_sessionIds[$test_name]) ) {
			return;
		}

		$browser = $this->getBrowser();

		if ( $browser instanceof ApiBrowserConfiguration ) {
			$api_client = $browser->getAPIClient();
			$session_info = $api_client->getInfo($this->_sessionIds[$test_name]);

			if ( $browser instanceof SauceLabsBrowserConfiguration ) {
				$this->assertEquals(
					get_class($this) . '::' . $test_name,
					$session_info['name'],
					'SauceLabs remote session name matches test name'
				);

				$passed_mapping = array(
					'testSuccess' => true,
					'testFailure' => false,
				);

				$this->assertSame(
					$passed_mapping[$test_name],
					$session_info['passed'],
					'SauceLabs test status set via API'
				);

				$custom_data_mapping = array(
					'testSuccess' => null,
					'testFailure' => array(
						'status_message' => "This test is expected to fail.\nFailed asserting that false is true.",
					),
				);

				$this->assertSame(
					$custom_data_mapping[$test_name],
					$session_info['custom-data'],
					'SauceLabs test status message set via API'
				);
			}
			elseif ( $browser instanceof BrowserStackBrowserConfiguration ) {
				$this->assertEquals(
					\str_replace('\\', '-', get_class($this) . '::' . $test_name),
					$session_info['name'],
					'BrowserStack remote session name matches test name'
				);

				$passed_mapping = array(
					'testSuccess' => 'passed',
					'testFailure' => 'failed',
				);

				$this->assertSame(
					$passed_mapping[$test_name],
					$session_info['status'],
					'BrowserStack test status set via API'
				);

				$reason_mapping = array(
					'testSuccess' => '',
					'testFailure' => "This test is expected to fail.\nFailed asserting that false is true.",
				);

				$this->assertSame(
					$reason_mapping[$test_name],
					$session_info['reason'],
					'BrowserStack test status message set via API'
				);
			}
		}
	}

	/**
	 * Test description.
	 *
	 * @param boolean $data Data.
	 *
	 * @return       void
	 * @dataProvider successDataProvider
	 */
	public function testSuccess($data)
	{
		$skip_message = $this->_getTestSkipMessage();

		if ( $skip_message ) {
			$this->markTestSkipped($skip_message);
		}

		$session = $this->getSession();
		$session->visit('http://www.google.com');

		$this->assertTrue(true);
	}

	public static function successDataProvider()
	{
		return array(
			'true' => array(true),
			'false' => array(false),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testFailure()
	{
		$skip_message = $this->_getTestSkipMessage();

		if ( $skip_message ) {
			$this->markTestSkipped($skip_message);
		}

		$session = $this->getSession();
		$session->visit('http://www.google.com');

		$this->assertTrue(false, 'This test is expected to fail.');
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
				'desiredCapabilities' => array('build' => BUILD_NAME, 'version' => 120),
				'baseUrl' => 'http://www.google.com',
			),
			'browserstack' => array(
				'type' => 'browserstack',
				'api_username' => getenv('BS_USERNAME'),
				'api_key' => getenv('BS_ACCESS_KEY'),

				'browserName' => 'chrome',
				'desiredCapabilities' => array('build' => BUILD_NAME, 'browser_version' => 120),
			),
		);
	}

	private function _getTestSkipMessage()
	{
		$browser = $this->getBrowser();

		if ( $browser->getType() == 'saucelabs' ) {
			if ( !getenv('SAUCE_USERNAME') || !getenv('SAUCE_ACCESS_KEY') ) {
				return 'SauceLabs integration is not configured';
			}
		}
		elseif ( $browser->getType() == 'browserstack' ) {
			if ( !getenv('BS_USERNAME') || !getenv('BS_ACCESS_KEY') ) {
				return 'BrowserStack integration is not configured';
			}
		}

		return '';
	}

}
