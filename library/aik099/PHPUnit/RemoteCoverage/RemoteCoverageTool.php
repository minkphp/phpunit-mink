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


/* Include file from PEAR.
require_once 'File/Iterator/Autoload.php';
require_once 'PHP/CodeCoverage/Autoload.php';*/

class RemoteCoverageTool
{

	const TEST_ID_VARIABLE = 'PHPUNIT_MINK_TEST_ID';

	const DATA_DIRECTORY_VARIABLE = 'PHPUNIT_MINK_COVERAGE_DATA_DIRECTORY';

	/**
	 * Directory for coverage information collection.
	 *
	 * @var string
	 */
	protected $dataDirectory;

	/**
	 * Files, excluded from coverage collection.
	 *
	 * @var array
	 */
	protected $excludedFiles = array(__FILE__);

	/**
	 * Collects & reports coverage information.
	 *
	 * @param string|null $data_directory Directory for coverage information collection.
	 *
	 * @return void
	 */
	public static function init($data_directory = null)
	{
		$coverage_tool = new self($data_directory);
		$mode = isset($_GET['rct_mode']) ? $_GET['rct_mode'] : '';

		if ( $mode == 'output' ) {
			echo $coverage_tool->aggregateCoverageInformation();
		}
		else {
			$coverage_tool->startCollection();
			register_shutdown_function(array($coverage_tool, 'stopCollection'));
		}
	}

	/**
	 * Creates an instance of remove coverage tool.
	 *
	 * @param string|null $data_directory Directory for coverage information collection.
	 */
	public function __construct($data_directory = null)
	{
		if ( !isset($data_directory) ) {
			if ( isset($GLOBALS[self::DATA_DIRECTORY_VARIABLE]) ) {
				$this->dataDirectory = $this->assertDirectory($GLOBALS[self::DATA_DIRECTORY_VARIABLE]);
			}
			else {
				$this->dataDirectory = getcwd();
			}
		}
		else {
			$this->dataDirectory = $this->assertDirectory($data_directory);
		}
	}

	/**
	 * Checks that a directory is valid.
	 *
	 * @param string $directory Directory.
	 *
	 * @throws \InvalidArgumentException When directory is invalid.
	 * @return string
	 */
	protected function assertDirectory($directory)
	{
		if ( !is_string($directory) || !is_dir($directory) || !file_exists($directory) ) {
			throw new \InvalidArgumentException('Directory "' . $directory . '" is invalid');
		}

		return $directory;
	}

	/**
	 * Excludes a file from coverage.
	 *
	 * @param string $file Path to file, that needs to be excluded.
	 *
	 * @return void
	 */
	public function excludeFile($file)
	{
		$this->excludedFiles[] = $file;
	}

	/**
	 * Starts coverage information collection.
	 *
	 * @return void
	 */
	public function startCollection()
	{
		if ( !$this->enabled() ) {
			return;
		}

		xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
	}

	/**
	 * Stops coverage information collection.
	 *
	 * @return void
	 */
	public function stopCollection()
	{
		if ( !$this->enabled() ) {
			return;
		}

		$data = xdebug_get_code_coverage();
		xdebug_stop_code_coverage();

		foreach ( $this->excludedFiles as $file ) {
			unset($data[$file]);
		}

		$unique_id = md5(uniqid(rand(), true));
		file_put_contents(
			$name = $this->getStorageLocationPrefix() . '.' . $unique_id . '.' . $_COOKIE[self::TEST_ID_VARIABLE],
			serialize($data)
		);
	}

	/**
	 * Determines if coverage information collection can be started.
	 *
	 * @return string
	 * @throws \RuntimeException When Xdebug extension not enabled.
	 */
	protected function enabled()
	{
		if ( !extension_loaded('xdebug') ) {
			throw new \RuntimeException('Xdebug extension must be enabled for coverage collection');
		}

		return isset($_COOKIE[self::TEST_ID_VARIABLE]) && !isset($_GET[self::TEST_ID_VARIABLE]);
	}

	/**
	 * Returns name of the file, where coverage information will be stored.
	 *
	 * @return string
	 */
	protected function getStorageLocationPrefix()
	{
		return $this->dataDirectory . DIRECTORY_SEPARATOR . md5($_SERVER['SCRIPT_FILENAME']);
	}

	/**
	 * Aggregates previously collected coverage information.
	 *
	 * @return string
	 */
	public function aggregateCoverageInformation()
	{
		if ( !isset($_GET[self::TEST_ID_VARIABLE]) ) {
			return '';
		}

		$coverage = array();
		$filter = new \PHP_CodeCoverage_Filter();

		foreach ( $this->getDataDirectoryFiles() as $data_directory_file ) {
			$raw_coverage_data = unserialize(file_get_contents($data_directory_file));

			foreach ( $raw_coverage_data as $file => $lines ) {
				if ( !$filter->isFile($file) ) {
					continue;
				}

				if ( !isset($coverage[$file]) ) {
					$coverage[$file] = array('md5' => md5_file($file), 'coverage' => $lines);
				}
				else {
					foreach ( $lines as $line => $flag ) {
						if ( !isset($coverage[$file]['coverage'][$line])
							|| $flag > $coverage[$file]['coverage'][$line]
						) {
							$coverage[$file]['coverage'][$line] = $flag;
						}
					}
				}
			}
		}

		return serialize($coverage);
	}

	/**
	 * Returns contents of data directory for a current test.
	 *
	 * @return array
	 */
	protected function getDataDirectoryFiles()
	{
		$facade = new \File_Iterator_Facade();

		return $facade->getFilesAsArray($this->dataDirectory, $_GET[self::TEST_ID_VARIABLE]);
	}

}
