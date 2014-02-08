<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\TestSuite;


use aik099\PHPUnit\TestSuite\BrowserTestSuite;
use Mockery as m;
use tests\aik099\PHPUnit\TestCase\EventDispatcherAwareTestCase;

class BrowserTestSuiteTest extends EventDispatcherAwareTestCase
{

	/**
	 * Suite.
	 *
	 * @var BrowserTestSuite
	 */
	private $_suite;

	/**
	 * Creates suite for testing.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_suite = new BrowserTestSuite();
		$this->_suite->setEventDispatcher($this->eventDispatcher);
	}

	/**
	 * Test description.
	 *
	 * @param array  $browser       Browser configuration array.
	 * @param string $expected_name Expected test name.
	 *
	 * @return void
	 * @dataProvider nameFromBrowserDataProvider
	 */
	public function testNameFromBrowser(array $browser, $expected_name)
	{
		$this->assertEquals($expected_name, $this->_suite->nameFromBrowser($browser));
	}

	/**
	 * Returns various browser configurations.
	 *
	 * @return array
	 */
	public function nameFromBrowserDataProvider()
	{
		return array(
			array(array('alias' => 'match'), 'match'),
			array(array('alias' => 'match', 'browserName' => 'no-match'), 'match'),
			array(array('browserName' => 'match'), 'match'),
			array(array('browserName' => 'match', 'name' => 'no-match'), 'match'),
			array(array('name' => 'match'), 'match'),
			array(array(), 'undefined'),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserFromConfiguration()
	{
		$browser = array('name' => 'safari');
		$test = m::mock('PHPUnit_Framework_Test');
		$test->shouldReceive('setBrowserFromConfiguration')->with($browser)->once();

		$this->_suite->addTest($test);

		$this->assertSame($this->_suite, $this->_suite->setBrowserFromConfiguration($browser));
	}

}
