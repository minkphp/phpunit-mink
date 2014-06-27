<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\RemoteCoverage;


/**
 * Class collects remove code coverage information and maps patch from remote to local server.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class RemoteCoverageHelper
{

	/**
	 * Remote URL.
	 *
	 * @var RemoteUrl
	 */
	private $_remoteUrl;

	/**
	 * Creates an instance of remote coverage class.
	 *
	 * @param RemoteUrl $remote_url Remote URL.
	 */
	public function __construct(RemoteUrl $remote_url)
	{
		$this->_remoteUrl = $remote_url;
	}

	/**
	 * Retrieves remote coverage information.
	 *
	 * @param string $coverage_script_url Coverage script irl.
	 * @param string $test_id             Test ID.
	 *
	 * @throws \RuntimeException Broken code coverage retrieved.
	 * @return array
	 */
	public function get($coverage_script_url, $test_id)
	{
		$url = $this->createUrl($coverage_script_url, $test_id);
		$buffer = $this->_remoteUrl->getPageContent($url);

		if ( $buffer !== false ) {
			$coverage_data = unserialize($buffer);

			if ( is_array($coverage_data) ) {
				return $this->matchLocalAndRemotePaths($coverage_data);
			}

			throw new \RuntimeException('Empty or invalid code coverage data received from url "' . $url . '"');
		}

		return array();
	}

	/**
	 * Returns url for remote code coverage collection.
	 *
	 * @param string $coverage_script_url Coverage script irl.
	 * @param string $test_id             Test ID.
	 *
	 * @return string
	 * @throws \InvalidArgumentException When empty coverage script url given.
	 */
	protected function createUrl($coverage_script_url, $test_id)
	{
		if ( !$coverage_script_url || !$test_id ) {
			throw new \InvalidArgumentException('Both Coverage script URL and Test ID must be filled in');
		}

		$query_string = array(
			'rct_mode' => 'output',
			RemoteCoverageTool::TEST_ID_VARIABLE => $test_id,
		);

		$url = $coverage_script_url;
		$url .= strpos($url, '?') === false ? '?' : '&';
		$url .= http_build_query($query_string);

		return $url;
	}

	/**
	 * Returns only files from remote server, that are matching files on test machine.
	 *
	 * @param array $coverage Remote coverage information.
	 *
	 * @return array
	 * @author Mattis Stordalen Flister <mattis@xait.no>
	 */
	protected function matchLocalAndRemotePaths(array $coverage)
	{
		$coverage_with_local_paths = array();

		foreach ( $coverage as $original_remote_path => $data ) {
			$remote_path = $original_remote_path;
			$separator = $this->findDirectorySeparator($remote_path);

			while ( !($local_path = stream_resolve_include_path($remote_path)) &&
				strpos($remote_path, $separator) !== false ) {
				$remote_path = substr($remote_path, strpos($remote_path, $separator) + 1);
			}

			if ( $local_path && md5_file($local_path) == $data['md5'] ) {
				$coverage_with_local_paths[$local_path] = $data['coverage'];
			}
		}

		return $coverage_with_local_paths;
	}

	/**
	 * Returns path separator in given path.
	 *
	 * @param string $path Path to file.
	 *
	 * @return string
	 * @author Mattis Stordalen Flister <mattis@xait.no>
	 */
	protected function findDirectorySeparator($path)
	{
		if ( strpos($path, '/') !== false ) {
			return '/';
		}

		return '\\';
	}

}
