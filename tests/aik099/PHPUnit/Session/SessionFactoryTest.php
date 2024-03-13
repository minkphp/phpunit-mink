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
use tests\aik099\PHPUnit\AbstractTestCase;
use Behat\Mink\Driver\DriverInterface;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use Behat\Mink\Session;

class SessionFactoryTest extends AbstractTestCase
{

	/**
	 * Session factory.
	 *
	 * @var SessionFactory
	 */
	private $_factory;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_factory = new SessionFactory();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSession()
	{
		$driver = m::mock(DriverInterface::class);
		$driver->shouldReceive('setSession')->with(m::any())->once();

		$browser = m::mock(BrowserConfiguration::class);
		$browser->shouldReceive('createDriver')->once()->andReturn($driver);

		$session = $this->_factory->createSession($browser);

		$this->assertInstanceOf(Session::class, $session);
		$this->assertSame($driver, $session->getDriver());
	}

}
