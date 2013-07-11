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

class WithoutBrowserConfig extends BrowserTestCase
{

	/**
	 * Test, that always succeeds.
	 *
	 * @return void
	 */
	public function testSuccess()
	{

	}

	/**
	 * Test, that always fails.
	 *
	 * @return void
	 */
	public function testFailure()
	{
		$this->fail('Test, that always fails');
	}

}
