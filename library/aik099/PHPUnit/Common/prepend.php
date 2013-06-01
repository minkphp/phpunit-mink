<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

// By default the code coverage files are written to the same directory
// that contains the covered sourcecode files. Use this setting to change
// the default behaviour and set a specific directory to write the files to.
// If you change the default setting, please make sure to also configure
// the same directory in phpunit_coverage.php. Also note that the webserver
// needs write access to the directory.

if ( !isset($GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY']) ) {
	$GLOBALS['PHPUNIT_COVERAGE_DATA_DIRECTORY'] = false;
}

if ( isset($_COOKIE['PHPUNIT_SELENIUM_TEST_ID']) &&
	!isset($_GET['PHPUNIT_SELENIUM_TEST_ID']) &&
	extension_loaded('xdebug')
) {
	$GLOBALS['PHPUNIT_FILTERED_FILES'] = array(__FILE__);

	xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
}
