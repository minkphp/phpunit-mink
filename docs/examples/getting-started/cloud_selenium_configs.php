<?php

use aik099\PHPUnit\BrowserTestCase;

class BrowserConfigExampleTest extends BrowserTestCase
{

    public static $browsers = array(
        // Sauce Labs browser configuration.
        array(
            'type' => 'saucelabs',
            'apiUsername' => '...',
            'apiKey' => '...',
            'browserName' => 'firefox',
            'baseUrl' => 'http://www.google.com',
        ),
        // BrowserStack browser configuration.
        array(
            'type' => 'browserstack',
            'api_username' => '...',
            'api_key' => '...',
            'browserName' => 'firefox',
            'baseUrl' => 'http://www.google.com',
        ),
        // Regular browser configuration.
        array(
            'driver' => 'selenium2',
            'host' => 'localhost',
            'port' => 4444,
            'browserName' => 'chrome',
            'baseUrl' => 'http://www.google.com',
        ),
    );

}
