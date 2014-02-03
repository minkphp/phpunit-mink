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

		$session = $this->_factory->createSession($browser);

		$this->assertInstanceOf('Behat\\Mink\\Session', $session);
		$this->assertInstanceOf('Behat\\Mink\\Driver\\Selenium2Driver', $session->getDriver());
	}

}
