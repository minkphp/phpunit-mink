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
use Behat\Mink\Session;
use Mockery as m;

class IsolatedSessionStrategyTest extends \PHPUnit_Framework_TestCase
{

	const BROWSER_CLASS = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

	const SESSION_CLASS = '\\Behat\\Mink\\Session';

	/**
	 * Session strategy.
	 *
	 * @var IsolatedSessionStrategy
	 */
	protected $strategy;

	/**
	 * Creates session strategy.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->strategy = new IsolatedSessionStrategy();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSession()
	{
		$expected_session1 = $this->createSession(1);
		$expected_session2 = $this->createSession(1);

		/* @var $browser BrowserConfiguration */
		$browser = m::mock(self::BROWSER_CLASS);
		$browser->shouldReceive('createSession')->twice()->andReturn($expected_session1, $expected_session2);

		$session1 = $this->strategy->session($browser);
		$session2 = $this->strategy->session($browser);

		$this->assertInstanceOf(self::SESSION_CLASS, $session1);
		$this->assertSame($expected_session1, $session1);

		$this->assertInstanceOf(self::SESSION_CLASS, $session2);
		$this->assertSame($expected_session2, $session2);

		$this->assertNotSame($session1, $session2);
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
	public function testEndOfTestWithSession()
	{
		$session = $this->createSession(0);
		$session->shouldReceive('stop')->withNoArgs()->once()->andReturnNull();

		$this->assertSame($this->strategy, $this->strategy->endOfTest($session));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestWithoutSession()
	{
		$this->assertSame($this->strategy, $this->strategy->endOfTest());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testEndOfTestCase()
	{
		$this->assertSame($this->strategy, $this->strategy->endOfTestCase());
	}

	/**
	 * Creates session.
	 *
	 * @param integer $start_count Session start time count.
	 *
	 * @return Session
	 */
	protected function createSession($start_count = 0)
	{
		$session = m::mock(self::SESSION_CLASS);
		$session->shouldReceive('start')->withNoArgs()->times($start_count)->andReturnNull();

		return $session;
	}

}
