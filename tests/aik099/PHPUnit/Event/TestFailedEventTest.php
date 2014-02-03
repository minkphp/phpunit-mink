<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Event;


use aik099\PHPUnit\Event\TestFailedEvent;
use Mockery\CountValidator\Exception;

class TestFailedEventTest extends TestEventTest
{

	/**
	 * Exception.
	 *
	 * @var \Exception
	 */
	private $_exception;

	/**
	 * Prepares the tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->_exception = new Exception();

		parent::setUp();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetException()
	{
		$this->assertSame($this->_exception, $this->event->getException());
	}

	/**
	 * Creates new event.
	 *
	 * @return TestFailedEvent
	 */
	protected function createEvent()
	{
		return new TestFailedEvent($this->_exception, $this->testCase, $this->session);
	}

}
