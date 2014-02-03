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
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SessionStrategyTestCase extends \PHPUnit_Framework_TestCase
{

	const BROWSER_CLASS = '\\aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

	const SESSION_CLASS = '\\Behat\\Mink\\Session';

	const TEST_CASE_CLASS = 'aik099\\PHPUnit\\BrowserTestCase';

	/**
	 * Event dispatcher.
	 *
	 * @var EventDispatcher
	 */
	protected $eventDispatcher;

	/**
	 * Session strategy.
	 *
	 * @var ISessionStrategy
	 */
	protected $strategy;

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->eventDispatcher = new EventDispatcher();
		$this->strategy->setEventDispatcher($this->eventDispatcher);
	}

}
