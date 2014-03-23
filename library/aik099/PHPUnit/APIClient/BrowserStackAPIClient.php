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


use WebDriver\Service\CurlServiceInterface;

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
	 * Curl service.
	 *
	 * @var CurlServiceInterface
	 */
	private $_curlService;

	/**
	 * Creates instance of API client.
	 *
	 * @param string               $api_username API Username.
	 * @param string               $api_key      API Password.
	 * @param CurlServiceInterface $curl_service Curl service.
	 */
	public function __construct($api_username, $api_key, CurlServiceInterface $curl_service)
	{
		$this->_apiUsername = $api_username;
		$this->_apiKey = $api_key;
		$this->_curlService = $curl_service;
	}

	/**
	 * Update status of the test, that was executed in the given session.
	 *
	 * @param string  $session_id  Session ID.
	 * @param boolean $test_status Test status.
	 *
	 * @return boolean
	 */
	public function updateStatus($session_id, $test_status)
	{
		$data = array('status' => $test_status ? 'completed' : 'error');

		return $this->execute('PUT', 'sessions/' . $session_id . '.json', json_encode($data));
	}

	/**
	 * Execute BrowserStack REST API command.
	 *
	 * @param string $requestMethod HTTP request method.
	 * @param string $url           URL.
	 * @param mixed  $parameters    Parameters.
	 *
	 * @return mixed
	 * @see    http://www.browserstack.com/automate/rest-api
	 */
	protected function execute($requestMethod, $url, $parameters = null)
	{
		$extraOptions = array(
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $this->_apiUsername . ':' . $this->_apiKey,
		);

		$url = 'https://www.browserstack.com/automate/' . $url;

		list($rawResults, $info) = $this->_curlService->execute($requestMethod, $url, $parameters, $extraOptions);

		return json_decode($rawResults, true);
	}

}
