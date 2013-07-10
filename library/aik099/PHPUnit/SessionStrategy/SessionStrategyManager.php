<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\SessionStrategy;


/**
 * Manages session strategies used across browser tests.
 *
 * @method \Mockery\Expectation shouldReceive
 */
class SessionStrategyManager
{

	/**
	 * Strategy, that create new session for each test in a test case.
	 */
	const ISOLATED_STRATEGY = 'isolated';

	/**
	 * Strategy, that allows to share session across all tests in a single test case.
	 */
	const SHARED_STRATEGY = 'shared';

	/**
	 * Browser configuration used in last executed test.
	 *
	 * @var string
	 */
	protected $lastUsedSessionStrategyHash;

	/**
	 * Session strategy, that was requested in browser configuration.
	 *
	 * @var ISessionStrategy[]
	 */
	protected $sessionStrategiesInUse = array();

	/**
	 * Session strategy, that will be used by default.
	 *
	 * @var ISessionStrategy
	 */
	protected $defaultSessionStrategy;

	/**
	 * No direct instantiation.
	 */
	protected function __construct()
	{

	}

	/**
	 * Returns instance of strategy manager.
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		static $instance = null;

		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Initializes session strategy using given browser test case.
	 *
	 * @param string $session_strategy      Session strategy.
	 * @param string $session_strategy_hash Session strategy hash.
	 *
	 * @return ISessionStrategy
	 */
	public function getSessionStrategy($session_strategy, $session_strategy_hash)
	{
		// This logic creates separate strategy for:
		//  - each browser configuration in BrowserTestCase::$browsers (for isolated strategy)
		//  - each browser configuration in BrowserTestCase::$browsers for each test case class (for shared strategy)

		if ( $session_strategy_hash != $this->lastUsedSessionStrategyHash ) {
			switch ( $session_strategy ) {
				case self::ISOLATED_STRATEGY:
					$this->sessionStrategiesInUse[$session_strategy_hash] = $this->createSessionStrategy(false);
					break;

				case self::SHARED_STRATEGY:
					$this->sessionStrategiesInUse[$session_strategy_hash] = $this->createSessionStrategy(true);
					break;
			}
		}

		$this->lastUsedSessionStrategyHash = $session_strategy_hash;

		return $this->sessionStrategiesInUse[$session_strategy_hash];
	}

	/**
	 * Creates specified session strategy.
	 *
	 * @param boolean $share_session Share or not the session.
	 *
	 * @return ISessionStrategy
	 * @throws \InvalidArgumentException When incorrect argument is given.
	 */
	public function createSessionStrategy($share_session)
	{
		if ( !is_bool($share_session) ) {
			throw new \InvalidArgumentException('The shared session support can only be switched on or off.');
		}

		if ( $share_session ) {
			return new SharedSessionStrategy(new IsolatedSessionStrategy());
		}

		return new IsolatedSessionStrategy();
	}

	/**
	 * Creates default session strategy.
	 *
	 * @return ISessionStrategy
	 */
	public function getDefaultSessionStrategy()
	{
		if ( !$this->defaultSessionStrategy ) {
			$this->defaultSessionStrategy = $this->createSessionStrategy(false);
		}

		return $this->defaultSessionStrategy;
	}

}
