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

class TestEndedEvent extends TestEvent
{

	/**
	 * Test result.
	 *
	 * @var \PHPUnit_Framework_TestResult
	 */
	private $_testResult;

	/**
	 * Remembers the exception which caused test to fail.
	 *
	 * @param BrowserTestCase               $test_case   Test case.
	 * @param \PHPUnit_Framework_TestResult $test_result Test result.
	 * @param Session                       $session     Session.
	 */
	public function __construct(
		BrowserTestCase $test_case,
		\PHPUnit_Framework_TestResult $test_result,
		Session $session = null
	) {
		parent::__construct($test_case, $session);
		$this->_testResult = $test_result;
	}

	/**
	 * Returns test result.
	 *
	 * @return \PHPUnit_Framework_TestResult
	 */
	public function getTestResult()
	{
		return $this->_testResult;
	}

}
