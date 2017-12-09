<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */


namespace aik099\PHPUnit\MinkDriver;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use Behat\Mink\Driver\DriverInterface;

class GoutteDriverFactory implements IMinkDriverFactory
{

	/**
	 * Returns driver name, that can be used in browser configuration.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'goutte';
	}

	/**
	 * Returns default values for browser configuration.
	 *
	 * @return array
	 */
	public function getDriverDefaults()
	{
		return array(
			'driverOptions' => array(
				'server_parameters' => array(),
				'guzzle_parameters' => array(),
			),
		);
	}

	/**
	 * Returns a new driver instance according to the browser configuration.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return DriverInterface
	 * @throws \RuntimeException When driver isn't installed.
	 */
	public function createDriver(BrowserConfiguration $browser)
	{
		if ( !class_exists('Behat\Mink\Driver\GoutteDriver') ) {
			throw new \RuntimeException(
				'Install MinkGoutteDriver in order to use goutte driver.'
			);
		}

		$driver_options = $browser->getDriverOptions();

		if ( $this->_isGoutte1() ) {
			$guzzle_client = $this->_buildGuzzle3Client($driver_options['guzzle_parameters']);
		}
		elseif ( $this->_isGuzzle6() ) {
			$guzzle_client = $this->_buildGuzzle6Client($driver_options['guzzle_parameters']);
		}
		else {
			$guzzle_client = $this->_buildGuzzle4Client($driver_options['guzzle_parameters']);
		}

		$goutte_client = new \Behat\Mink\Driver\Goutte\Client($driver_options['server_parameters']);
		$goutte_client->setClient($guzzle_client);

		return new \Behat\Mink\Driver\GoutteDriver($goutte_client);
	}

	/**
	 * Builds Guzzle 6 client.
	 *
	 * @param array $parameters Parameters.
	 *
	 * @return \GuzzleHttp\Client
	 */
	private function _buildGuzzle6Client(array $parameters)
	{
		// Force the parameters set by default in Goutte to reproduce its behavior.
		$parameters['allow_redirects'] = false;
		$parameters['cookies'] = true;

		return new \GuzzleHttp\Client($parameters);

	}

	/**
	 * Builds Guzzle 4 client.
	 *
	 * @param array $parameters Parameters.
	 *
	 * @return \GuzzleHttp\Client
	 */
	private function _buildGuzzle4Client(array $parameters)
	{
		// Force the parameters set by default in Goutte to reproduce its behavior.
		$parameters['allow_redirects'] = false;
		$parameters['cookies'] = true;

		return new \GuzzleHttp\Client(array('defaults' => $parameters));

	}

	/**
	 * Builds Guzzle 3 client.
	 *
	 * @param array $parameters Parameters.
	 *
	 * @return \Guzzle\Http\Client
	 */
	private function _buildGuzzle3Client(array $parameters)
	{
		// Force the parameters set by default in Goutte to reproduce its behavior.
		$parameters['redirect.disable'] = true;

		return new \Guzzle\Http\Client(null, $parameters);
	}

	/**
	 * Determines Goutte client version.
	 *
	 * @return boolean
	 */
	private function _isGoutte1()
	{
		$reflection = new \ReflectionParameter(array('Goutte\Client', 'setClient'), 0);

		return $reflection->getClass() && 'Guzzle\Http\ClientInterface' === $reflection->getClass()->name;
	}
	
	/**
	 * Determines Guzzle version.
	 *
	 * @return boolean
	 */
	private function _isGuzzle6()
	{
		return interface_exists('GuzzleHttp\ClientInterface') && 
		version_compare(\GuzzleHttp\ClientInterface::VERSION, '6.0.0', '>=');
	}

}
