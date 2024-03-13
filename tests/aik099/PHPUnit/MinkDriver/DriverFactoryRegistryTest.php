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


use aik099\PHPUnit\MinkDriver\DriverFactoryRegistry;
use Mockery as m;
use tests\aik099\PHPUnit\AbstractTestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;
use aik099\PHPUnit\MinkDriver\IMinkDriverFactory;

class DriverFactoryRegistryTest extends AbstractTestCase
{

	use ExpectException;

	/**
	 * Driver factory registry.
	 *
	 * @var DriverFactoryRegistry|m\MockInterface
	 */
	private $_driverFactoryRegistry;

	/**
	 * @before
	 */
	public function setUpTest()
	{
		$this->_driverFactoryRegistry = new DriverFactoryRegistry();
	}

	public function testAddingAndGetting()
	{
		$factory = m::mock(IMinkDriverFactory::class);
		$factory->shouldReceive('getDriverName')->andReturn('test');

		$this->_driverFactoryRegistry->add($factory);

		$this->assertSame($factory, $this->_driverFactoryRegistry->get('test'));
	}

	public function testAddingExisting()
	{
		$this->expectException('LogicException');
		$this->expectExceptionMessage('Driver factory for "test" driver is already registered.');

		$factory = m::mock(IMinkDriverFactory::class);
		$factory->shouldReceive('getDriverName')->andReturn('test');

		$this->_driverFactoryRegistry->add($factory);
		$this->_driverFactoryRegistry->add($factory);
	}

	public function testGettingNonExisting()
	{
		$this->expectException('OutOfBoundsException');
		$this->expectExceptionMessage('No driver factory for "test" driver.');

		$this->_driverFactoryRegistry->get('test');
	}

}
