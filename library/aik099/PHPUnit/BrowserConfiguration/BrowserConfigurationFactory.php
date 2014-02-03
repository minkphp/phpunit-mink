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
use aik099\PHPUnit\ITestApplicationAware;
use aik099\PHPUnit\TestApplication;
use WebDriver\SauceLabs\SauceRest;

class BrowserConfigurationFactory implements IBrowserConfigurationFactory, ITestApplicationAware
{

	/**
	 * Application.
	 *
	 * @var TestApplication
	 */
	protected $application;

	/**
	 * Sets application.
	 *
	 * @param TestApplication $application The application.
	 *
	 * @return void
	 */
	public function setApplication(TestApplication $application)
	{
		$this->application = $application;
	}

	/**
	 * Returns browser configuration instance.
	 *
	 * @param array           $config    Browser.
	 * @param BrowserTestCase $test_case Test case.
	 *
	 * @return BrowserConfiguration
	 */
	public function createBrowserConfiguration(array $config, BrowserTestCase $test_case)
	{
		$aliases = $test_case->getBrowserAliases();
		$config = BrowserConfiguration::resolveAliases($config, $aliases);

		/** @var BrowserConfiguration $browser */
		if ( isset($config['sauce']) ) {
			$browser = $this->application->getObject('sauce_labs_browser_configuration');
		}
		else {
			$browser = $this->application->getObject('browser_configuration');
		}

		$browser->setAliases($aliases);
		$browser->setup($config);

		return $browser;
	}

	/**
	 * Creates API client.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return \stdClass
	 * @throws \LogicException When unsupported browser configuration given.
	 */
	public function createAPIClient(BrowserConfiguration $browser)
	{
		if ( $browser instanceof SauceLabsBrowserConfiguration ) {
			$sauce = $browser->getSauce();

			return new SauceRest($sauce['username'], $sauce['api_key']);
		}

		throw new \LogicException('Unsupported browser configuration given');
	}

}
