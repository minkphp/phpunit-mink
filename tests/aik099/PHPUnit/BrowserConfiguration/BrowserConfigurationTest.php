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
use aik099\PHPUnit\SessionStrategy\SessionStrategyManager;
use Mockery as m;

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
	 * Complete setup.
	 *
	 * @var array
	 */
	protected $setup = array();

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

		$this->setup = array(
			'host' => $this->host,
			'port' => $this->port,
			'browserName' => 'safari',
			'desiredCapabilities' => array('platform' => 'Windows 7', 'version' => 10),
			'seleniumServerRequestsTimeout' => 500,
			'baseUrl' => 'http://other-host',
			'sessionStrategy' => SessionStrategyManager::SHARED_STRATEGY,
		);
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

		$browser->setup(array('alias' => 'a1'));

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

		$browser->setup(array('alias' => 'a1'));

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

		$browser->setup(array('alias' => 'a1', 'browserName' => 'firefox'));

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
		$browser->setup(array('alias' => 'not_found'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveBrowserAliasWithoutAliasGiven()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setup(array('browserName' => 'safari'));

		$this->assertEquals('safari', $browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @param BrowserConfiguration|null $browser Browser configuration.
	 *
	 * @return void
	 */
	public function testSetup(BrowserConfiguration $browser = null)
	{
		if ( !isset($browser) ) {
			$browser = $this->createBrowserConfiguration();
		}

		$this->assertSame($browser, $browser->setup($this->setup));
		$this->assertSame($this->setup['host'], $browser->getHost());
		$this->assertSame($this->setup['port'], $browser->getPort());
		$this->assertSame($this->setup['browserName'], $browser->getBrowserName());
		$this->assertSame($this->setup['desiredCapabilities'], $browser->getDesiredCapabilities());
		$this->assertSame($this->setup['seleniumServerRequestsTimeout'], $browser->getSeleniumServerRequestsTimeout());
		$this->assertSame($this->setup['baseUrl'], $browser->getBaseUrl());
		$this->assertSame($this->setup['sessionStrategy'], $browser->getSessionStrategy());
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
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategyCorrect()
	{
		$browser = $this->createBrowserConfiguration();

		$expected = SessionStrategyManager::SHARED_STRATEGY;
		$this->assertSame($browser, $browser->setSessionStrategy($expected));
		$this->assertSame($expected, $browser->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSessionStrategyIncorrect()
	{
		$browser = $this->createBrowserConfiguration();
		$browser->setSessionStrategy('wrong');
	}

	/**
	 * Test description.
	 *
	 * @param string $session_strategy Session strategy name.
	 *
	 * @return void
	 * @dataProvider sessionSharingDataProvider
	 */
	public function testGetSessionStrategyHashBrowserSharing($session_strategy)
	{
		$test_case = m::mock('\\aik099\\PHPUnit\\BrowserTestCase');
		/* @var $test_case BrowserTestCase */

		$browser1 = $this->createBrowserConfiguration();
		$browser1->setSessionStrategy($session_strategy);

		$browser2 = $this->createBrowserConfiguration();
		$browser2->setSessionStrategy($session_strategy);

		$this->assertSame($browser1->getSessionStrategyHash($test_case), $browser2->getSessionStrategyHash($test_case));
	}

	/**
	 * Provides test data for session strategy hash sharing testing.
	 *
	 * @return array
	 */
	public function sessionSharingDataProvider()
	{
		return array(
			array(SessionStrategyManager::ISOLATED_STRATEGY),
			array(SessionStrategyManager::SHARED_STRATEGY),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetSessionStrategyHashNotSharing()
	{
		$test_case1 = m::mock('\\aik099\\PHPUnit\\BrowserTestCase');
		/* @var $test_case1 BrowserTestCase */

		$browser1 = $this->createBrowserConfiguration();
		$browser1->setSessionStrategy(SessionStrategyManager::SHARED_STRATEGY);

		$test_case2 = m::mock('\\aik099\\PHPUnit\\BrowserTestCase');
		/* @var $test_case2 BrowserTestCase */

		$browser2 = $this->createBrowserConfiguration();
		$browser2->setSessionStrategy(SessionStrategyManager::SHARED_STRATEGY);

		$this->assertNotSame($browser1->getSessionStrategyHash($test_case1), $browser2->getSessionStrategyHash($test_case2));
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
