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


use aik099\PHPUnit\Session\ISessionFactory;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use Mockery as m;
use Mockery\MockInterface;

class IsolatedSessionStrategyTest extends AbstractSessionStrategyTestCase
{

	/**
	 * Session factory.
	 *
	 * @var ISessionFactory|MockInterface
	 */
	private $_factory;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_factory = m::mock(ISessionFactory::class);
		$this->strategy = new IsolatedSessionStrategy($this->_factory);
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

	public function testIsFreshSessionAfterSessionIsStarted()
	{
		$browser = m::mock(self::BROWSER_CLASS);
		$session = m::mock(self::SESSION_CLASS);

		$this->_factory->shouldReceive('createSession')->with($browser)->once()->andReturn($session);

		$this->strategy->session($browser);

		$this->assertTrue($this->strategy->isFreshSession());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testOnTestEnded()
	{
		$session = m::mock(self::SESSION_CLASS);
		$session->shouldReceive('stop')->once();
		$session->shouldReceive('isStarted')->once()->andReturn(true);

		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getSession')->with(false)->once()->andReturn($session);

		$this->strategy->onTestEnded($test_case);
	}

	public function testOnTestFailed()
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getSession')->never();

		$this->strategy->onTestFailed($test_case, new \Exception('test'));
	}

	public function testOnTestSuiteEnded()
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getSession')->never();

		$this->strategy->onTestSuiteEnded($test_case);
	}

}
