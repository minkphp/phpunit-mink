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
use aik099\PHPUnit\Framework\TestResult;

class TestEndedEvent extends TestEvent
{

	/**
	 * Test result.
	 *
	 * @var TestResult
	 */
	private $_testResult;

	/**
	 * Remembers the exception which caused test to fail.
	 *
	 * @param BrowserTestCase $test_case   Test case.
	 * @param TestResult      $test_result Test result.
	 * @param Session         $session     Session.
	 */
	public function __construct(
		BrowserTestCase $test_case,
		TestResult $test_result,
		Session $session = null
	) {
		parent::__construct($test_case, $session);
		$this->_testResult = $test_result;
	}

	/**
	 * Returns test result.
	 *
	 * @return TestResult
	 */
	public function getTestResult()
	{
		return $this->_testResult;
	}

}
