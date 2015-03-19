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


use aik099\PHPUnit\MinkDriver\SahiDriverFactory;
use Mockery as m;

class SahiDriverFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var string
	 */
	private $_driverClass = 'Behat\\Mink\\Driver\\SahiDriver';

	/**
	 * @var SahiDriverFactory;
	 */
	private $_factory;

	public function setUp()
	{
		if ( !class_exists($this->_driverClass) ) {
			$this->markTestSkipped(sprintf('Mink driver not installed: "%s"', $this->_driverClass));
		}

		$this->_factory = new SahiDriverFactory();
	}

	public function testItImplementsTheDriverFactoryInterface()
	{
		$this->assertInstanceOf('aik099\\PHPUnit\\MinkDriver\\IMinkDriverFactory', $this->_factory);
	}

	public function testItReturnsASahiDriver()
	{
		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser->shouldReceive('getBrowserName')->once()->andReturn('firefox');
		$browser->shouldReceive('getHost')->once()->andReturn('');
		$browser->shouldReceive('getPort')->once()->andReturn(0);
		$this->assertInstanceOf($this->_driverClass, $this->_factory->getInstance($browser));
	}

}
