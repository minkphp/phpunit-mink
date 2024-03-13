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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserTestCase;

/**
 * Manages session strategies used across browser tests.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class SessionStrategyManager
{

	/**
	 * Browser configuration used in last executed test.
	 *
	 * @var string
	 */
	protected $lastUsedSessionStrategyHash;

	/**
	 * Session strategy, that was last requested in the browser configuration.
	 *
	 * @var ISessionStrategy
	 */
	protected $lastUsedSessionStrategy;

	/**
	 * Session strategy, that will be used by default.
	 *
	 * @var ISessionStrategy
	 */
	protected $defaultSessionStrategy;

	/**
	 * Session strategy factory.
	 *
	 * @var ISessionStrategyFactory
	 */
	private $_sessionStrategyFactory;

	/**
	 * Creates session strategy manager instance.
	 *
	 * @param ISessionStrategyFactory $session_strategy_factory Session strategy factory.
	 */
	public function __construct(ISessionStrategyFactory $session_strategy_factory)
	{
		$this->_sessionStrategyFactory = $session_strategy_factory;
	}

	/**
	 * Creates default session strategy.
	 *
	 * @return ISessionStrategy
	 */
	public function getDefaultSessionStrategy()
	{
		if ( !$this->defaultSessionStrategy ) {
			$this->defaultSessionStrategy = $this->_sessionStrategyFactory->createStrategy(
				ISessionStrategyFactory::TYPE_ISOLATED
			);
		}

		return $this->defaultSessionStrategy;
	}

	/**
	 * Initializes session strategy using given browser test case.
	 *
	 * @param BrowserConfiguration $browser   Browser configuration.
	 * @param BrowserTestCase      $test_case Test case.
	 *
	 * @return ISessionStrategy
	 */
	public function getSessionStrategy(BrowserConfiguration $browser, BrowserTestCase $test_case)
	{
		/*
		 * This logic creates separate strategy for:
		 * - each browser configuration in BrowserTestCase::$browsers (for isolated strategy)
		 * - each browser configuration in BrowserTestCase::$browsers for each test case class (for shared strategy)
		 */
		$strategy_type = $browser->getSessionStrategy();
		$strategy_hash = $browser->getSessionStrategyHash($test_case);

		if ( $strategy_hash === $this->lastUsedSessionStrategyHash ) {
			return $this->lastUsedSessionStrategy;
		}

		$this->lastUsedSessionStrategy = $this->_sessionStrategyFactory->createStrategy($strategy_type);
		$this->lastUsedSessionStrategyHash = $strategy_hash;

		return $this->lastUsedSessionStrategy;
	}

}
