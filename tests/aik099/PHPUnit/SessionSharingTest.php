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

/**
 * Testing, that session can be shared across all tests in a test case.
 */
class SessionSharingTest extends TestCase
{

	/**
	 * Browsers to use for tests.
	 *
	 * @var array
	 * @access public
	 */
	public static $browsers = array(
		array('alias' => 'web_fixture_shared'),
	);

	/**
	 * Tests, that cookie can be set.
	 *
	 * @return string
	 * @access public
	 */
	public function testSetCookie()
	{
		$session = $this->getSession();
		$session->visit($this->getBaseUrl() . '/PHPUnit/WebFixtures/?mode=set');

		$cookie_value = md5(microtime(true));
		$session->setCookie('phpunit_cookie_test', $cookie_value);

		$this->assertEquals($cookie_value, $session->getCookie('phpunit_cookie_test'));

		return $cookie_value;
	}

	/**
	 * Tests, that cookie set in one test is available in another test.
	 *
	 * @param string $cookie_value Cookie value.
	 *
	 * @return void
	 * @depends testSetCookie
	 */
	public function testGetCookie($cookie_value)
	{
		$session = $this->getSession();
		$session->visit($this->getBaseUrl() . '/PHPUnit/WebFixtures/?mode=get');

		$this->assertEquals($cookie_value, $session->getCookie('phpunit_cookie_test'));
	}

}