<?php

use aik099\PHPUnit\BrowserTestCase;

class GeneralTest extends BrowserTestCase
{

    public static $browsers = array(
        array(
            'driver' => 'selenium2',
            'host' => 'localhost',
            'port' => 4444,
            'browserName' => 'firefox',
            'baseUrl' => 'http://www.google.com',
        ),
    );

    public function testUsingSession()
    {
        // This is Mink's Session.
        $session = $this->getSession();

        // Go to a page.
        $session->visit('http://www.google.com');

        // Validate text presence on a page.
        $this->assertTrue($session->getPage()->hasContent('Google'));
    }

    public function testUsingBrowser()
    {
        // Prints the name of used browser.
        echo sprintf(
            "I'm executed using '%s' browser",
            $this->getBrowser()->getBrowserName()
        );
    }

}
