<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfigurationFactory;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use Mockery as m;
use tests\aik099\PHPUnit\TestCase\EventDispatcherAwareTestCase;

class BrowserConfigurationFactoryTest extends EventDispatcherAwareTestCase
{

	/**
	 * Browser configuration factory.
	 *
	 * @var BrowserConfigurationFactory
	 */
	private $_factory;

	/**
	 * Configures the tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_factory = new BrowserConfigurationFactory();
	}

	/**
	 * Test description.
	 *
	 * @param array  $browser_config Browser configuration.
	 * @param string $type           Type.
	 *
	 * @return void
	 * @dataProvider createBrowserConfigurationDataProvider
	 */
	public function testCreateBrowserConfiguration(array $browser_config, $type)
	{
		$browser_aliases = array('alias-one' => array());

		$test_case = m::mock('aik099\\PHPUnit\\BrowserTestCase');
		$test_case->shouldReceive('getBrowserAliases')->once()->andReturn($browser_aliases);

		$browser_configuration = $this->_createBrowserConfiguration($type);
		$browser_configuration
			->shouldReceive('setAliases')
			->with($browser_aliases)
			->once()
			->andReturn($browser_configuration);
		$browser_configuration
			->shouldReceive('setup')
			->with($browser_config)
			->once()
			->andReturn($browser_configuration);
		$this->_factory->register($browser_configuration);

		$actual_browser = $this->_factory->createBrowserConfiguration($browser_config, $test_case);
		$this->assertEquals($type, $actual_browser->getType());
		$this->assertInstanceOf('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration', $actual_browser);
	}

	/**
	 * Returns data for possible browser configuration creation ways.
	 *
	 * @return array
	 */
	public function createBrowserConfigurationDataProvider()
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
	 * @expectedException \InvalidArgumentException
	 */
	public function testCreateBrowserConfigurationError()
	{
		$browser_aliases = array('alias-one' => array());

		$test_case = m::mock('aik099\\PHPUnit\\BrowserTestCase');
		$test_case->shouldReceive('getBrowserAliases')->once()->andReturn($browser_aliases);

		$this->_factory->createBrowserConfiguration(array('type' => 'test'), $test_case);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testRegisterFailure()
	{
		$browser_configuration = $this->_createBrowserConfiguration('new-one');
		$this->_factory->register($browser_configuration);
		$this->_factory->register($browser_configuration);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateAPIClientSuccess()
	{
		$browser = new SauceLabsBrowserConfiguration($this->eventDispatcher, $this->_factory);
		$api_client = $this->_factory->createAPIClient($browser);

		$this->assertInstanceOf('WebDriver\\SauceLabs\\SauceRest', $api_client);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \LogicException
	 */
	public function testCreateAPIClientFailure()
	{
		$browser = new BrowserConfiguration($this->eventDispatcher);
		$this->_factory->createAPIClient($browser);
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
		$browser_configuration = m::mock('aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration');
		$browser_configuration->shouldReceive('getType')->andReturn($type);

		return $browser_configuration;
	}

}
