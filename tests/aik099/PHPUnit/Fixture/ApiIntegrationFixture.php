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
	 * Sets event dispatcher.
	 *
	 * @param EventDispatcherInterface $event_dispatcher Event dispatcher.
	 *
	 * @return void
	 */
	public function setEventDispatcher(EventDispatcherInterface $event_dispatcher)
	{
		parent::setEventDispatcher($event_dispatcher);

		$event_dispatcher->addListener(self::TEST_ENDED_EVENT, array($this, 'verifyRemoteAPICalls'));
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

		if ( get_class($test_case) !== get_class($this) || $test_case->getName() !== $this->getName() ) {
			return;
		}

		$browser = $this->getBrowser();

		if ( $browser instanceof SauceLabsBrowserConfiguration ) {
			$session = $event->getSession();

			if ( $session === null ) {
				$this->markTestSkipped('Unable to connect to SauceLabs. Please check Internet connection.');
			}

			$sauce_rest = new SauceRest($browser->getApiUsername(), $browser->getApiKey());
			$job_info = $sauce_rest->getJob($session->getDriver()->getWebDriverSessionId());

			$this->assertEquals(get_class($test_case) . '::' . $test_case->getName(), $job_info['name']);

			$passed_mapping = array(
				'testSuccess' => true,
				'testFailure' => false,
			);

			$this->assertSame($passed_mapping[$test_case->getName()], $job_info['passed']);
		}
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSuccess()
	{
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
			),
			/*'browserstack' => array(
				'type' => 'browserstack',
			),*/
		);
	}

}
