<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */


namespace tests\aik099\PHPUnit\MinkDriver;


use aik099\PHPUnit\MinkDriver\Selenium2DriverFactory;
use Mockery as m;

class Selenium2DriverFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var string
	 */
	private $_driverClass = 'Behat\\Mink\\Driver\\Selenium2Driver';

	/**
	 * @var Selenium2DriverFactory;
	 */
	private $_factory;

	public function setUp()
	{
		if ( !class_exists($this->_driverClass) ) {
			$this->markTestSkipped(sprintf('Mink driver not installed: "%s"', $this->_driverClass));
		}

		$this->_factory = new Selenium2DriverFactory();
	}

	public function testItImplementsTheDriverFactoryInterface()
	{
		$this->assertInstanceOf('aik099\\PHPUnit\\MinkDriver\\IMinkDriverFactory', $this->_factory);
	}

	public function testItReturnsASelenium2Driver()
	{
		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser->shouldReceive('getDesiredCapabilities')->once()->andReturn(array());
		$browser->shouldReceive('getBrowserName')->once()->andReturn('');
		$browser->shouldReceive('getTimeout')->once()->andReturn(0);
		$browser->shouldReceive('getHost')->once()->andReturn('');
		$browser->shouldReceive('getPort')->once()->andReturn(0);
		$this->assertInstanceOf($this->_driverClass, $this->_factory->getInstance($browser));
	}

}
