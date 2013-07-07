<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Fixture;


use aik099\PHPUnit\BrowserTestCase;

class WithBrowserConfig extends BrowserTestCase
{

	/**
	 * Browser configurations.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array(
			'browserName' => 'firefox',
			'host' => 'localhost',
		),
		array(
			'browserName' => 'chrome',
			'host' => '127.0.0.1',
		),
	);

	/**
	 * Test One.
	 *
	 * @return void
	 */
	public function testOne()
	{

	}

	/**
	 * Test One.
	 *
	 * @return void
	 */
	public function testTwo()
	{

	}

}
