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
use aik099\PHPUnit\Event\TestEvent;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
	 * Sets event dispatcher.
	 *
	 * @param EventDispatcherInterface $event_dispatcher Event dispatcher.
	 *
	 * @return void
	 */
	public function setEventDispatcher(EventDispatcherInterface $event_dispatcher)
	{
		parent::setEventDispatcher($event_dispatcher);

		// Use priority for this listener to be called before one, that stops the session.
		$event_dispatcher->addListener(self::TEST_ENDED_EVENT, array($this, 'recordSessionId'), 200);
		$event_dispatcher->addListener(self::TEST_ENDED_EVENT, array($this, 'verifyRemoteAPICalls'));
	}

	/**
	 * Record WebDriver session ID of the test.
	 *
	 * @param TestEvent $event Event.
	 *
	 * @return void
	 */
	public function recordSessionId(TestEvent $event)
	{
		$test_case = $event->getTestCase();

		if ( get_class($test_case) !== get_class($this)
			|| $test_case->getName() !== $this->getName()
			|| $this->_getTestSkipMessage()
		) {
			return;
		}

		$session = $event->getSession();

		if ( $session === null ) {
			$this->markTestSkipped('Unable to connect to SauceLabs/BrowserStack. Please check Internet connection.');
		}

		$this->_sessionIds[$test_case->getName(false)] = $session->getDriver()->getWebDriverSessionId();
	}

	/**
	 * Verify how the API calls were made.
	 *
	 * @param TestEvent $event Event.
	 *
	 * @return void
	 */
	public function verifyRemoteAPICalls(TestEvent $event)
	{
		if ( !$event->validateSubscriber($this) ) {
			return;
		}

		$test_case = $event->getTestCase();
		$test_name = $test_case->getName(false);

		if ( !isset($this->_sessionIds[$test_name]) ) {
			return;
		}

		$browser = $this->getBrowser();

		if ( $browser instanceof ApiBrowserConfiguration ) {
			$api_client = $browser->getAPIClient();
			$session_info = $api_client->getInfo($this->_sessionIds[$test_name]);

			$this->assertEquals(get_class($test_case) . '::' . $test_name, $session_info['name']);

			if ( $browser instanceof SauceLabsBrowserConfiguration ) {
				$passed_mapping = array(
					'testSuccess' => true,
					'testFailure' => false,
				);

				$this->assertSame($passed_mapping[$test_name], $session_info['passed']);
			}
			elseif ( $browser instanceof BrowserStackBrowserConfiguration ) {
				$passed_mapping = array(
					'testSuccess' => 'done',
					'testFailure' => 'error',
				);

				$this->assertSame($passed_mapping[$test_name], $session_info['status']);
			}
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
	 * Test description.
	 *
	 * @return void
	 */
	public function testSuccess()
	{
		$skip_message = $this->_getTestSkipMessage();

		if ( $skip_message ) {
			$this->markTestSkipped($skip_message);
		}

		$session = $this->getSession();
		$session->visit('http://www.google.com');

		$this->assertTrue(true);
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

		$this->assertTrue(false);
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
				'desiredCapabilities' => array('version' => 38),
				'baseUrl' => 'http://www.google.com',
			),
			'browserstack' => array(
				'type' => 'browserstack',
				'api_username' => getenv('BS_USERNAME'),
				'api_key' => getenv('BS_ACCESS_KEY'),

				'browserName' => 'chrome',
				'desiredCapabilities' => array('browser_version' => '38.0', 'project' => 'PHPUnit-Mink'),
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
