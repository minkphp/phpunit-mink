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
use aik099\PHPUnit\Session\SessionStrategyFactory;
use Mockery as m;
use tests\aik099\PHPUnit\AbstractTestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class SessionStrategyFactoryTest extends AbstractTestCase
{

	use ExpectException;

	/**
	 * Session factory.
	 *
	 * @var SessionStrategyFactory
	 */
	private $_factory;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_factory = new SessionStrategyFactory();
	}

	public function testRegisterSuccess()
	{
		$session_strategy = m::mock(ISessionStrategy::class);
		$this->_factory->register('strategy-type', $session_strategy);

		$this->assertInstanceOf(ISessionStrategy::class, $this->_factory->createStrategy('strategy-type'));
	}

	public function testRegisterFailure()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Session strategy with type "strategy-type" is already registered');

		$session_strategy = m::mock(ISessionStrategy::class);
		$this->_factory->register('strategy-type', $session_strategy);
		$this->_factory->register('strategy-type', $session_strategy);
	}

	public function testCreateStrategyFailure()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Session strategy type "wrong" not registered');

		$this->_factory->createStrategy('wrong');
	}

}
