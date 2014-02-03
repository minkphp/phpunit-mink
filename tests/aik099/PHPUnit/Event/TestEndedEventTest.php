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


use aik099\PHPUnit\Event\TestEndedEvent;

class TestEndedEventTest extends TestEventTest
{

	/**
	 * Test result.
	 *
	 * @var \PHPUnit_Framework_TestResult
	 */
	private $_testResult;

	/**
	 * Prepares the tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->_testResult = new \PHPUnit_Framework_TestResult();

		parent::setUp();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetTestResult()
	{
		$this->assertSame($this->_testResult, $this->event->getTestResult());
	}

	/**
	 * Creates new event.
	 *
	 * @return TestEndedEvent
	 */
	protected function createEvent()
	{
		return new TestEndedEvent($this->testCase, $this->_testResult, $this->session);
	}

}
