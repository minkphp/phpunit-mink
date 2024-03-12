<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Integration;


use ConsoleHelpers\PHPUnitCompat\Framework\TestResult;
use tests\aik099\PHPUnit\AbstractTestCase;
use tests\aik099\PHPUnit\Fixture\ApiIntegrationFixture;

class ApiIntegrationTest extends AbstractTestCase
{

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		// Define the constant because this test is running PHPUnit testcases manually.
		if ( $this->isInIsolation() ) {
			define('PHPUNIT_TESTSUITE', true);
		}
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @large
	 * @runInSeparateProcess
	 */
	public function testAPICalls()
	{
		$result = new TestResult();

		$suite = ApiIntegrationFixture::suite('tests\\aik099\\PHPUnit\\Fixture\\ApiIntegrationFixture');
		$suite->run($result);

		$this->assertTrue(true);
	}

}
