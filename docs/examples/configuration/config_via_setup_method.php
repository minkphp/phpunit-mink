<?php

use aik099\PHPUnit\BrowserTestCase;

class PerTestBrowserConfigTest extends BrowserTestCase
{

    /**
     * @before
     */
    protected function setUpTest()
    {
        // Creates the regular browser configuration using "BrowserConfigurationFactory".
        $browser = $this->createBrowserConfiguration(array(
            // options goes here (optional)
        ));

        // Creates the "Sauce Labs" browser configuration using "BrowserConfigurationFactory".
        $browser = $this->createBrowserConfiguration(array(
            // required
            'type' => 'saucelabs',
            'apiUsername' => 'sauce_username',
            'apiKey' => 'sauce_api_key',
            // optional options goes here
        ));

        // Creates the "BrowserStack" browser configuration using "BrowserConfigurationFactory".
        $browser = $this->createBrowserConfiguration(array(
            // required
            'type' => 'browserstack',
            'apiUsername' => 'bs_username',
            'apiKey' => 'bs_api_key',
            // optional options goes here
        ));

        // Options can be changed later (optional).
        $browser->setHost('selenium_host')->setPort('selenium_port')->setTimeout(30);
        $browser->setBrowserName('browser name')->setDesiredCapabilities(array(
            'version' => '6.5'
        ));
        $browser->setBaseUrl('http://www.test-host.com');

        // Set browser configuration to the test case.
        $this->setBrowser($browser);

        parent::setUpTest();
    }

}
