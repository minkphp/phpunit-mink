<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit;


use tests\aik099\TestCase;

class BrowserConfigurationTest extends TestCase
{

	/**
	 * Browsers to use for tests.
	 *
	 * @var array
	 * @access public
	 */
	public static $browsers = array();

	/**
	 * Tries out various browser configurations.
	 *
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		switch ( $this->getName(false) ) {
			case 'testAlias':
				$this->setupSpecificBrowser(array('alias' => self::DEFAULT_BROWSER_ALIAS));
				break;

			case 'testIndependent':
				$this->setBrowser('firefox')->setBaseUrl($_SERVER['WEB_FIXTURES_URL'] . '/PHPUnit/WebFixtures/?base=1');
				$this->setHost($_SERVER['WEB_FIXTURES_HOST']);
				break;
		}

		parent::setUp();
	}

	/**
	 * Tests, that browser can be configured from an alias.
	 *
	 * @return void
	 * @access public
	 */
	public function testAlias()
	{
		$session = $this->getSession();
		$session->visit($this->getBaseUrl());

		$this->assertTrue($session->getPage()->hasContent('Test Suite'));
	}

	/**
	 * Tests, that browser can be configured independently.
	 *
	 * @return void
	 * @access public
	 */
	public function testIndependent()
	{
		$session = $this->getSession();
		$session->visit($this->getBaseUrl());

		$this->assertTrue(strpos($session->getCurrentUrl(), '?base=1') !== false);
	}

}