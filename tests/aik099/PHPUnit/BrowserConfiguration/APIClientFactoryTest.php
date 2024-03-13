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


use aik099\PHPUnit\APIClient\APIClientFactory;
use aik099\PHPUnit\APIClient\BrowserStackAPIClient;
use aik099\PHPUnit\APIClient\SauceLabsAPIClient;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserStackBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class APIClientFactoryTest extends TestCase
{

	use ExpectException;

	public function testBrowserStackAPIClient()
	{
		$browser = m::mock(BrowserStackBrowserConfiguration::class);
		$browser->shouldReceive('getApiUsername')->once()->andReturn('username');
		$browser->shouldReceive('getApiKey')->once()->andReturn('key');

		$factory = new APIClientFactory();

		$this->assertInstanceOf(BrowserStackAPIClient::class, $factory->getAPIClient($browser));
	}

	public function testSauceLabsAPIClient()
	{
		$browser = m::mock(SauceLabsBrowserConfiguration::class);
		$browser->shouldReceive('getApiUsername')->once()->andReturn('username');
		$browser->shouldReceive('getApiKey')->once()->andReturn('key');

		$factory = new APIClientFactory();

		$this->assertInstanceOf(SauceLabsAPIClient::class, $factory->getAPIClient($browser));
	}

	public function testUnknownAPIClient()
	{
		$browser = m::mock(BrowserConfiguration::class);
		$browser->shouldReceive('getType')->once()->andReturn('browser config type');

		$factory = new APIClientFactory();

		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('The "browser config type" browser configuration is not supported.');

		$factory->getAPIClient($browser);
	}

}
