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


class BrowserStackAPIClient implements IAPIClient
{

	/**
	 * API username.
	 *
	 * @var string
	 */
	private $_apiUsername;

	/**
	 * API key.
	 *
	 * @var string
	 */
	private $_apiKey;

	/**
	 * Creates instance of API client.
	 *
	 * @param string $api_username API Username.
	 * @param string $api_key      API Password.
	 */
	public function __construct($api_username, $api_key)
	{
		$this->_apiUsername = $api_username;
		$this->_apiKey = $api_key;
	}

	/**
	 * Update status of the test, that was executed in the given session.
	 *
	 * @param string  $session_id  Session ID.
	 * @param boolean $test_status Test status.
	 *
	 * @return void
	 */
	public function updateStatus($session_id, $test_status)
	{
		// TODO: to implement
	}

}
