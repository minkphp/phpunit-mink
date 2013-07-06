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


use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;

class SauceLabsBrowserConfigurationTest extends BrowserConfigurationTest
{

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->host = ':@ondemand.saucelabs.com';
		$this->port = 80;
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSauceIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setSauce(array());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSauceCorrect()
	{
		$expected = array('username' => '', 'api_key' => '');
		$browser = $this->createBrowserConfiguration();

		$this->assertSame($browser, $browser->setSauce($expected));
		$this->assertSame($expected, $browser->getSauce());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetHostCorrect()
	{
		$browser = $this->createBrowserConfiguration(array(), true);

		$this->assertSame($browser, $browser->setHost('EXAMPLE_HOST'));
		$this->assertSame('A:B@ondemand.saucelabs.com', $browser->getHost());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetPortCorrect()
	{
		$browser = $this->createBrowserConfiguration(array(), true);
		$this->assertSame($browser, $browser->setPort(5555));
		$this->assertSame(80, $browser->getPort());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserNameCorrect()
	{
		$browser = $this->createBrowserConfiguration(array(), true);
		$this->assertSame($browser, $browser->setBrowserName(''));
		$this->assertSame('chrome', $browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @param array $desired_capabilities Desired capabilities.
	 * @param array $expected             Expected capabilities.
	 *
	 * @return void
	 * @dataProvider desiredCapabilitiesDataProvider
	 */
	public function testSetDesiredCapabilitiesCorrect(array $desired_capabilities, array $expected)
	{
		$browser = $this->createBrowserConfiguration(array(), true);
		$this->assertSame($browser, $browser->setDesiredCapabilities($desired_capabilities));
		$this->assertSame($expected, $browser->getDesiredCapabilities());
	}

	/**
	 * Desired capability data provider.
	 *
	 * @return array
	 */
	public function desiredCapabilitiesDataProvider()
	{
		return array(
			array(
				array('platform' => 'pl1'),
				array('platform' => 'pl1', 'version' => ''),
			),
			array(
				array('version' => 'ver1'),
				array('version' => 'ver1', 'platform' => 'Windows XP'),
			),
		);
	}

	/**
	 * Creates instance of browser configuration.
	 *
	 * @param array   $aliases    Aliases.
	 * @param boolean $with_sauce Include test sauce configuration.
	 *
	 * @return SauceLabsBrowserConfiguration
	 */
	protected function createBrowserConfiguration(array $aliases = array(), $with_sauce = false)
	{
		$browser = new SauceLabsBrowserConfiguration($aliases);

		if ( $with_sauce ) {
			$browser->setSauce(array('username' => 'A', 'api_key' => 'B'));
		}

		return $browser;
	}

}