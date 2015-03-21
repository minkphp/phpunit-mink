<?php

use aik099\PHPUnit\BrowserTestCase;

abstract class BrowserAliasTestCase extends BrowserTestCase
{

    public function getBrowserAliases()
    {
        return array(
            'example_alias' => array(
                'driver' => 'selenium2',
                'host' => 'localhost',
                'port' => 4444,
                'browserName' => 'firefox',
                'baseUrl' => 'http://www.google.com',
            ),
        );
    }

}


class ConcreteTest extends BrowserAliasTestCase
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
