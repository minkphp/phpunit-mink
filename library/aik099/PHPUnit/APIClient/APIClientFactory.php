<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\APIClient;


use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\BrowserStackBrowserConfiguration;
use aik099\PHPUnit\BrowserConfiguration\SauceLabsBrowserConfiguration;
use WebDriver\SauceLabs\SauceRest;
use WebDriver\ServiceFactory;

class APIClientFactory
{

	/**
	 * Creates an API client based on a browser configuration.
	 *
	 * @param BrowserConfiguration $browser The browser configuration.
	 *
	 * @return IAPIClient
	 * @throws \LogicException When unsupported browser configuration was given.
	 */
	public function getAPIClient(BrowserConfiguration $browser)
	{
		if ( $browser instanceof BrowserStackBrowserConfiguration ) {
			return new BrowserStackAPIClient(
				$browser->getApiUsername(),
				$browser->getApiKey(),
				ServiceFactory::getInstance()->getService('service.curl')
			);
		}

		if ( $browser instanceof SauceLabsBrowserConfiguration ) {
			$sauce_rest = new SauceRest($browser->getApiUsername(), $browser->getApiKey());

			return new SauceLabsAPIClient($sauce_rest);
		}

		throw new \LogicException('The "' . $browser->getType() . '" browser configuration is not supported.');
	}

}
