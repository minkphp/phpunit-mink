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
 * Interface for browser factory creation.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
interface IBrowserConfigurationFactory
{

	/**
	 * Returns browser configuration instance.
	 *
	 * @param array           $config    Browser.
	 * @param BrowserTestCase $test_case Test case.
	 *
	 * @return BrowserConfiguration
	 */
	public function createBrowserConfiguration(array $config, BrowserTestCase $test_case);

	/**
	 * Registers a browser configuration.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return void
	 * @throws \InvalidArgumentException When browser configuration is already registered.
	 */
	public function register(BrowserConfiguration $browser);

}
