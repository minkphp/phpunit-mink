<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit;


use WebDriver\SauceLabs\Capability as SauceLabsCapability;
use WebDriver\SauceLabs\SauceRest;

class SauceLabsConnector
{

	/**
	 * Test case.
	 *
	 * @var BrowserTestCase
	 */
	protected $testCase;

	/**
	 * Creates "Sauce Labs" Connector.
	 *
	 * @param BrowserTestCase $test_case Browser test case.
	 */
	public function __construct(BrowserTestCase $test_case)
	{
		$this->testCase = $test_case;
	}

	/**
	 * Patches test case browser configuration.
	 *
	 * @return void
	 */
	public function patchBrowserConfiguration()
	{
		$browser = $this->testCase->getBrowser();
		$desired_capabilities = $browser->getDesiredCapabilities();

		$desired_capabilities[SauceLabsCapability::NAME] = $this->getJobName();

		$jenkins_build_number = getenv('BUILD_NUMBER');

		if ( $jenkins_build_number ) {
			$desired_capabilities[SauceLabsCapability::BUILD] = $jenkins_build_number;
		}

		$browser->setDesiredCapabilities($desired_capabilities);
	}

	/**
	 * Returns Job name for "Sauce Labs" service.
	 *
	 * @return string
	 */
	protected function getJobName()
	{
		if ( $this->testCase->isShared() ) {
			return get_class($this);
		}

		return $this->testCase->toString();
	}

	/**
	 * Sets Job status.
	 *
	 * @param boolean $passed Job status.
	 *
	 * @return void
	 */
	public function setJobStatus($passed)
	{
		$this->getRest()->updateJob($this->getSessionId(), array('passed' => $passed));
	}

	/**
	 * Get Selenium2 current session id.
	 *
	 * @return string
	 */
	protected function getSessionId()
	{
		$driver = $this->testCase->getSession()->getDriver();
		/* @var $driver \Behat\Mink\Driver\Selenium2Driver */

		$wd_session = $driver->getWebDriverSession();

		return $wd_session ? basename($wd_session->getUrl()) : '';
	}

	/**
	 * Returns API class for "Sauce Labs" service interaction.
	 *
	 * @return SauceRest
	 */
	protected function getRest()
	{
		$browser = $this->testCase->getBrowser();
		/* @var $browser \aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration */

		$sauce = $browser->getSauce();

		return new SauceRest($sauce['username'], $sauce['api_key']);
	}

}