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
use tests\aik099\PHPUnit\TestCase\TestApplicationAwareTestCase;

class BrowserConfigurationFactoryTest extends TestApplicationAwareTestCase
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
		$this->_factory->setApplication($this->application);
	}

	/**
	 * Test description.
	 *
	 * @param array  $browser_config Browser config.
	 * @param string $service_id     Service ID in factory.
	 *
	 * @return void
	 * @dataProvider createBrowserConfigurationDataProvider
	 */
	public function testCreateBrowserConfiguration(array $browser_config, $service_id)
	{
		$browser_aliases = array('alias-one' => array());
		$browser_class = 'aik099\\PHPUnit\\BrowserConfiguration\\BrowserConfiguration';

		$browser = m::mock($browser_class);
		$browser->shouldReceive('setAliases')->with($browser_aliases)->once();
		$browser->shouldReceive('setup')->with($browser_config)->once();
		$this->expectFactoryCall($service_id, $browser);

		$test_case = m::mock('aik099\\PHPUnit\\BrowserTestCase');
		$test_case->shouldReceive('getBrowserAliases')->once()->andReturn($browser_aliases);

		$actual_browser = $this->_factory->createBrowserConfiguration($browser_config, $test_case);
		$this->assertInstanceOf($browser_class, $actual_browser);
	}

	/**
	 * Returns data for possible browser configuration creation ways.
	 *
	 * @return array
	 */
	public function createBrowserConfigurationDataProvider()
	{
		return array(
			array(array('port' => 9999), 'browser_configuration'),
			array(array('port' => 9999, 'sauce' => array()), 'sauce_labs_browser_configuration'),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateAPIClientSuccess()
	{
		$browser = new SauceLabsBrowserConfiguration($this->_factory);
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
		$browser = new BrowserConfiguration();
		$this->_factory->createAPIClient($browser);
	}

}
