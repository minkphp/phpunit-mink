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
 * Browser configuration tailored to use with "BrowserStack" service.
 *
 * @link https://www.browserstack.com/automate
 */
class BrowserStackBrowserConfiguration extends ApiBrowserConfiguration
{
	const TYPE = 'browserstack';

	/**
	 * @inheritDoc
	 */
	public function onTestSetup(BrowserTestCase $test_case)
	{
		parent::onTestSetup($test_case);

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
	 * @link   https://www.browserstack.com/docs/automate/selenium/select-browsers-and-devices#Selenium_Legacy_JSON
	 */
	public function getDesiredCapabilities()
	{
		$capabilities = parent::getDesiredCapabilities();

		if ( !isset($capabilities['os']) ) {
			$capabilities['os'] = 'Windows';
			$capabilities['os_version'] = '10';
		}

		if ( !isset($capabilities['acceptSslCerts']) ) {
			$capabilities['acceptSslCerts'] = 'true';
		}

		return $capabilities;
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
		// The BrowserStack started to replace "\" with " " some time ago.
		return \str_replace('\\', '-', parent::getJobName($test_case));
	}

}
