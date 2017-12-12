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


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\Session\SessionFactory;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Session;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SessionFactoryTest extends MockeryTestCase
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
		$driver = m::mock(DriverInterface::class);
		$driver->shouldReceive('setSession')->with(m::any())->once();

		$browser = m::mock(BrowserConfiguration::class);
		$browser->shouldReceive('createDriver')->once()->andReturn($driver);

		$session = $this->_factory->createSession($browser);

		$this->assertInstanceOf(Session::class, $session);
		$this->assertSame($driver, $session->getDriver());
	}

}
