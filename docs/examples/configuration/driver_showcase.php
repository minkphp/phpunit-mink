<?php

use aik099\PHPUnit\BrowserTestCase;

class DriverShowCaseTest extends BrowserTestCase
{

    public static $browsers = array(
        array(
            'driver' => 'goutte',

            // Defaults for this driver.
            'driverOptions' => array(
                'server_parameters' => array(),
                'guzzle_parameters' => array(),
            ),

        ),
        array(
            'driver' => 'sahi',

            // Defaults for this driver.
            'port' => 9999,
            'driverOptions' => array(
                'sid' => null,
                'limit' => 600,
                'browser' => null,
            ),
        ),

        array(
            'driver' => 'selenium2',

            // Defaults for this driver.
            'port' => 4444,
            'driverOptions' => array(),
        ),

        array(
            'driver' => 'zombie',

            // Defaults for this driver.
            'port' => 8124,
            'driverOptions' => array(
                'node_bin' => 'node',
                'server_path' => null,
                'threshold' => 2000000,
                'node_modules_path' => '',
            ),
        ),
    );

}
