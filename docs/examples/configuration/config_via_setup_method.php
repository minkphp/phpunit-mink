<?php

use aik099\PHPUnit\BrowserTestCase;

class PerTestBrowserConfigTest extends BrowserTestCase
{

    protected function setUp()
    {
        // To create regular browser configuration via BrowserConfigurationFactory.
        $browser = $this->createBrowserConfiguration(array(
            // options goes here (optional)
        ));

        // To create "Sauce Labs" browser configuration via BrowserConfigurationFactory.
        $browser = $this->createBrowserConfiguration(array(
            // required
            'type' => 'saucelabs',
            'api_username' => 'sauce_username',
            'api_key' => 'sauce_api_key',
            // optional options goes here
        ));

        // Options can be changed later (optional).
        $browser->setHost('selenium_host')->setPort('selenium_port')->setTimeout(30);
        $browser->setBrowserName('browser name')->setDesiredCapabilities(array(
            'version' => '6.5'
        ));
        $browser->setBaseUrl('http://www.test-host.com');

        // Set browser configuration to test case.
        $this->setBrowser($browser);

        parent::setUp();
    }

}
