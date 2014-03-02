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


use aik099\PHPUnit\Session\ISessionStrategy;
use aik099\PHPUnit\Session\ISessionStrategyFactory;
use aik099\PHPUnit\Session\SessionStrategyFactory;
use aik099\PHPUnit\Session\SessionStrategyManager;
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
	 * Session strategy factory.
	 *
	 * @var SessionStrategyFactory
	 */
	protected $factory;

	/**
	 * Creates session strategy manager to use for tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->factory = m::mock('aik099\\PHPUnit\\Session\\ISessionStrategyFactory');
		$this->manager = new SessionStrategyManager($this->factory);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetDefaultSessionStrategy()
	{
		$expected = 'OK';
		$this->factory->shouldReceive('createStrategy')->andReturn($expected);

		$this->assertEquals($expected, $this->manager->getDefaultSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetDefaultSessionStrategySharing()
	{
		$this->factory->shouldReceive('createStrategy')->andReturn('OK');

		$this->assertEquals($this->manager->getDefaultSessionStrategy(), $this->manager->getDefaultSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionStrategySharing()
	{
		$this->factory
			->shouldReceive('createStrategy')
			->andReturnUsing(function () {
				return m::mock('aik099\\PHPUnit\\Session\\ISessionStrategy');
			});

		// Sequential identical browser configurations share strategy.
		$strategy1 = $this->_getStrategy(ISessionStrategyFactory::TYPE_ISOLATED, 'H1');
		$strategy2 = $this->_getStrategy(ISessionStrategyFactory::TYPE_ISOLATED, 'H1');
		$this->assertSame($strategy1, $strategy2);

		// Different browser configuration use different strategy.
		$strategy3 = $this->_getStrategy(ISessionStrategyFactory::TYPE_ISOLATED, 'H2');
		$this->assertNotSame($strategy2, $strategy3);

		// Different browser configuration break the sequence.
		$strategy4 = $this->_getStrategy(ISessionStrategyFactory::TYPE_ISOLATED, 'H1');
		$this->assertNotSame($strategy1, $strategy4);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionStrategyIsolated()
	{
		$expected = '\\aik099\\PHPUnit\\Session\\IsolatedSessionStrategy';
		$this->factory->shouldReceive('createStrategy')->andReturn(m::mock($expected));

		$this->assertInstanceOf($expected, $this->_getStrategy(ISessionStrategyFactory::TYPE_ISOLATED, 'IS1'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionStrategyShared()
	{
		$expected = '\\aik099\\PHPUnit\\Session\\SharedSessionStrategy';
		$this->factory->shouldReceive('createStrategy')->andReturn(m::mock($expected));

		$this->assertInstanceOf($expected, $this->_getStrategy(ISessionStrategyFactory::TYPE_SHARED, 'SH1'));
	}

	/**
	 * Creates browser configuration.
	 *
	 * @param string $strategy_type Strategy type.
	 * @param string $strategy_hash Strategy hash.
	 *
	 * @return ISessionStrategy
	 */
	private function _getStrategy($strategy_type, $strategy_hash)
	{
		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser->shouldReceive('getSessionStrategy')->once()->andReturn($strategy_type);
		$browser->shouldReceive('getSessionStrategyHash')->once()->andReturn($strategy_hash);

		return $this->manager->getSessionStrategy($browser);
	}

}
