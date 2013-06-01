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
 * Testing, that 2 different test cases have their own sharing sessions.
 */
class SessionSharingBorderTest extends TestCase
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
	 * Tests, that cookie set in one test is available in another test.
	 *
	 * @return void
	 */
	public function testGetCookie()
	{
		$session = $this->getSession();
		$session->visit($this->getBaseUrl() . '/PHPUnit/WebFixtures/?mode=get');

		$this->assertNull($session->getCookie('phpunit_cookie_test'));
	}

}