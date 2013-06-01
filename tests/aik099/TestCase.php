<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099;


use aik099\PHPUnit\BrowserTestCase;

class TestCase extends BrowserTestCase
{

	/**
	 * Default browser alias to use in tests.
	 */
	const DEFAULT_BROWSER_ALIAS = 'web_fixture';

	/**
	 * Browsers to use for tests.
	 *
	 * @var array
	 * @access public
	 */
	public static $browsers = array(
		array('alias' => self::DEFAULT_BROWSER_ALIAS),
	);

	/**
	 * Constructs a test case with the given name.
	 *
	 * @param string $name     Test case name.
	 * @param array  $data     Data.
	 * @param string $dataName Data name.
	 *
	 * @access public
	 */
	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->coverageScriptUrl = $_SERVER['WEB_FIXTURES_URL'] . '/PHPUnit/WebFixtures/phpunit_coverage.php';
	}

	/**
	 * Gets browser configuration aliases.
	 *
	 * Allows to decouple actual test server connection details from test cases.
	 *
	 * @return array
	 * @access protected
	 */
	protected function getBrowserAliases()
	{
		return array(
			'web_fixture' => array(
				'host' => $_SERVER['WEB_FIXTURES_HOST'],
				'port' => (int)$_SERVER['WEB_FIXTURES_PORT'],
				'browserName' => $_SERVER['WEB_FIXTURES_BROWSER'],
				'baseUrl' => $_SERVER['WEB_FIXTURES_URL'],
			),

			'web_fixture_sauce' => array(
				'sauce' => array(
					'username' => $_SERVER['WEB_FIXTURES_SAUCE_USERNAME'],
					'api_key' => $_SERVER['WEB_FIXTURES_SAUCE_API_KEY'],
				),
				'baseUrl' => $_SERVER['WEB_FIXTURES_URL'],
			),

			'web_fixture_shared' => array(
				'alias' => self::DEFAULT_BROWSER_ALIAS,
				'sessionStrategy' => self::SESSION_STRATEGY_SHARED,
			),
		);
	}

}