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
 * Browser configuration factory.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class BrowserConfigurationFactory implements IBrowserConfigurationFactory
{

	/**
	 * Browser configurations.
	 *
	 * @var array
	 */
	protected $browserConfigurations = array();

	/**
	 * Registers a browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return void
	 * @throws \InvalidArgumentException When browser configuration is already registered.
	 */
	public function register(BrowserConfiguration $browser)
	{
		$type = $browser->getType();

		if ( isset($this->browserConfigurations[$type]) ) {
			throw new \InvalidArgumentException(
				'Browser configuration with type "' . $type . '" is already registered'
			);
		}

		$this->browserConfigurations[$type] = $browser;
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

		$type = isset($config['type']) ? $config['type'] : 'default';
		unset($config['type']);

		return $this->create($type)->setAliases($aliases)->setup($config);
	}

	/**
	 * Creates browser configuration based on give type.
	 *
	 * @param string $type Type.
	 *
	 * @return BrowserConfiguration
	 * @throws \InvalidArgumentException When browser configuration not registered.
	 */
	protected function create($type)
	{
		if ( !isset($this->browserConfigurations[$type]) ) {
			throw new \InvalidArgumentException('Browser configuration type "' . $type . '" not registered');
		}

		return clone $this->browserConfigurations[$type];
	}

}
