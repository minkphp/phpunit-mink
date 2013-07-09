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
	 * Browser configuration.
	 *
	 * @var BrowserConfiguration
	 */
	protected $browser;

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

		$this->browser = $this->createBrowserConfiguration();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveAliasUsingSingleAlias()
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
	public function testResolveAliasUsingRecursiveAlias()
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
	public function testResolveAliasUsingAliasMerging()
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
	 */
	public function testResolveAliasWithOverwrite()
	{
		$browser = $this->createBrowserConfiguration(array(
			'a1' => array('host' => 'alias-host', 'port' => $this->port),
		));

		$browser->setup(array('alias' => 'a1', 'host' => $this->host));

		$this->assertEquals($this->host, $browser->getHost());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testResolveAliasUsingIncorrectAlias()
	{
		$this->browser->setup(array('alias' => 'not_found'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testResolveAliasWithoutAliasGiven()
	{
		$this->browser->setup(array('browserName' => 'safari'));

		$this->assertEquals('safari', $this->browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetup()
	{
		$this->assertSame($this->browser, $this->browser->setup($this->setup));
		$this->assertSame($this->setup['host'], $this->browser->getHost());
		$this->assertSame($this->setup['port'], $this->browser->getPort());
		$this->assertSame($this->setup['browserName'], $this->browser->getBrowserName());
		$this->assertSame($this->setup['desiredCapabilities'], $this->browser->getDesiredCapabilities());
		$this->assertSame($this->setup['seleniumServerRequestsTimeout'], $this->browser->getSeleniumServerRequestsTimeout());
		$this->assertSame($this->setup['baseUrl'], $this->browser->getBaseUrl());
		$this->assertSame($this->setup['sessionStrategy'], $this->browser->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetHostIncorrect()
	{
		$this->browser->setHost(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetHostCorrect()
	{
		$expected = 'EXAMPLE_HOST';
		$this->assertSame($this->browser, $this->browser->setHost($expected));
		$this->assertSame($expected, $this->browser->getHost());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetPortIncorrect()
	{
		$this->browser->setPort('5555');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetPortCorrect()
	{
		$expected = 5555;
		$this->assertSame($this->browser, $this->browser->setPort($expected));
		$this->assertSame($expected, $this->browser->getPort());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetBrowserNameIncorrect()
	{
		$this->browser->setBrowserName(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBrowserNameCorrect()
	{
		$expected = 'firefox';
		$this->assertSame($this->browser, $this->browser->setBrowserName($expected));
		$this->assertSame($expected, $this->browser->getBrowserName());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetBaseUrlIncorrect()
	{
		$this->browser->setBaseUrl(5555);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetBaseUrlCorrect()
	{
		$expected = 'http://some-url';
		$this->assertSame($this->browser, $this->browser->setBaseUrl($expected));
		$this->assertSame($expected, $this->browser->getBaseUrl());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetDesiredCapabilitiesCorrect()
	{
		$expected = array('k1' => 'v1', 'k2' => 'v2');
		$this->assertSame($this->browser, $this->browser->setDesiredCapabilities($expected));
		$this->assertSame($expected, $this->browser->getDesiredCapabilities());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSeleniumServerRequestsTimeoutCorrect()
	{
		$expected = 1000;
		$this->assertSame($this->browser, $this->browser->setSeleniumServerRequestsTimeout($expected));
		$this->assertSame($expected, $this->browser->getSeleniumServerRequestsTimeout());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSeleniumServerRequestsTimeoutIncorrect()
	{
		$this->browser->setSeleniumServerRequestsTimeout('5555');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetSessionStrategyCorrect()
	{
		$expected = SessionStrategyManager::SHARED_STRATEGY;
		$this->assertSame($this->browser, $this->browser->setSessionStrategy($expected));
		$this->assertSame($expected, $this->browser->getSessionStrategy());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSessionStrategyIncorrect()
	{
		$this->browser->setSessionStrategy('wrong');
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
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateSession()
	{
		$session = $this->browser->createSession();

		$this->assertInstanceOf('\\Behat\\Mink\\Session', $session);
		$this->assertInstanceOf('\\Behat\\Mink\\Driver\\Selenium2Driver', $session->getDriver());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testSetUpHook()
	{
		$test_case = m::mock('\\aik099\\PHPUnit\\BrowserTestCase');
		/* @var $test_case BrowserTestCase */

		$this->assertSame($this->browser, $this->browser->testSetUpHook($test_case));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testAfterRunHook()
	{
		$test_case = m::mock('\\aik099\\PHPUnit\\BrowserTestCase');
		/* @var $test_case BrowserTestCase */

		$test_result = m::mock('\\PHPUnit_Framework_TestResult');
		/* @var $test_result \PHPUnit_Framework_TestResult */

		$this->assertSame($this->browser, $this->browser->testAfterRunHook($test_case, $test_result));
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
