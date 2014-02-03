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
	 * Creates API client.
	 *
	 * @param BrowserConfiguration $browser Browser configuration.
	 *
	 * @return \stdClass
	 * @throws \LogicException When unsupported browser configuration given.
	 */
	public function createAPIClient(BrowserConfiguration $browser);

}
