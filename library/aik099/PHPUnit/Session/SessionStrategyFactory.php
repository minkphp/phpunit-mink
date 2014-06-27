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


use aik099\PHPUnit\IApplicationAware;
use aik099\PHPUnit\Application;

/**
 * Produces sessions.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class SessionStrategyFactory implements ISessionStrategyFactory, IApplicationAware
{

	/**
	 * Application.
	 *
	 * @var Application
	 */
	protected $application;

	/**
	 * Sets application.
	 *
	 * @param Application $application The application.
	 *
	 * @return void
	 */
	public function setApplication(Application $application)
	{
		$this->application = $application;
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
		if ( $strategy_type == ISessionStrategyFactory::TYPE_ISOLATED ) {
			return $this->application->getObject('isolated_session_strategy');
		}
		elseif ( $strategy_type == ISessionStrategyFactory::TYPE_SHARED ) {
			return $this->application->getObject('shared_session_strategy');
		}

		throw new \InvalidArgumentException('Incorrect session strategy type');
	}

}
