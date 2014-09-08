<?php

use aik099\PHPUnit\BrowserTestCase;

class SauceLabsTest extends BrowserTestCase
{

    public static $browsers = array(
        // Sauce Labs browser configuration.
        array(
            'type' => 'saucelabs',
            'api_username' => '...',
            'api_key' => '...',
            'browserName' => 'firefox',
            'baseUrl' => 'http://www.google.com',
        ),
        // Regular browser configuration.
        array(
            'host' => 'localhost',
            'port' => 4444,
            'browserName' => 'chrome',
            'baseUrl' => 'http://www.google.com',
        ),
    );

}
