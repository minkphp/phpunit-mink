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


use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Event\TestEvent;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WebDriver\SauceLabs\SauceRest;

class ApiIntegrationFixture extends BrowserTestCase
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
			$this->markTestSkipped('Unable to connect to SauceLabs. Please check Internet connection.');
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
		$test_case = $event->getTestCase();

		if ( !$event->validateSubscriber($this) ) {
			return;
		}

		$test_name = $test_case->getName(false);

		if ( !isset($this->_sessionIds[$test_name]) ) {
			return;
		}

		$browser = $this->getBrowser();

		if ( $browser instanceof SauceLabsBrowserConfiguration ) {
			$sauce_rest = new SauceRest($browser->getApiUsername(), $browser->getApiKey());
			$job_info = $sauce_rest->getJob($this->_sessionIds[$test_name]);

			$this->assertEquals(get_class($test_case) . '::' . $test_name, $job_info['name']);

			$passed_mapping = array(
				'testSuccess' => true,
				'testFailure' => false,
			);

			$this->assertSame($passed_mapping[$test_name], $job_info['passed']);
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

	private function _getTestSkipMessage()
	{
		$browser = $this->getBrowser();

		if ( $browser->getType() == 'saucelabs' ) {
			if ( !getenv('SAUCE_USERNAME') || !getenv('SAUCE_ACCESS_KEY') ) {
				return 'SauceLabs integration is not configured';
			}
		}

		return '';
	}

}
