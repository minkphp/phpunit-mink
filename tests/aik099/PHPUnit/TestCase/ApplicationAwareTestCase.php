<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\TestCase;


use aik099\PHPUnit\Application;
use Mockery\MockInterface;
use Mockery as m;

class ApplicationAwareTestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * Application.
	 *
	 * @var Application|MockInterface
	 */
	protected $application;

	/**
	 * Configures the tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->application = m::mock('aik099\\PHPUnit\\Application');
	}

	/**
	 * Expects a factory call.
	 *
	 * @param string $service_id      Service ID.
	 * @param mixed  $returned_object Object to return.
	 *
	 * @return void
	 */
	protected function expectFactoryCall($service_id, $returned_object)
	{
		if ( is_array($returned_object) ) {
			$this->application->shouldReceive('getObject')->with($service_id)->andReturnValues($returned_object);
		}
		else {
			$this->application->shouldReceive('getObject')->with($service_id)->andReturn($returned_object);
		}
	}

}
