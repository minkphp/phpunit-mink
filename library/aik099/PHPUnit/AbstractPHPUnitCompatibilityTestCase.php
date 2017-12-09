<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit;


use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

/**
 * Implementation for PHPUnit 5+
 *
 * This code should be moved back to aik099\PHPUnit\BrowserTestCase when dropping support for
 * PHP 5.5 and older, as PHPUnit 4 won't be needed anymore.
 *
 * @internal
 */
abstract class AbstractPHPUnitCompatibilityTestCase extends TestCase
{

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param \Throwable $e Exception.
     *
     * @return void
     */
    protected function onNotSuccessfulTest(\Throwable $e)
    {
        $this->onNotSuccessfulTestCompatibilized($e);

        parent::onNotSuccessfulTest($e);
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param \Throwable $e Exception.
     *
     * @return void
     */
    abstract protected function onNotSuccessfulTestCompatibilized(\Throwable $e);

}
