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


use aik099\PHPUnit\MinkDriver\GoutteDriverFactory;
use Mockery as m;

class GoutteDriverFactoryTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var string
	 */
	private $_driverClass = 'Behat\\Mink\\Driver\\GoutteDriver';

	/**
	 * @var GoutteDriverFactory;
	 */
	private $_factory;

	public function setUp()
	{
		if ( !class_exists($this->_driverClass) ) {
			$this->markTestSkipped(sprintf('Mink driver not installed: "%s"', $this->_driverClass));
		}

		$this->_factory = new GoutteDriverFactory();
	}

	public function testItImplementsTheDriverFactoryInterface()
	{
		$this->assertInstanceOf('aik099\\PHPUnit\\MinkDriver\\IMinkDriverFactory', $this->_factory);
	}

	public function testItReturnsAGoutteDriver()
	{
		$browser = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$this->assertInstanceOf($this->_driverClass, $this->_factory->getInstance($browser));
	}

}
