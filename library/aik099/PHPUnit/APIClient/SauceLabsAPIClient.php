<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\APIClient;


use WebDriver\SauceLabs\SauceRest;

class SauceLabsAPIClient implements IAPIClient
{

	/**
	 * API client for SauceLabs service.
	 *
	 * @var SauceRest
	 */
	private $_sauceRest;

	/**
	 * Creates instance of API client.
	 *
	 * @param SauceRest $sauce_rest SauceRest client.
	 */
	public function __construct(SauceRest $sauce_rest)
	{
		$this->_sauceRest = $sauce_rest;
	}

	/**
	 * Returns information about session.
	 *
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 */
	public function getInfo($session_id)
	{
		return $this->_sauceRest->getJob($session_id);
	}

	/**
	 * Update status of the test, that was executed in the given session.
	 *
	 * @param string  $session_id  Session ID.
	 * @param boolean $test_status Test status.
	 *
	 * @return array
	 */
	public function updateStatus($session_id, $test_status)
	{
		return $this->_sauceRest->updateJob($session_id, array('passed' => $test_status));
	}

}
