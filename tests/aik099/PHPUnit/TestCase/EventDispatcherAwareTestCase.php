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


use Mockery\MockInterface;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherAwareTestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * Event dispatcher.
	 *
	 * @var EventDispatcherInterface|MockInterface
	 */
	protected $eventDispatcher;

	/**
	 * Configures the tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->eventDispatcher = m::mock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
	}

	/**
	 * Expects a specific event to be called.
	 *
	 * @param string $event_name Event name.
	 *
	 * @return void
	 */
	protected function expectEvent($event_name)
	{
		$this->eventDispatcher
			->shouldReceive('dispatch')
			->with($event_name, m::any())
			->once();
	}

}
