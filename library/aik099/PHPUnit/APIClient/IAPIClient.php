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


/**
 * Interface that each API client must implement.
 */
interface IAPIClient
{

	/**
	 * Returns information about session.
	 *
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 */
	public function getInfo($session_id);

	/**
	 * Update status of the test, that was executed in the given session.
	 *
	 * @param string  $session_id  Session ID.
	 * @param boolean $test_status Test status.
	 *
	 * @return array
	 */
	public function updateStatus($session_id, $test_status);

}
