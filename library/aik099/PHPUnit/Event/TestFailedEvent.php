<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\Event;


use aik099\PHPUnit\BrowserTestCase;
use Behat\Mink\Session;

class TestFailedEvent extends TestEvent
{

	/**
	 * Exception.
	 *
	 * @var \Exception
	 */
	private $_exception;

	/**
	 * Remembers the exception which caused test to fail.
	 *
	 * @param \Exception|\Throwable $e         Exception.
	 * @param BrowserTestCase       $test_case Test case.
	 * @param Session               $session   Session.
	 */
	public function __construct($e, BrowserTestCase $test_case, Session $session = null)
	{
		parent::__construct($test_case, $session);
		$this->_exception = $e;
	}

	/**
	 * Returns exception, that caused test to fail.
	 *
	 * @return \Exception|\Throwable
	 */
	public function getException()
	{
		return $this->_exception;
	}

}
