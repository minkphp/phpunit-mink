<?php

namespace aik099\PHPUnit;

use Behat\Mink\Exception\UnsupportedDriverActionException;

if (version_compare(\PHPUnit_Runner_Version::id(), '5.0.0', '>=')) {
    /**
     * Implementation of the skipping for UnsupportedDriverActionException for PHPUnit 5+
     *
     * This code should be moved back to \aik099\BrowserTestCase when dropping support for
     * PHP 5.5 and older, as PHPUnit 4 won't be needed anymore.
     *
     * Class SkippingUnsupportedTestCase
     *
     * @internal
     *
     * @package aik099\PHPUnit
     */
    class SkippingUnsupportedTestCase extends \PHPUnit_Framework_TestCase
    {
        /**
         * This method is called when a test method did not execute successfully.
         *
         * @param \Exception|\Throwable $e
         *
         * @return void
         */
        protected function onNotSuccessfulTest($e)
        {
            if ($e instanceof UnsupportedDriverActionException) {
                $this->markTestSkipped($e->getMessage());
            }

            parent::onNotSuccessfulTest($e);
        }
    }
} else {
    /**
     * Class SkippingUnsupportedTestCase
     *
     * Implementation of the skipping for UnsupportedDriverActionException for PHPUnit 4
     *
     * @internal
     *
     * @package aik099\PHPUnit
     */
    class SkippingUnsupportedTestCase extends \PHPUnit_Framework_TestCase
    {
        /**
         * This method is called when a test method did not execute successfully.
         *
         * @param \Exception $e Exception.
         *
         * @return void
         */
        protected function onNotSuccessfulTest(\Exception $e)
        {
            if ($e instanceof UnsupportedDriverActionException) {
                $this->markTestSkipped($e->getMessage());
            }

            parent::onNotSuccessfulTest($e);
        }
    }
}
