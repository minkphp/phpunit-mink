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

	const BROWSER_CLASS = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

	const SESSION_CLASS = '\\Behat\\Mink\\Session';

	/**
	 * Session strategy.
	 *
	 * @var SharedSessionStrategy
	 */
	protected $strategy;

	/**
	 * Isolated strategy.
	 *
	 * @var IsolatedSessionStrategy
	 */
	protected $isolatedStrategy;

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

		$this->session1 = $this->createSession();
		$this->session2 = $this->createSession();

		$this->isolatedStrategy = m::mock('\\aik099\\PHPUnit\\SessionStrategy\\IsolatedSessionStrategy');
		$this->strategy = new SharedSessionStrategy($this->isolatedStrategy);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSessionSharing()
	{
		$browser = m::mock(self::BROWSER_CLASS);
		/* @var $browser BrowserConfiguration */

		$this->isolatedStrategy->shouldReceive('session')->once()->with($browser)->andReturn($this->session1);

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
		$browser = m::mock(self::BROWSER_CLASS);
		/* @var $browser BrowserConfiguration */

		$this->isolatedStrategy
		->shouldReceive('session')->twice()->with($browser)->andReturn($this->session1, $this->session2);

		$this->session1->shouldReceive('stop')->once()->andReturnNull();

		$session = $this->strategy->session($browser);
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
		$session = $this->createSession();
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

	/**
	 * Creates session mock.
	 *
	 * @return Session
	 */
	protected function createSession()
	{
		$session = m::mock(self::SESSION_CLASS);
		$session->shouldReceive('getDriver')->andReturnNull();
		$session->shouldReceive('switchToWindow')->with(null)->andReturnNull();

		return $session;
	}

}
