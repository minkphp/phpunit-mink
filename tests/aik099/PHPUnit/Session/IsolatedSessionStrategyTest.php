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


use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Session;
use Mockery as m;

class IsolatedSessionStrategyTest extends AbstractSessionStrategyTestCase
{

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$this->strategy = new IsolatedSessionStrategy();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSession()
	{
		$browser = m::mock(self::BROWSER_CLASS);

		$driver1 = m::mock(DriverInterface::class);
		$driver1->shouldReceive('setSession')->with(m::type(Session::class))->once();

		$driver2 = m::mock(DriverInterface::class);
		$driver2->shouldReceive('setSession')->with(m::type(Session::class))->once();

		$browser->shouldReceive('createDriver')->twice()->andReturn($driver1, $driver2);

		$session1 = $this->strategy->session($browser);
		$this->assertInstanceOf(Session::class, $session1);
		$this->assertSame($driver1, $session1->getDriver());

		$session2 = $this->strategy->session($browser);
		$this->assertInstanceOf(Session::class, $session2);
		$this->assertSame($driver2, $session2->getDriver());
	}

	public function testIsFreshSessionAfterSessionIsStarted()
	{
		$driver = m::mock(DriverInterface::class);
		$driver->shouldReceive('setSession')->with(m::type(Session::class))->once();

		$browser = m::mock(self::BROWSER_CLASS);
		$browser->shouldReceive('createDriver')->once()->andReturn($driver);

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
