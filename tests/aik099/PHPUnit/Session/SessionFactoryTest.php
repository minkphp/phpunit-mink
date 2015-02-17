<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Session;


use aik099\PHPUnit\Session\SessionFactory;
use Mockery as m;

class SessionFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Session factory.
	 *
	 * @var SessionFactory
	 */
	private $_factory;

	/**
	 * Creates session strategy.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_factory = new SessionFactory();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSession()
	{
		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser->shouldReceive('getDesiredCapabilities')->once()->andReturn(array());
		$browser->shouldReceive('getBrowserName')->once()->andReturn('');
		$browser->shouldReceive('getTimeout')->once()->andReturn(0);
		$browser->shouldReceive('getHost')->once()->andReturn('');
		$browser->shouldReceive('getPort')->once()->andReturn(0);
		$browser->shouldReceive('getDriver')->once()->andReturn('selenium2');

		$session = $this->_factory->createSession($browser);

		$this->assertInstanceOf('Behat\\Mink\\Session', $session);
		$this->assertInstanceOf('Behat\\Mink\\Driver\\Selenium2Driver', $session->getDriver());
	}

	/**
	 * @expectedException \OutOfBoundsException
	 * @expectedExceptionMessage No driver factory for driver
	 */
	public function testItThrowsExceptionForUnknownDriverAlias()
	{
		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser->shouldReceive('getDriver')->once()->andReturn('invalid');

		$this->_factory->createSession($browser);
	}

	/**
	 * @dataProvider driversAvailableByDefaultProvider
	 */
	public function testDriversRegisteredByDefault($driver, $expected_driver)
	{
		if ( !class_exists($expected_driver) ) {
			$this->markTestSkipped(
				sprintf('Test skipped because Mink driver is not installed: "%s"', $expected_driver)
			);
		}

		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser->shouldReceive('getDesiredCapabilities')->andReturn(array());
		$browser->shouldReceive('getBrowserName')->andReturn('');
		$browser->shouldReceive('getTimeout')->andReturn(0);
		$browser->shouldReceive('getHost')->andReturn('');
		$browser->shouldReceive('getPort')->andReturn(0);
		$browser->shouldReceive('getDriverOptions')->andReturn(array());
		$browser->shouldReceive('getDriver')->once()->andReturn($driver);

		$session = $this->_factory->createSession($browser);
		$this->assertInstanceOf($expected_driver, $session->getDriver());
	}

	public function driversAvailableByDefaultProvider()
	{
		return array(
			'selenium2' => array('selenium2', 'Behat\\Mink\\Driver\\Selenium2Driver'),
			'sahi' => array('sahi', 'Behat\\Mink\\Driver\\SahiDriver'),
			'goutte' => array('goutte', 'Behat\\Mink\\Driver\\GoutteDriver'),
			'zombie' => array('zombie', 'Behat\\Mink\\Driver\\ZombieDriver'),
		);
	}

	public function testItRegistersTheDriverFactory()
	{
		$stub_factory = m::mock('aik099\\PHPUnit\\MinkDriver\\IMinkDriverFactory');
		$this->_factory->registerDriverFactory('test', $stub_factory);
		$this->assertAttributeContains($stub_factory, '_driverFactoryRegistry', $this->_factory);
	}

}
