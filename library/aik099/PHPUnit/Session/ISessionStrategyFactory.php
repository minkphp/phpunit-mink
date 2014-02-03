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
 * Specifies how to create Session objects for running tests.
 *
 * @method \Mockery\Expectation shouldReceive
 */
interface ISessionStrategyFactory
{

	/**
	 * Creates specified session strategy.
	 *
	 * @param string $strategy_type Session strategy type.
	 *
	 * @return ISessionStrategy
	 */
	public function createStrategy($strategy_type);

}
