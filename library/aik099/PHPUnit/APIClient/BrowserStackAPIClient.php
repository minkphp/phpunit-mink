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
	 * Returns information about session.
	 *
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 */
	public function getInfo($session_id)
	{
		$result = $this->execute('GET', 'sessions/' . $session_id . '.json');

		return $result['automation_session'];
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
		$data = array('status' => $test_status ? 'completed' : 'error');
		$result = $this->execute('PUT', 'sessions/' . $session_id . '.json', $data);

		return $result['automation_session'];
	}

	/**
	 * Execute BrowserStack REST API command.
	 *
	 * @param string $request_method HTTP request method.
	 * @param string $url            URL.
	 * @param mixed  $parameters     Parameters.
	 *
	 * @return mixed
	 * @see    http://www.browserstack.com/automate/rest-api
	 */
	protected function execute($request_method, $url, $parameters = null)
	{
		$extra_options = array(
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $this->_apiUsername . ':' . $this->_apiKey,
		);

		$url = 'https://www.browserstack.com/automate/' . $url;

		list($raw_results, ) = $this->_curlService->execute($request_method, $url, $parameters, $extra_options);

		return json_decode($raw_results, true);
	}

}
