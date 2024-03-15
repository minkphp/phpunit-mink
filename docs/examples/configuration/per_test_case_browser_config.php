<?php

use aik099\PHPUnit\BrowserTestCase;

class CommonBrowserConfigTest extends BrowserTestCase
{

    public static $browsers = array(
        array(
            'driver' => 'selenium2',
            'host' => 'localhost',
            'port' => 4444,
            'browserName' => 'firefox',
            'baseUrl' => 'http://www.google.com',
            'sessionStrategy' => 'shared',
        ),
    );

    /**
     * @before
     */
    public function setUpTest()
    {
        parent::setUpTest();

        if ( $this->getSessionStrategy()->isFreshSession() ) {
            // login once before any of the tests was started
        }
    }

    public function testOne()
    {
        // user will be already logged-in regardless
        // of the test execution order/filtering
    }

    public function testTwo()
    {
        // user will be already logged-in regardless
        // of the test execution order/filtering
    }

    /**
     * @inheritDoc
     */
    public function onTestSuiteEnded()
    {
        $session = $this->getSession(false);

        if ( $session !== null && $session->isStarted() ) {
            // logout once after all the tests were finished
        }

        return parent::onTestSuiteEnded();
    }

}
