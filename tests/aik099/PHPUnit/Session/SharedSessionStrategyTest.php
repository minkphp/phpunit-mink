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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use aik099\PHPUnit\Session\SharedSessionStrategy;
use Behat\Mink\Session;
use Mockery as m;
use Mockery\MockInterface;
use aik099\PHPUnit\Framework\IncompleteTestError;
use aik099\PHPUnit\Framework\SkippedTestError;

class SharedSessionStrategyTest extends SessionStrategyTestCase
{

	/**
	 * Isolated strategy.
	 *
	 * @var IsolatedSessionStrategy
	 */
	private $_isolatedStrategy;

	/**
	 * First created session.
	 *
	 * @var IsolatedSessionStrategy
	 */
	private $_session1;

	/**
	 * Second created session.
	 *
	 * @var IsolatedSessionStrategy
	 */
	private $_session2;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_session1 = $this->createSession();
		$this->_session2 = $this->createSession();

		$this->_isolatedStrategy = m::mock('\\aik099\\PHPUnit\\Session\\IsolatedSessionStrategy');
		$this->strategy = new SharedSessionStrategy($this->_isolatedStrategy);
	}

	/**
	 * Test description.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return void
	 * @dataProvider ignoreExceptionDataProvider
	 */
	public function testSessionSharing(\Exception $e = null)
	{
		/* @var $browser BrowserConfiguration */
		$browser = m::mock(self::BROWSER_CLASS);
		$this->_isolatedStrategy->shouldReceive('session')->once()->with($browser)->andReturn($this->_session1);

		$this->_session1->shouldReceive('switchToWindow')->once();

		$this->assertSame($this->_session1, $this->strategy->session($browser));

		if ( isset($e) ) {
			$this->_sessionFailure($e);
		}

		$this->assertSame($this->_session1, $this->strategy->session($browser));
	}

	/**
	 * Returns exceptions, that doesn't reset session.
	 *
	 * @return array
	 */
	public function ignoreExceptionDataProvider()
	{
		return array(
			array(null),
			array(new IncompleteTestError()),
			array(new SkippedTestError()),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSessionResetOnFailure()
	{
		/* @var $browser BrowserConfiguration */
		$browser = m::mock(self::BROWSER_CLASS);

		$this->_isolatedStrategy
			->shouldReceive('session')
			->with($browser)
			->twice()
			->andReturn($this->_session1, $this->_session2);

		$this->_session1->shouldReceive('stop')->once();
		$this->_session2->shouldReceive('switchToWindow')->once();

		$session = $this->strategy->session($browser);
		$this->assertSame($this->_session1, $session);

		$this->_sessionFailure(new \Exception());

		$this->assertSame($this->_session2, $this->strategy->session($browser));
		$this->assertSame($this->_session2, $this->strategy->session($browser));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testImmediateSessionFailure()
	{
		$this->_sessionFailure(new \Exception());

		$this->_isolatedStrategy->shouldReceive('session')->once()->andReturn($this->_session1);
		$this->assertSame($this->_session1, $this->strategy->session(m::mock(self::BROWSER_CLASS)));
	}

	/**
	 * Generates test failure.
	 *
	 * @param \Exception $e Exception.
	 *
	 * @return void
	 */
	private function _sessionFailure(\Exception $e)
	{
		$this->strategy->onTestFailed(m::mock(self::TEST_CASE_CLASS), $e);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testOnTestEnded()
	{
		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getSession')->never();

		$this->strategy->onTestEnded($test_case);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestCaseWithSession()
	{
		$session = $this->createSession();
		$session->shouldReceive('stop')->withNoArgs()->withAnyArgs()->once();
		$session->shouldReceive('isStarted')->once()->andReturn(true);

		$test_case = m::mock(self::TEST_CASE_CLASS);
		$test_case->shouldReceive('getSession')->with(false)->once()->andReturn($session);

		$this->strategy->onTestSuiteEnded($test_case);
	}

	/**
	 * Creates session mock.
	 *
	 * @return Session|MockInterface
	 */
	protected function createSession()
	{
		return m::mock(self::SESSION_CLASS);
	}

}
