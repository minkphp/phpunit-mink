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
use tests\aik099\PHPUnit\AbstractTestCase;

class SessionStrategyTestCase extends AbstractTestCase
{

	const BROWSER_CLASS = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

	const SESSION_CLASS = '\\Behat\\Mink\\Session';

	const TEST_CASE_CLASS = 'aik099\\PHPUnit\\BrowserTestCase';

	/**
	 * Session strategy.
	 *
	 * @var ISessionStrategy
	 */
	protected $strategy;

}
