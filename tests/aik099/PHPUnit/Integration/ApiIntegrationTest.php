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


use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\TestResult;
use tests\aik099\PHPUnit\Fixture\ApiIntegrationFixture;

class ApiIntegrationTest extends MockeryTestCase
{

	protected function setUp()
	{
		parent::setUp();

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

		$suite = ApiIntegrationFixture::suite(ApiIntegrationFixture::class);
		$suite->run($result);

		$this->assertTrue(true);
	}

}
