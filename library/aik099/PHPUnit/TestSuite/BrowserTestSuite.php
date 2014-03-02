<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\TestSuite;


use aik099\PHPUnit\BrowserTestCase;

/**
 * Test Suite class for a set of tests from a single Test Case Class executed with a particular browser.
 */
class BrowserTestSuite extends AbstractTestSuite
{

	/**
	 * Generates suite name by the browser configuration.
	 *
	 * @param array $browser Browser configuration.
	 *
	 * @return string
	 */
	public function nameFromBrowser(array $browser)
	{
		$try_settings = array('alias', 'browserName', 'name');

		foreach ( $try_settings as $try_setting ) {
			if ( isset($browser[$try_setting]) ) {
				return $browser[$try_setting];
			}
		}

		return 'undefined';
	}

	/**
	 * Sets given browser to be used in each underlying test cases and test suites.
	 *
	 * @param array $browser Browser configuration.
	 *
	 * @return self
	 */
	public function setBrowserFromConfiguration(array $browser)
	{
		/* @var $test BrowserTestCase */
		foreach ( $this->tests() as $test ) {
			$test->setBrowserFromConfiguration($browser);
		}

		return $this;
	}

}
