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


use Mockery as m;
use tests\aik099\PHPUnit\Fixture\ApiIntegrationFixture;

class ApiIntegrationTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testAPICalls()
	{
		$result = new \PHPUnit_Framework_TestResult();

		$suite = ApiIntegrationFixture::suite('tests\\aik099\\PHPUnit\\Fixture\\ApiIntegrationFixture');
		$suite->run($result);

		$this->assertTrue(true);
	}

}
