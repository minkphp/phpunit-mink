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


use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;

class SauceLabsBrowserConfiguration extends BrowserConfiguration
{

	/**
	 * Creates browser configuration.
	 *
	 * @param array $aliases Browser configuration aliases.
	 */
	public function __construct(array $aliases)
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
	public function configure(array $parameters)
	{
		$parameters = array_merge($this->parameters, $this->resolveAlias($parameters));
		$this->setSauce($parameters['sauce']);

		return parent::configure($parameters);
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

}