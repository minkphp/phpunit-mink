<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\SessionStrategy;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\SessionStrategy\IsolatedSessionStrategy;
use aik099\PHPUnit\SessionStrategy\SharedSessionStrategy;
use Behat\Mink\Session;
use Mockery as m;

class SharedSessionStrategyTest extends \PHPUnit_Framework_TestCase
{

	const SESSION_CLASS = '\\Behat\\Mink\\Session';

	/**
	 * Session strategy.
	 *
	 * @var SharedSessionStrategy
	 */
	protected $strategy;

	/**
	 * First created session.
	 *
	 * @var IsolatedSessionStrategy
	 */
	protected $session1;

	/**
	 * Second created session.
	 *
	 * @var IsolatedSessionStrategy
	 */
	protected $session2;

	/**
	 * Creates session strategy.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->session1 = $this->createSessionMock();
		$this->session2 = $this->createSessionMock();

		$isolated_strategy = m::mock('\\aik099\\PHPUnit\\SessionStrategy\\IsolatedSessionStrategy');
		/* @var $isolated_strategy IsolatedSessionStrategy */

		$isolated_strategy->shouldReceive('session')->andReturn($this->session1, $this->session2);

		$this->strategy = new SharedSessionStrategy($isolated_strategy);
	}

	/**
	 * Creates session mock.
	 *
	 * @return Session
	 */
	protected function createSessionMock()
	{
		$session = m::mock(self::SESSION_CLASS);
		$session->shouldReceive('getDriver')->andReturnNull();
		$session->shouldReceive('switchToWindow')->with(null)->andReturnNull();

		return $session;
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSessionSharing()
	{
		$browser = m::mock('\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		/* @var $browser BrowserConfiguration */

		$this->assertSame($this->session1, $this->strategy->session($browser));
		$this->assertSame($this->session1, $this->strategy->session($browser));
		$this->assertSame($this->session1, $this->strategy->session($browser));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSessionResetOnFailure()
	{
		$browser = m::mock('\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		/* @var $browser BrowserConfiguration */

		$session = $this->strategy->session($browser);
		$session->shouldReceive('stop')->once()->andReturnNull();
		$this->assertSame($this->session1, $session);

		$this->strategy->notSuccessfulTest(new \Exception());

		$this->assertSame($this->session2, $this->strategy->session($browser));
		$this->assertSame($this->session2, $this->strategy->session($browser));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testNotSuccessfulTest()
	{
		$this->assertSame($this->strategy, $this->strategy->notSuccessfulTest(new \Exception()));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTest()
	{
		$this->assertSame($this->strategy, $this->strategy->endOfTest());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestCaseWithSession()
	{
		$session = m::mock(self::SESSION_CLASS);
		/* @var $session \Behat\Mink\Session */

		$session->shouldReceive('stop')->withNoArgs()->once()->andReturnNull();
		$this->assertSame($this->strategy, $this->strategy->endOfTestCase($session));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestCaseWithoutSession()
	{
		$this->assertSame($this->strategy, $this->strategy->endOfTestCase());
	}

}