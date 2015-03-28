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
use aik099\PHPUnit\APIClient\SauceLabsAPIClient;
use aik099\PHPUnit\Event\TestEvent;
use WebDriver\SauceLabs\SauceRest;

/**
 * Browser configuration tailored to use with "Sauce Labs" service.
 *
 * @link https://saucelabs.com/php
 */
class SauceLabsBrowserConfiguration extends ApiBrowserConfiguration
{
	const TYPE = 'saucelabs';

	/**
	 * Returns API class for service interaction.
	 *
	 * @return IAPIClient
	 */
	public function getAPIClient()
	{
		$sauce_rest = new SauceRest($this->getApiUsername(), $this->getApiKey());

		return new SauceLabsAPIClient($sauce_rest);
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
			$desired_capabilities['tunnel-identifier'] = getenv('PHPUNIT_MINK_TUNNEL_ID');
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
		return $this->getApiUsername() . ':' . $this->getApiKey() . '@ondemand.saucelabs.com';
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
			$capabilities['platform'] = 'Windows 7';
		}

		if ( !isset($capabilities['version']) ) {
			$capabilities['version'] = '';
		}

		return $capabilities;
	}

}
