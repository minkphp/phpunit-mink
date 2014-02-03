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


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Event\TestEvent;
use Behat\Mink\Session;
use Mockery as m;

class TestEventTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Test event.
	 *
	 * @var TestEvent
	 */
	protected $event;

	/**
	 * Test case.
	 *
	 * @var BrowserTestCase
	 */
	protected $testCase;

	/**
	 * Session.
	 *
	 * @var Session
	 */
	protected $session;

	/**
	 * Prepares the tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->testCase = m::mock('aik099\\PHPUnit\\BrowserTestCase');
		$this->session = m::mock('Behat\\Mink\\Session');
		$this->event = $this->createEvent();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetTestCase()
	{
		$this->assertSame($this->testCase, $this->event->getTestCase());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSession()
	{
		$this->assertSame($this->session, $this->event->getSession());
	}

	/**
	 * Creates new event.
	 *
	 * @return TestEvent
	 */
	protected function createEvent()
	{
		return new TestEvent($this->testCase, $this->session);
	}

}
