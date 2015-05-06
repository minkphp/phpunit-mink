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


use aik099\PHPUnit\APIClient\IAPIClient;
use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Event\TestEvent;
use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Browser configuration tailored to use with API-based service.
 *
 * @method string getApiUsername() Returns API username.
 * @method string getApiKey() Returns API key.
 */
abstract class ApiBrowserConfiguration extends BrowserConfiguration
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
	 * Creates browser configuration.
	 *
	 * @param EventDispatcherInterface $event_dispatcher        Event dispatcher.
	 * @param DriverFactoryRegistry    $driver_factory_registry Driver factory registry.
	 */
	public function __construct(
		EventDispatcherInterface $event_dispatcher,
		DriverFactoryRegistry $driver_factory_registry
	) {
		$this->defaults['driver'] = 'selenium2';
		$this->defaults['apiUsername'] = '';
		$this->defaults['apiKey'] = '';

		parent::__construct($event_dispatcher, $driver_factory_registry);
	}

	/**
	 * Sets API username.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $api_username API username.
	 *
	 * @return self
	 */
	public function setApiUsername($api_username)
	{
		return $this->setParameter('apiUsername', $api_username);
	}

	/**
	 * Sets API key.
	 *
	 * To be called from TestCase::setUp().
	 *
	 * @param string $api_key API key.
	 *
	 * @return self
	 */
	public function setApiKey($api_key)
	{
		return $this->setParameter('apiKey', $api_key);
	}

	/**
	 * Sets API username.
	 *
	 * Used internally to to allow using "api_username" parameter and avoid BC break.
	 *
	 * @param string $api_username API username.
	 *
	 * @return     self
	 * @deprecated
	 */
	protected function setApi_username($api_username)
	{
		return $this->setApiUsername($api_username);
	}

	/**
	 * Sets API key.
	 *
	 * Used internally to to allow using "api_key" parameter and avoid BC break.
	 *
	 * @param string $api_key API key.
	 *
	 * @return     self
	 * @deprecated
	 */
	protected function setApi_key($api_key)
	{
		return $this->setApiKey($api_key);
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
	 * Hook, called from "BrowserTestCase::setUp" method.
	 *
	 * @param TestEvent $event Test event.
	 *
	 * @return void
	 */
	public function onTestSetup(TestEvent $event)
	{
		if ( !$event->validateSubscriber($this->getTestCase()) ) {
			return;
		}

		parent::onTestSetup($event);

		$desired_capabilities = $this->getDesiredCapabilities();
		$desired_capabilities[self::NAME_CAPABILITY] = $this->getJobName($event->getTestCase());

		if ( getenv('BUILD_NUMBER') ) {
			$desired_capabilities[self::BUILD_NUMBER_CAPABILITY] = getenv('BUILD_NUMBER'); // Jenkins.
		}
		elseif ( getenv('TRAVIS_BUILD_NUMBER') ) {
			$desired_capabilities[self::BUILD_NUMBER_CAPABILITY] = getenv('TRAVIS_BUILD_NUMBER');
		}

		$this->setDesiredCapabilities($desired_capabilities);
	}

	/**
	 * Returns Job name for API service.
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
		if ( !$event->validateSubscriber($this->getTestCase()) ) {
			return;
		}

		parent::onTestEnded($event);

		$session = $event->getSession();

		if ( $session === null || !$session->isStarted() ) {
			// Session wasn't used in particular test.
			return;
		}

		$test_case = $event->getTestCase();

		$this->getAPIClient()->updateStatus(
			$this->getSessionId($session),
			$this->getTestStatus($test_case, $event->getTestResult())
		);
	}

	/**
	 * Returns API class for service interaction.
	 *
	 * @return IAPIClient
	 */
	public abstract function getAPIClient();

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

		if ( $driver instanceof Selenium2Driver ) {
			return $driver->getWebDriverSessionId();
		}

		throw new \RuntimeException('Unsupported session driver');
	}

}
