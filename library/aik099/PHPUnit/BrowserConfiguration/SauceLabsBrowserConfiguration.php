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
use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;
use Behat\Mink\Driver\Selenium2Driver;
use WebDriver\SauceLabs\Capability as SauceLabsCapability;
use WebDriver\SauceLabs\SauceRest;

/**
 * Browser configuration tailored to use with "Sauce Labs" service.
 */
class SauceLabsBrowserConfiguration extends BrowserConfiguration
{

	/**
	 * Creates browser configuration.
	 *
	 * @param array $aliases Browser configuration aliases.
	 */
	public function __construct(array $aliases = array())
	{
		parent::__construct($aliases);

		$this->parameters['sauce'] = array('username' => '', 'api_key' => '');
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
		$parameters = array_merge($this->parameters, $this->resolveAlias($parameters));
		$this->setSauce($parameters['sauce']);

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
	 * @param BrowserTestCase $test_case Browser test case.
	 *
	 * @return self
	 */
	public function testSetUpHook(BrowserTestCase $test_case)
	{
		$desired_capabilities = $this->getDesiredCapabilities();

		$desired_capabilities[SauceLabsCapability::NAME] = $this->getJobName($test_case);

		$jenkins_build_number = getenv('BUILD_NUMBER');

		if ( $jenkins_build_number ) {
			$desired_capabilities[SauceLabsCapability::BUILD] = $jenkins_build_number;
		}

		$this->setDesiredCapabilities($desired_capabilities);

		return $this;
	}

	/**
	 * Hook, called from "BrowserTestCase::run" method.
	 *
	 * @param BrowserTestCase               $test_case   Browser test case.
	 * @param \PHPUnit_Framework_TestResult $test_result Test result.
	 *
	 * @return self
	 */
	public function testAfterRunHook(BrowserTestCase $test_case, \PHPUnit_Framework_TestResult $test_result)
	{
		$passed = $this->getTestStatus($test_case, $test_result);
		$this->getRestClient()->updateJob($this->getJobId($test_case), array('passed' => $passed));

		return $this;
	}

	/**
	 * Get Selenium2 current session id.
	 *
	 * @param BrowserTestCase $test_case Browser test case.
	 *
	 * @return string
	 * @throws \RuntimeException When test case session was created using an unsupported driver.
	 */
	protected function getJobId(BrowserTestCase $test_case)
	{
		$driver = $test_case->getSession()->getDriver();

		if ( $driver instanceof Selenium2Driver ) {
			$wd_session = $driver->getWebDriverSession();

			return $wd_session ? basename($wd_session->getUrl()) : '';
		}

		throw new \RuntimeException('Unsupported session driver');
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
	 * Returns API class for "Sauce Labs" service interaction.
	 *
	 * @return SauceRest
	 */
	protected function getRestClient()
	{
		$sauce = $this->getSauce();

		return new SauceRest($sauce['username'], $sauce['api_key']);
	}

}
