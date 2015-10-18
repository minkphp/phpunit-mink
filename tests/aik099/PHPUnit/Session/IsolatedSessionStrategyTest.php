<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Session;


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\Event\TestEndedEvent;
use aik099\PHPUnit\Session\ISessionFactory;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use Mockery as m;
use Mockery\MockInterface;

class IsolatedSessionStrategyTest extends SessionStrategyTestCase
{

	/**
	 * Session factory.
	 *
	 * @var ISessionFactory|MockInterface
	 */
	private $_factory;

	/**
	 * Creates session strategy.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->_factory = m::mock('aik099\\PHPUnit\\Session\\ISessionFactory');
		$this->strategy = new IsolatedSessionStrategy($this->_factory);

		parent::setUp();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSession()
	{
		$browser = m::mock(self::BROWSER_CLASS);

		$session1 = m::mock(self::SESSION_CLASS);
		$session2 = m::mock(self::SESSION_CLASS);

		$this->_factory
			->shouldReceive('createSession')
			->with($browser)
			->twice()
			->andReturn($session1, $session2);

		$this->assertEquals($session1, $this->strategy->session($browser));
		$this->assertEquals($session2, $this->strategy->session($browser));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testOnTestEnd()
	{
		$session = m::mock(self::SESSION_CLASS);
		$session->shouldReceive('stop')->once();
		$session->shouldReceive('isStarted')->once()->andReturn(true);

		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getSessionStrategy')->once()->andReturn($this->strategy);

		$event = $this->eventDispatcher->dispatch(
			BrowserTestCase::TEST_ENDED_EVENT,
			new TestEndedEvent(
				$test_case,
				m::mock('PHPUnit_Framework_TestResult'),
				$session
			)
		);

		$this->assertInstanceOf('aik099\\PHPUnit\\Event\\TestEndedEvent', $event);
	}

}
