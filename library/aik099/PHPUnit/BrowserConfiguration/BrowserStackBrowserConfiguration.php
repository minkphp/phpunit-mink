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


use aik099\PHPUnit\APIClient\BrowserStackAPIClient;
use aik099\PHPUnit\APIClient\IAPIClient;
use aik099\PHPUnit\Event\TestEvent;
use WebDriver\ServiceFactory;

/**
 * Browser configuration tailored to use with "BrowserStack" service.
 *
 * @link https://www.browserstack.com/automate
 */
class BrowserStackBrowserConfiguration extends ApiBrowserConfiguration
{
	const TYPE = 'browserstack';

	/**
	 * Returns API class for service interaction.
	 *
	 * @return IAPIClient
	 */
	public function getAPIClient()
	{
		return new BrowserStackAPIClient(
			$this->getApiUsername(),
			$this->getApiKey(),
			ServiceFactory::getInstance()->getService('service.curl')
		);
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

		if ( getenv('PHPUNIT_MINK_TUNNEL_ID') ) {
			$desired_capabilities['browserstack.local'] = 'true';
			$desired_capabilities['browserstack.localIdentifier'] = getenv('PHPUNIT_MINK_TUNNEL_ID');
		}

		$this->setDesiredCapabilities($desired_capabilities);
	}

	/**
	 * Returns hostname from browser configuration.
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->getApiUsername() . ':' . $this->getApiKey() . '@hub.browserstack.com';
	}

	/**
	 * Returns desired capabilities from browser configuration.
	 *
	 * @return array
	 * @link   http://www.browserstack.com/automate/capabilities
	 */
	public function getDesiredCapabilities()
	{
		$capabilities = parent::getDesiredCapabilities();

		if ( !isset($capabilities['os']) ) {
			$capabilities['os'] = 'Windows';
			$capabilities['os_version'] = '7';
		}

		if ( !isset($capabilities['acceptSslCerts']) ) {
			$capabilities['acceptSslCerts'] = 'true';
		}

		return $capabilities;
	}

}
