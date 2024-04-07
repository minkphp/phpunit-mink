<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Integration;


use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;

class SharedSessionStrategyTest extends BrowserStackAwareTestCase
{

	use AssertStringContains;

	/**
	 * Browser list to be used in tests.
	 *
	 * @var array
	 */
	public static $browsers = array(
		array(
			'alias' => 'default',
			'sessionStrategy' => 'shared',
			'desiredCapabilities' => array('build' => BUILD_NAME, 'name' => 'SharedSessionStrategyTest'),
		),
	);

	/**
	 * @large
	 */
	public function testOpensPage()
	{
		$session = $this->getSession();
		$session->visit('https://www.google.com');

		$this->assertTrue(true);
	}

	/**
	 * @large
	 * @depends testOpensPage
	 */
	public function testUsesOpenedPage()
	{
		$session = $this->getSession();
		$url = $session->getCurrentUrl();

		$this->assertStringContainsString('https://www.google.com', $url);
	}

	public function testOpensPopups()
	{
		$session = $this->getSession();
		$session->visit('https://the-internet.herokuapp.com/windows');

		$page = $session->getPage();
		$page->clickLink('Click Here');
		$page->clickLink('Click Here');

		$this->assertCount(3, $session->getWindowNames()); // Main window + 2 popups.
	}

	/**
	 * @depends testOpensPopups
	 */
	public function testNoPopupsBeforeTest()
	{
		$session = $this->getSession();
		$this->assertEquals('https://the-internet.herokuapp.com/windows', $session->getCurrentUrl());

		$this->assertCount(1, $session->getWindowNames()); // Main window.
	}

}
