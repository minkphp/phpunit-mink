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
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use Behat\Mink\Session;
use aik099\PHPUnit\BrowserTestCase;

abstract class AbstractSessionStrategyTestCase extends AbstractTestCase
{

	const BROWSER_CLASS = BrowserConfiguration::class;

	const SESSION_CLASS = Session::class;

	const TEST_CASE_CLASS = BrowserTestCase::class;

	/**
	 * Session strategy.
	 *
	 * @var ISessionStrategy
	 */
	protected $strategy;

	public function testUnknownSessionFreshnessStateUntilItsStarted()
	{
		$this->assertNull($this->strategy->isFreshSession());
	}

}
