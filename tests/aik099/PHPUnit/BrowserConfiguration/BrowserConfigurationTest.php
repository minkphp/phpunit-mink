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
use aik099\PHPUnit\BrowserTestCase;

class BrowserConfigurationTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Hostname.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Port.
	 *
	 * @var integer
	 */
	protected $port;

	/**
	 * Configures all tests.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->host = 'example_host';
		$this->port = 1234;
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveBrowserAliasUsingSingleAlias()
	{
		$browser = $this->createBrowserConfiguration(array(
			'a1' => array('host' => $this->host, 'port' => $this->port),
		));

		$browser->configure(array('alias' => 'a1'));

		$this->assertEquals($this->host, $browser->getHost());
		$this->assertEquals($this->port, $browser->getPort());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveBrowserAliasUsingRecursiveAlias()
	{
		$browser = $this->createBrowserConfiguration(array(
			'a1' => array('alias' => 'a2', 'host' => $this->host, 'port' => $this->port),
			'a2' => array('browserName' => 'safari', 'baseUrl' => 'http://example_host'),
		));

		$browser->configure(array('alias' => 'a1'));

		$this->assertEquals($this->host, $browser->getHost());
		$this->assertEquals($this->port, $browser->getPort());
		$this->assertEquals('safari', $browser->getBrowserName());
		$this->assertEquals('http://example_host', $browser->getBaseUrl());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveBrowserAliasUsingAliasMerging()
	{
		$browser = $this->createBrowserConfiguration(array(
			'a1' => array('host' => $this->host, 'port' => $this->port),
		));

		$browser->configure(array('alias' => 'a1', 'browserName' => 'firefox'));

		$this->assertEquals($this->host, $browser->getHost());
		$this->assertEquals($this->port, $browser->getPort());
		$this->assertEquals('firefox', $browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testResolveBrowserAliasUsingIncorrectAlias()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->configure(array('alias' => 'not_found'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveBrowserAliasWithoutAliasGiven()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->configure(array('browserName' => 'safari'));

		$this->assertEquals('safari', $browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetHostIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setHost(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetHostCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = 'EXAMPLE_HOST';
		$this->assertSame($browser, $browser->setHost($expected));
		$this->assertSame($expected, $browser->getHost());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetPortIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setPort('5555');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetPortCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = 5555;
		$this->assertSame($browser, $browser->setPort($expected));
		$this->assertSame($expected, $browser->getPort());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetBrowserNameIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setBrowserName(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserNameCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = 'firefox';
		$this->assertSame($browser, $browser->setBrowserName($expected));
		$this->assertSame($expected, $browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetBaseUrlIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setBaseUrl(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBaseUrlCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = 'http://some-url';
		$this->assertSame($browser, $browser->setBaseUrl($expected));
		$this->assertSame($expected, $browser->getBaseUrl());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetDesiredCapabilitiesCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = array('k1' => 'v1', 'k2' => 'v2');
		$this->assertSame($browser, $browser->setDesiredCapabilities($expected));
		$this->assertSame($expected, $browser->getDesiredCapabilities());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSeleniumServerRequestsTimeoutCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = 1000;
		$this->assertSame($browser, $browser->setSeleniumServerRequestsTimeout($expected));
		$this->assertSame($expected, $browser->getSeleniumServerRequestsTimeout());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSeleniumServerRequestsTimeoutIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setSeleniumServerRequestsTimeout('5555');
	}

	/**
	 * Creates instance of browser configuration.
	 *
	 * @param array $aliases Aliases.
	 *
	 * @return BrowserConfiguration
	 */
	protected function createBrowserConfiguration(array $aliases = array())
	{
		return new BrowserConfiguration($aliases);
	}

}