<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

if ( isset($_COOKIE['PHPUNIT_SELENIUM_TEST_ID']) &&
	!isset($_GET['PHPUNIT_SELENIUM_TEST_ID']) &&
	extension_loaded('xdebug')
) {
	$GLOBALS['PHPUNIT_FILTERED_FILES'][] = __FILE__;

	$data = xdebug_get_code_coverage();
	xdebug_stop_code_coverage();

	foreach ($GLOBALS['PHPUNIT_FILTERED_FILES'] as $file) {
		unset($data[$file]);
	}

	if ( is_string($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY']) &&
		is_dir($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'])
	) {
		$file = $GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] .
			DIRECTORY_SEPARATOR . md5($_SERVER['SCRIPT_FILENAME']);
	}
	else {
		$file = $_SERVER['SCRIPT_FILENAME'];
	}

	file_put_contents(
		$name = $file . '.' . md5(uniqid(rand(), true)) . '.' . $_COOKIE['PHPUNIT_SELENIUM_TEST_ID'],
		serialize($data)
	);
}