# PHPUnit-Mink
[![Build Status](https://travis-ci.org/aik099/phpunit-mink.png?branch=master)](https://travis-ci.org/aik099/phpunit-mink)
[![HHVM Status](http://hhvm.h4cc.de/badge/aik099/phpunit-mink.png)](http://hhvm.h4cc.de/package/aik099/phpunit-mink)

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/aik099/phpunit-mink/badges/quality-score.png?s=57e408500d59e10ce44b604df678ec8b59a1b8f8)](https://scrutinizer-ci.com/g/aik099/phpunit-mink/)
[![Coverage Status](https://coveralls.io/repos/aik099/phpunit-mink/badge.png?branch=master)](https://coveralls.io/r/aik099/phpunit-mink?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/52ad65e0ec1375ead3000049/badge.png)](https://www.versioneye.com/user/projects/52ad65e0ec1375ead3000049)

[![Latest Stable Version](https://poser.pugx.org/aik099/phpunit-mink/v/stable.png)](https://packagist.org/packages/aik099/phpunit-mink)
[![Total Downloads](https://poser.pugx.org/aik099/phpunit-mink/downloads.png)](https://packagist.org/packages/aik099/phpunit-mink)

This library is an extension for [PHPUnit](sebastianbergmann/phpunit), that allows to write tests with help of [Mink](Behat/Mink).

## Overview
This library allows to perform following things:

* use [Mink](Behat/Mink) for browser session control
* each test in a test case can use independent browser session
* all tests in a test case can share session between them
* Selenium server connection details are decoupled from tests using them
* perform individual browser configuration for each test in a test case
* support for "[Sauce Labs](https://saucelabs.com/)"
* remote code coverage collection

Each mentioned above features is described in more detail below.

## Basic Usage
1. create subclass from `\aik099\PHPUnit\BrowserTestCase` class
2. define used browser configurations in static `$browsers` property of that class
3. use `$this->getSession()` method in your tests to access [Mink](Behat/Mink) session

## Using Mink
Call `$this->getSession()` from a test to get running `\Behat\Mink\Session` object, which is already configured from test configuration.

```php
<?php

use aik099\PHPUnit\BrowserTestCase;

class SessionTest extends BrowserTestCase
{

	public function testSession()
	{
		$session = $this->getSession();

		$session->visit('http://www.google.com');

		$this->assertTrue($session->getPage()->hasContent('Google'));
	}

}
```


## Per-test Browser Configuration
It is possible to set individual browser configuration for each test in a test case by creating a `\aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration` class instance in `setUp` method of the test case.

```php
<?php

use aik099\PHPUnit\BrowserTestCase;
use aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration;

class PerBrowserConfigTest extends BrowserTestCase
{

	protected function setUp()
	{
		// to create regular browser configuration via BrowserConfigurationFactory
		$browser = $this->createBrowserConfiguration(array(
			// options goes here (optional)
		));

		// to create "Sauce Labs" browser configuration via BrowserConfigurationFactory
		$browser = $this->createBrowserConfiguration(array(
			// required
			'type' => 'saucelabs',
			'api_username' => 'sauce_username',
			'api_key' => 'sauce_api_key',
			// options (optional) goes here
		));

		// options can be changed later (optional)
		$browser->setHost('selenium_host')->setPort('selenium_port')->setTimeout(30);
		$browser->setBrowserName('browser name')->setDesiredCapabilities(array('version' => '6.5'));
		$browser->setBaseUrl('http://www.test-host.com');

		// set browser configuration to test case
		$this->setBrowser($browser);

		parent::setUp();
	}

}
```

## Sharing Browser Configuration Between Tests
It is possible to define a single browser configuration to be used for each test in test case. This can be done by defining static `$browsers` class variable as an array, where each item represents a single browser configuration. In that case each of the tests in a test case would be executed using each of defined browser configurations.

```php
<?php

use aik099\PHPUnit\BrowserTestCase;

class CommonBrowserConfigTest extends BrowserTestCase
{

	public static $browsers = array(
		array(
			'host' => 'localhost',
			'port' => 4444,
			'browserName' => 'firefox',
			'baseUrl' => 'http://www.google.com',
		),
		array(
			'host' => 'localhost',
			'port' => 4444,
			'browserName' => 'chrome',
			'baseUrl' => 'http://www.google.com',
		),
	);

	public function testUsingBrowsersArray()
	{
		echo sprintf("I'm executed using '%s' browser", $this->getBrowser()->getBrowserName());
	}

}
```

## Sharing Session Between Tests
As a benefit of shared browser configuration, that was described above is an ability to not only share browser configuration, that is used to create [Mink](Behat/Mink) session, but to actually share created sessions between all tests in a test case. This can be done by adding `sessionStrategy` option to browser configuration.

```php
<?php

use aik099\PHPUnit\BrowserTestCase;

class CommonBrowserConfigTest extends BrowserTestCase
{

	public static $browsers = array(
		array(
			'host' => 'localhost',
			'port' => 4444,
			'browserName' => 'firefox',
			'baseUrl' => 'http://www.google.com',
			'sessionStrategy' => 'shared',
		),
	);

}
```

## Using Browser Aliases
All previous examples demonstrate various ways how browser configuration can be defined, but they all have same downside - server connection details stay hard-coded in test case classes. This could become very problematic if:

* same test cases needs to be executed on different servers (e.g. each developer runs them on his own machine)
* due change of server connection details each test case class needs to be changed

To solve this problem a browser aliases were introduced. Basically a browser alias is predefined browser configuration, that is available in the test case by it's alias. How it can be used:

1. create base test case class, by extending BrowserTestCase class in the project with `getBrowserAliases` method in it. That method will return an associative array of a browser configurations (array key acts as alias name)
2. in any place, where browser configuration is defined use `'alias' => 'alias_name_here'` instead of actual browser configuration
3. feel free to override any part of configuration defined in alias
4. nested aliases are also supported

```php
<?php

use aik099\PHPUnit\BrowserTestCase;

abstract class BrowserAliasTest extends BrowserTestCase
{

	public function getBrowserAliases()
	{
		return array(
			'example_alias' => array(
				'host' => 'localhost',
				'port' => 4444,
				'browserName' => 'firefox',
				'baseUrl' => 'http://www.google.com',
			),
		);
	}

}


class ConcreteTest extends BrowserAliasTest
{

	public static $browsers = array(
		array(
			'alias' => 'example_alias',

		),
		array(
			'alias' => 'example_alias',
			'browserName' => 'chrome',
		),
	);
}

```

## Using "Sauce Labs"
When using "Sauce Labs" account to perform Selenium server-based testing you need to specify `'type' => 'saucelabs', 'api_username' => '...', 'api_key' => '...'` instead of `host` or `port` settings. In all other aspects all will work the same as if all tests are running locally.

## Remote Code Coverage
Browser tests are executed on different machine, then one, where code coverage information is collected (and tests are executed). To solve that problem this library uses remote coverage collection. Following steps needs to be performed before using this feature:

### On Remote Server
Remote server is web-server, where website used in tests is located.

1. Install [Xdebug](http://xdebug.org/) PHP extension on web-server
2. Copy `library/aik099/PHPUnit/RemoteCoverage/RemoteCoverageTool.php` into web-server's DocumentRoot directory.
3. Include following code before your application bootstraps:

```php
require_once 'RemoteCoverageTool.php';
\aik099\PHPUnit\RemoteCoverage\RemoteCoverageTool::init();
```

### On Test Machine
This is machine, where PHPUnit tests are being executed.

By default the `baseUrl` setting from browser configuration is used as the `remote code coverage information url`. However if a need exists to set alternative url on per-test basis, then place following code in the `setUp` method of the test case class, that extends `BrowserTestCase` class:

```php
	$this->setRemoteCoverageScriptUrl('http://host/'); // `host` should be replaced with web server's url
```

### How This Works
1. each test sets a special cookie on website under test
2. when cookie is present, then `RemoteCoverageTool.php` script collects coverage information and stores it on disk
3. once test finishes, then `http://host/?rct_mode=output` url is accessed on remote server, which in turn returns collected coverage information
4. remote coverage information is then joined with coverage information collected locally on test machine

## Browser Configuration in Details
Each browser configuration consists of the following settings:

| Name | Description |
|---|---|
| `host` | host, where Selenium Server is located (defaults to `localhost`) |
| `port` | port, on which Selenium Server is listening for incoming connections (defaults to `4444`) |
| `timeout` | connection timeout of the server in seconds (defaults to `60`) |
| `browserName` | name of browser to use (e.g. `firefox`, `chrome`, etc., defaults to `firefox`) |
| `desiredCapabilities` | parameters, that specify additional browser configuration (e.g. browser version, platform, etc.) |
| `baseUrl` | base url of website, that is tested |
| `sauce` | Sauce Labs connection configuration (e.g. `array('username' => 'username_here', 'api_key' => 'api_key_here')`) |

There are also corresponding `set` and `get` methods for each of mentioned above settings, that allow to individually change them before test has started (from `setUp` method).

## Using Composer

1. Define the dependencies in your ```composer.json```:
```json
{
	"require": {
		"aik099/phpunit-mink": "~1.0"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/aik099/phpunit-mink"
		},
	]
}
```

2. Install/update your vendors:
```bash
$ curl http://getcomposer.org/installer | php
$ php composer.phar install
```
