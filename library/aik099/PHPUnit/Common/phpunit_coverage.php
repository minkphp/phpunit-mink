<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

require_once 'File/Iterator/Autoload.php';
require_once 'PHP/CodeCoverage/Autoload.php';

// Set this to the directory that contains the code coverage files.
// It defaults to getcwd(). If you have configured a different directory
// in prepend.php, you need to configure the same directory here.

if ( !isset($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY']) ) {
	$GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] = getcwd();
}

if ( isset($_GET['PHPUNIT_SELENIUM_TEST_ID']) ) {
	$facade = new File_Iterator_Facade();

	$files = $facade->getFilesAsArray(
		$GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'],
		$_GET['PHPUNIT_SELENIUM_TEST_ID']
	);

	$coverage = array();

	foreach ($files as $file) {
		$data = unserialize(file_get_contents($file));
		unlink($file);
		unset($file);
		$filter = new PHP_CodeCoverage_Filter();

		foreach ($data as $file => $lines) {
			if ( $filter->isFile($file) ) {
				if ( !isset($coverage[$file]) ) {
					$coverage[$file] = array('md5' => md5_file($file), 'coverage' => $lines);
				}
				else {
					foreach ($lines as $line => $flag) {
						if ( !isset($coverage[$file]['coverage'][$line]) ||
							$flag > $coverage[$file]['coverage'][$line]
						) {
							$coverage[$file]['coverage'][$line] = $flag;
						}
					}
				}
			}
		}
	}

	print serialize($coverage);
}