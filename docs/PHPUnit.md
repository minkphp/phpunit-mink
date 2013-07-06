Overview
========

This library allows to perform following things:

* use Mink for browser session control
* each test in a test case can use independent browser session
* all tests in a single test case can share session between them
* decoupled Selenium connection details from test cases using them
* perform individual browser session configuration for each test in a test case
* support for "Sauce Labs"
* remote coverage collection

Each mentioned above features is described in more detail below.

Using Mink
----------
Using Mink from PHPUnit test never been easier. Just grab a session, which is from where all interactions with Mink begins and use it.

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


Per-Test Browser Configuration
------------------------------

It is possible to set individual browser configuration for each test in a test case by calling `$this->set*` methods from inside of `setUp` method of a test case.

```php
<?php

use aik099\PHPUnit\BrowserTestCase;

class PerBrowserConfigTest extends BrowserTestCase
{

	protected function setUp()
	{
		$this->setSauce(array('username' => 'sause_username', 'api_key' => 'sause_api_key'));
		$this->setHost('selenium_host')->setPort('selenium_port')->setBrowser('browser name');

		$this->setDesiredCapabilities(array('version' => '6.5'));
		$this->setSeleniumServerRequestsTimeout(30);
		$this->setBaseUrl('http://www.test-host.com');

		parent::setUp();
	}

}
```

Sharing Browser Configuration Between Tests
-------------------------------------------
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

	public function testUsingBrowserArray()
	{
		echo sprintf("I'm executed using '%s' browser", $this->getBrowser());
	}

}
```

Sharing Session Between Tests
-----------------------------
As a benefit of shared browser configuration, that was described above is an ability to not only share browser configuration, that is used to create Mink session, but to actually share created sessions between all tests in a single test case. This can be done by adding `sessionStrategy` option to browser configuration.

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


Using Browser Aliases
---------------------
All previous examples demonstrate various ways how browser configuration can be defined, but they all have same downside - Selenium server connection details stay hard-coded in test cases. This could become very problematic if:

* same test cases needs to be executed on different Selenium servers (e.g. each developer runs them on his own machine)
* due change of Selenium server connection details each test case needs to be changed

To solve this problem an browser aliases were introduced. Basically a browser alias is predefined browser configuration, that is available in test case through it's alias. How it can be used:

1. create base BrowserTestCase class in the project with `getBrowserAliases` method in it, which would return an associative array of browser configurations (array key is alias name)
2. in any place, where browser configuration is defined use `'alias' => 'alias_name_here'` instead of actual browser configuration
3. feel free to override any part of configuration defined in alias

```php
<?php

use aik099\PHPUnit\BrowserTestCase;

abstract class BrowserAliasTest extends BrowserTestCase
{

	protected function getBrowserAliases()
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

Using "Sauce Labs"
------------------
When using "Sauce Labs" account to perform Selenium testing there is no need to specify `host` or `port`, because they are detected automatically based on "Sauce Labs" connection parameters from `sauce` key in browser configuration.


Remote Code Coverage
--------------------
Browser tests are executed on different machine, then one, where test is executed and where code coverage information is collected. To solve that problem this library uses remote coverage collection. These  steps needs to be performed before using this feature:

### On Remote Server
Remote server is webserver, where website used in tests are located.

1. Install Xdebug PHP extension on webserver
2. Copy `library/aik099/PHPUnit/Common/phpunit_coverage.php` into your webserver's document root directory.
3. In your webserver's php.ini configuration file, configure `library/aik099/PHPUnit/Common/prepend.php` and `library/aik099/PHPUnit/Common/append.php` as the `auto_prepend_file` and `auto_append_file`, respectively.

### On Test Machine
This is machine, where PHPUnit tests are being executed.

1. In your test case class that extends `BrowserTestCase`, use `protected $coverageScriptUrl = 'http://host/phpunit_coverage.php';` to configure the URL for the `phpunit_coverage.php` script.

### How This Works

1. each test sets a special cookie to to website under test
2. when cookie is present, then `prepend.php` script collects coverage information and `append.php` stores it on disk
3. once test finished `phpunit_coverage.php` is accessed on remote server, which return collected coverage information
4. remote coverage information is then joined with coverage information collected on test machine


Browser Configuration in Details
--------------------------------
Each browser configuration consists of the following settings:

* `host` - host, where Selenium Server is located (default `localhost`)
* `port` - port, on which Selenium Server is listening for incoming connections (default `4444`)
* `browserName` - name of browser to use (e.g. `firefox`, `chrome`, etc.)
* `desiredCapabilities` - parameters, that specify browser configuraion in more detail (e.g. browser version, platform, etc.)
* `seleniumServerRequestsTimeout` - connection timeout of Selenium Server
* `baseUrl` - base url, used during tests
* `sauce` - Sauce Labs connection configuration (e.g. `array('username' => 'username_here', 'api_key' => 'api_key_here')`)

There also `set` and `get` methods for each of mentioned above settings, that allow to individually change them before test has started (from `setUp` method).


# More Reading
* https://github.com/sebastianbergmann/phpunit-selenium