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


if ( version_compare(\PHPUnit_Runner_Version::id(), '5.0.0', '>=') ) {
	/**
	 * Implementation for PHPUnit 5+
	 *
	 * This code should be moved back to aik099\PHPUnit\BrowserTestCase when dropping support for
	 * PHP 5.5 and older, as PHPUnit 4 won't be needed anymore.
	 *
	 * @internal
	 */
	abstract class AbstractPHPUnitCompatibilityTestCase extends \PHPUnit_Framework_TestCase
	{

		/**
		 * This method is called when a test method did not execute successfully.
		 *
		 * @param \Exception $e Exception.
		 *
		 * @return void
		 */
		protected function onNotSuccessfulTest($e)
		{
			$this->onNotSuccessfulTestCompatibilized($e);

			parent::onNotSuccessfulTest($e);
		}

		/**
		 * This method is called when a test method did not execute successfully.
		 *
		 * @param \Exception $e
		 *
		 * @return void
		 */
		abstract protected function onNotSuccessfulTestCompatibilized(\Exception $e);

	}
}
else {
	/**
	 * Implementation for PHPUnit 4
	 *
	 * @internal
	 */
	abstract class AbstractPHPUnitCompatibilityTestCase extends \PHPUnit_Framework_TestCase
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
			$this->onNotSuccessfulTestCompatibilized($e);

			parent::onNotSuccessfulTest($e);
		}

		/**
		 * This method is called when a test method did not execute successfully.
		 *
		 * @param \Exception $e
		 *
		 * @return void
		 */
		abstract protected function onNotSuccessfulTestCompatibilized(\Exception $e);

	}
}
