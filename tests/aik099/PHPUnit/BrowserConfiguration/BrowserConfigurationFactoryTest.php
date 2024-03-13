<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\BrowserConfiguration;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use Mockery as m;
use tests\aik099\PHPUnit\AbstractTestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;
use aik099\PHPUnit\BrowserTestCase;

class BrowserConfigurationFactoryTest extends AbstractTestCase
{

	use ExpectException;

	/**
	 * Browser configuration factory.
	 *
	 * @var BrowserConfigurationFactory
	 */
	private $_factory;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_factory = new BrowserConfigurationFactory();
	}

	/**
	 * Test description.
	 *
	 * @param array  $browser_config Browser configuration.
	 * @param string $type           Type.
	 *
	 * @return void
	 * @medium
	 * @dataProvider createBrowserConfigurationDataProvider
	 */
	public function testCreateBrowserConfiguration(array $browser_config, $type)
	{
		$browser_aliases = array('alias-one' => array());

		$test_case = m::mock(BrowserTestCase::class);
		$test_case->shouldReceive('getBrowserAliases')->once()->andReturn($browser_aliases);

		$cleaned_browser_config = $browser_config;
		unset($cleaned_browser_config['type']);

		$browser_configuration = $this->_createBrowserConfiguration($type);
		$browser_configuration
			->shouldReceive('setAliases')
			->with($browser_aliases)
			->once()
			->andReturn($browser_configuration);
		$browser_configuration
			->shouldReceive('setup')
			->with($cleaned_browser_config)
			->once()
			->andReturn($browser_configuration);
		$this->_factory->register($browser_configuration);

		$actual_browser = $this->_factory->createBrowserConfiguration($browser_config, $test_case);
		$this->assertEquals($type, $actual_browser->getType());
		$this->assertInstanceOf(BrowserConfiguration::class, $actual_browser);
	}

	/**
	 * Returns data for possible browser configuration creation ways.
	 *
	 * @return array
	 */
	public static function createBrowserConfigurationDataProvider()
	{
		return array(
			array(array('type' => 'test'), 'test'),
			array(array(), 'default'),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateBrowserConfigurationError()
	{
		$this->expectException('InvalidArgumentException');

		$browser_aliases = array('alias-one' => array());

		$test_case = m::mock(BrowserTestCase::class);
		$test_case->shouldReceive('getBrowserAliases')->once()->andReturn($browser_aliases);

		$this->_factory->createBrowserConfiguration(array('type' => 'test'), $test_case);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testRegisterFailure()
	{
		$this->expectException('InvalidArgumentException');

		$browser_configuration = $this->_createBrowserConfiguration('new-one');
		$this->_factory->register($browser_configuration);
		$this->_factory->register($browser_configuration);
	}

	/**
	 * Creates browser configuration.
	 *
	 * @param string $type Type.
	 *
	 * @return BrowserConfiguration
	 */
	private function _createBrowserConfiguration($type)
	{
		$browser_configuration = m::mock(BrowserConfiguration::class);
		$browser_configuration->shouldReceive('getType')->andReturn($type);

		return $browser_configuration;
	}

}
