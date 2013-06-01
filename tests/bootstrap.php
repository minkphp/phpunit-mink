<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

define('FULL_PATH', realpath(__DIR__ . '/..'));

$vendor_path = FULL_PATH . '/vendor';

if ( !is_dir($vendor_path) ) {
	echo 'Install dependencies first' . PHP_EOL;
	exit(1);
}

require_once($vendor_path . '/autoload.php');

$auto_loader = new \Composer\Autoload\ClassLoader();
$auto_loader->add("aik099\\", FULL_PATH . '/library/');
$auto_loader->add("tests\\aik099\\", FULL_PATH . '/');
$auto_loader->register();