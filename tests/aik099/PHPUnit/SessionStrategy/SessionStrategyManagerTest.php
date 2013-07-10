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


use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;
use Mockery as m;

class SessionStrategyManagerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Session strategy manager.
	 *
	 * @var SessionStrategyManager
	 */
	protected $manager;

	/**
	 * Creates session strategy manager to use for tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->manager = SessionStrategyManager::getInstance();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		$this->assertSame(SessionStrategyManager::getInstance(), SessionStrategyManager::getInstance());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionStrategySharing()
	{
		// sequential identical browser configurations share strategy
		$strategy1 = $this->manager->getSessionStrategy(SessionStrategyManager::ISOLATED_STRATEGY, 'H1');
		$strategy2 = $this->manager->getSessionStrategy(SessionStrategyManager::ISOLATED_STRATEGY, 'H1');
		$this->assertSame($strategy1, $strategy2);

		// different browser configuration use different strategy
		$strategy3 = $this->manager->getSessionStrategy(SessionStrategyManager::ISOLATED_STRATEGY, 'H2');
		$this->assertNotSame($strategy2, $strategy3);

		// different browser configuration break the sequence
		$strategy4 = $this->manager->getSessionStrategy(SessionStrategyManager::ISOLATED_STRATEGY, 'H1');
		$this->assertNotSame($strategy1, $strategy4);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSessionStrategyIsolated()
	{
		$expected = '\\aik099\\PHPUnit\\SessionStrategy\\IsolatedSessionStrategy';
		$this->assertInstanceOf($expected, $this->manager->createSessionStrategy(false));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSessionStrategyShared()
	{
		$expected = '\\aik099\\PHPUnit\\SessionStrategy\\SharedSessionStrategy';
		$this->assertInstanceOf($expected, $this->manager->createSessionStrategy(true));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testCreateSessionStrategyIncorrect()
	{
		$this->manager->createSessionStrategy('wrong');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetDefaultSessionStrategy()
	{
		$expected = '\\aik099\\PHPUnit\\SessionStrategy\\IsolatedSessionStrategy';
		$this->assertInstanceOf($expected, $this->manager->getDefaultSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetDefaultSessionStrategySharing()
	{
		$this->assertSame($this->manager->getDefaultSessionStrategy(), $this->manager->getDefaultSessionStrategy());
	}

}
