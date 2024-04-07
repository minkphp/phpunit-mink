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
use aik099\PHPUnit\Session\ISessionStrategy;
use aik099\PHPUnit\Session\IsolatedSessionStrategy;
use aik099\PHPUnit\Session\SharedSessionStrategy;
use Behat\Mink\Session;
use ConsoleHelpers\PHPUnitCompat\Framework\IncompleteTestError;
use ConsoleHelpers\PHPUnitCompat\Framework\SkippedTestError;
use Mockery as m;
use Mockery\MockInterface;

class SharedSessionStrategyTest extends AbstractSessionStrategyTestCase
{

	/**
	 * Isolated strategy.
	 *
	 * @var IsolatedSessionStrategy
	 */
	private $_originalStrategy;

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

		$this->_originalStrategy = m::mock(ISessionStrategy::class);
		$this->strategy = new SharedSessionStrategy($this->_originalStrategy);
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
		/** @var BrowserConfiguration $browser */
		$browser = m::mock(self::BROWSER_CLASS);
		$this->_originalStrategy->shouldReceive('session')->once()->with($browser)->andReturn($this->_session1);
		$this->_originalStrategy->shouldReceive('isFreshSession')->once()->andReturn(true);

		$this->expectNoPopups($this->_session1);

		$this->assertSame($this->_session1, $this->strategy->session($browser));
		$this->assertTrue($this->strategy->isFreshSession(), 'First created session must be fresh');

		if ( isset($e) ) {
			$this->_sessionFailure($e);
		}

		$this->assertSame($this->_session1, $this->strategy->session($browser));
		$this->assertFalse($this->strategy->isFreshSession(), 'Reused session must not be fresh');
	}

	/**
	 * Expects no popups.
	 *
	 * @param MockInterface $session Session.
	 *
	 * @return void
	 */
	protected function expectNoPopups(MockInterface $session)
	{
		// Testing if popup windows are actually closed will be done in the integration test.
		$session->shouldReceive('switchToWindow')->atLeast()->once();
		$session->shouldReceive('getWindowName')->once()->andReturn('initial-window-name');
		$session->shouldReceive('getWindowNames')->once()->andReturn(array('initial-window-name'));
	}

	/**
	 * Returns exceptions, that doesn't reset session.
	 *
	 * @return array
	 */
	public static function ignoreExceptionDataProvider()
	{
		return array(
			'no error' => array(null),
			'incomplete test' => array(new IncompleteTestError()),
			'skipped test' => array(new SkippedTestError()),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSessionResetOnFailure()
	{
		/** @var BrowserConfiguration $browser */
		$browser = m::mock(self::BROWSER_CLASS);

		$this->_originalStrategy
			->shouldReceive('session')
			->with($browser)
			->twice()
			->andReturn($this->_session1, $this->_session2);
		$this->_originalStrategy
			->shouldReceive('isFreshSession')
			->twice()
			->andReturn(true);

		$this->_session1->shouldReceive('isStarted')->once()->andReturn(true);
		$this->_session1->shouldReceive('stop')->once();
		$this->expectNoPopups($this->_session2);

		$session = $this->strategy->session($browser);
		$this->assertSame($this->_session1, $session);
		$this->assertTrue($this->strategy->isFreshSession(), 'First created session must be fresh');

		$this->_sessionFailure(new \Exception());

		$this->assertSame($this->_session2, $this->strategy->session($browser));
		$this->assertTrue($this->strategy->isFreshSession(), 'First created session after failure must be fresh');

		$this->assertSame($this->_session2, $this->strategy->session($browser));
		$this->assertFalse($this->strategy->isFreshSession(), 'Reused session must not be fresh');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testImmediateSessionFailure()
	{
		$this->_sessionFailure(new \Exception());

		$this->_originalStrategy->shouldReceive('session')->once()->andReturn($this->_session1);
		$this->_originalStrategy->shouldReceive('isFreshSession')->once()->andReturn(true);

		$this->assertSame($this->_session1, $this->strategy->session(m::mock(self::BROWSER_CLASS)));
		$this->assertTrue($this->strategy->isFreshSession(), 'First created session after failure must be fresh');
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
