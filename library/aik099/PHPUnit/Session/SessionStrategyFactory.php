<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\Session;


/**
 * Produces sessions.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class SessionStrategyFactory implements ISessionStrategyFactory
{

	/**
	 * Session strategies.
	 *
	 * @var ISessionStrategy[]
	 */
	protected $sessionStrategies = array();

	/**
	 * Registers a browser configuration.
	 *
	 * @param string           $strategy_type    Session strategy type.
	 * @param ISessionStrategy $session_strategy Session strategy.
	 *
	 * @return void
	 * @throws \InvalidArgumentException When session strategy is already registered.
	 */
	public function register($strategy_type, ISessionStrategy $session_strategy)
	{
		if ( isset($this->sessionStrategies[$strategy_type]) ) {
			throw new \InvalidArgumentException(
				'Session strategy with type "' . $strategy_type . '" is already registered'
			);
		}

		$this->sessionStrategies[$strategy_type] = $session_strategy;
	}

	/**
	 * Creates specified session strategy.
	 *
	 * @param string $strategy_type Session strategy type.
	 *
	 * @return ISessionStrategy
	 * @throws \InvalidArgumentException When session strategy type is invalid.
	 */
	public function createStrategy($strategy_type)
	{
		if ( !isset($this->sessionStrategies[$strategy_type]) ) {
			throw new \InvalidArgumentException('Session strategy type "' . $strategy_type . '" not registered');
		}

		return clone $this->sessionStrategies[$strategy_type];
	}

}
