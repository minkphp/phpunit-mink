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
use Mockery as m;

class IsolatedSessionStrategyTest extends \PHPUnit_Framework_TestCase
{

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
		/* @var $browser BrowserConfiguration */
		$browser = m::mock('\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');

		$browser->shouldReceive('createSession')->withNoArgs()->once()->andReturnUsing(function () {
			$session = m::mock(IsolatedSessionStrategyTest::SESSION_CLASS);
			$session->shouldReceive('start')->withNoArgs()->once()->andReturnNull();

			return $session;
		});

		$this->assertInstanceOf(self::SESSION_CLASS, $this->strategy->session($browser));
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
		$session = m::mock(self::SESSION_CLASS);
		/* @var $session \Behat\Mink\Session */

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

}
