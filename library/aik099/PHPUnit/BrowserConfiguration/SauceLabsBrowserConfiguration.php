<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\BrowserConfiguration;


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Event\TestEvent;
use Behat\Mink\Session;
use WebDriver\SauceLabs\SauceRest;

/**
 * Browser configuration tailored to use with "Sauce Labs" service.
 */
class SauceLabsBrowserConfiguration extends BrowserConfiguration
{

	/**
	 * The build number.
	 */
	const BUILD_NUMBER_CAPABILITY = 'build';

	/**
	 * The test name.
	 */
	const NAME_CAPABILITY = 'name';

	/**
	 * Browser configuration factory.
	 *
	 * @var IBrowserConfigurationFactory
	 */
	private $_browserConfigurationFactory;

	/**
	 * Creates browser configuration.
	 *
	 * @param IBrowserConfigurationFactory $browser_configuration_factory Browser configuration factory.
	 */
	public function __construct(IBrowserConfigurationFactory $browser_configuration_factory)
	{
		$this->_browserConfigurationFactory = $browser_configuration_factory;
		$this->defaultParameters['sauce'] = array('username' => '', 'api_key' => '');

		parent::__construct();
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		$events = parent::getSubscribedEvents();
		$events[BrowserTestCase::TEST_SETUP_EVENT] = array('onTestSetup', 100);
		$events[BrowserTestCase::TEST_ENDED_EVENT] = array('onTestEnded', 100);

		return $events;
	}

	/**
	 * Initializes a browser with given configuration.
	 *
	 * @param array $parameters Browser configuration parameters.
	 *
	 * @return self
	 */
	public function setup(array $parameters)
	{
		$prepared_parameters = $this->prepareParameters($parameters);
		$this->setSauce($prepared_parameters['sauce']);

		return parent::setup($parameters);
	}

	/**
	 * Sets "Sauce Labs" connection details.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param array $sauce Connection details.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When incorrect sauce is given.
	 * @link https://saucelabs.com/php
	 */
	public function setSauce(array $sauce)
	{
		if ( !isset($sauce['username']) || !isset($sauce['api_key']) ) {
			throw new \InvalidArgumentException('Incorrect sauce');
		}

		$this->parameters['sauce'] = $sauce;

		return $this;
	}

	/**
	 * Returns "Sauce Labs" connection details.
	 *
	 * @return array
	 * @link https://saucelabs.com/php
	 */
	public function getSauce()
	{
		return $this->parameters['sauce'];
	}

	/**
	 * Returns hostname from browser configuration.
	 *
	 * @return string
	 */
	public function getHost()
	{
		$sauce = $this->getSauce();

		return $sauce['username'] . ':' . $sauce['api_key'] . '@ondemand.saucelabs.com';
	}

	/**
	 * Returns port from browser configuration.
	 *
	 * @return integer
	 */
	public function getPort()
	{
		return 80;
	}

	/**
	 * Returns browser name from browser configuration.
	 *
	 * @return string
	 */
	public function getBrowserName()
	{
		$browser_name = parent::getBrowserName();

		return strlen($browser_name) ? $browser_name : 'chrome';
	}

	/**
	 * Returns desired capabilities from browser configuration.
	 *
	 * @return array
	 */
	public function getDesiredCapabilities()
	{
		$capabilities = parent::getDesiredCapabilities();

		if ( !isset($capabilities['platform']) ) {
			$capabilities['platform'] = 'Windows XP';
		}

		if ( !isset($capabilities['version']) ) {
			$capabilities['version'] = '';
		}

		return $capabilities;
	}

	/**
	 * Hook, called from "BrowserTestCase::setUp" method.
	 *
	 * @param TestEvent $event Test event.
	 *
	 * @return void
	 */
	public function onTestSetup(TestEvent $event)
	{
		$desired_capabilities = $this->getDesiredCapabilities();
		$desired_capabilities[self::NAME_CAPABILITY] = $this->getJobName($event->getTestCase());

		$jenkins_build_number = getenv('BUILD_NUMBER');

		if ( $jenkins_build_number ) {
			$desired_capabilities[self::BUILD_NUMBER_CAPABILITY] = $jenkins_build_number;
		}

		$this->setDesiredCapabilities($desired_capabilities);
	}

	/**
	 * Returns Job name for "Sauce Labs" service.
	 *
	 * @param BrowserTestCase $test_case Browser test case.
	 *
	 * @return string
	 */
	protected function getJobName(BrowserTestCase $test_case)
	{
		if ( $this->isShared() ) {
			return get_class($test_case);
		}

		return $test_case->toString();
	}

	/**
	 * Hook, called from "BrowserTestCase::run" method.
	 *
	 * @param TestEndedEvent $event Test ended event.
	 *
	 * @return void
	 */
	public function onTestEnded(TestEndedEvent $event)
	{
		if ( $event->getSession() === null ) {
			// session wasn't used in particular test
			return;
		}

		$test_case = $event->getTestCase();

		$this->getAPIClient()->updateJob(
			$this->getSessionId($event->getSession()),
			array('passed' => $this->getTestStatus($test_case, $event->getTestResult()))
		);
	}

	/**
	 * Returns API class for "Sauce Labs" service interaction.
	 *
	 * @return SauceRest
	 */
	protected function getAPIClient()
	{
		return $this->_browserConfigurationFactory->createAPIClient($this);
	}

	/**
	 * Get Selenium2 current session id.
	 *
	 * @param Session $session Session.
	 *
	 * @return string
	 * @throws \RuntimeException When session was created using an unsupported driver.
	 */
	protected function getSessionId(Session $session)
	{
		$driver = $session->getDriver();

		if ( method_exists($driver, 'getWebDriverSession') ) {
			$wd_session = $driver->getWebDriverSession();

			return $wd_session ? basename($wd_session->getUrl()) : '';
		}

		return '';
	}

}
