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

/**
 * Browser configuration tailored to use with "Sauce Labs" service.
 *
 * @link https://saucelabs.com/php
 */
class SauceLabsBrowserConfiguration extends ApiBrowserConfiguration
{
	const TYPE = 'saucelabs';

	/**
	 * @inheritDoc
	 */
	public function onTestSetup(BrowserTestCase $test_case)
	{
		parent::onTestSetup($test_case);

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
			$capabilities['platform'] = 'Windows 10';
		}

		if ( !isset($capabilities['acceptInsecureCerts']) ) {
			$capabilities['acceptInsecureCerts'] = true;
		}

		return $capabilities;
	}

}
