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

if ( version_compare($runner_version, '5.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 4
	 *
	 * @internal
	 * @codeCoverageIgnore
	 */
	abstract class AbstractPHPUnitCompatibilityTestCase extends TestCase
	{

		use TAbstractPHPUnitCompatibilityTestCase;

		/**
		 * @inheritDoc
		 */
		protected function onNotSuccessfulTest(\Exception $e)
		{
			$this->onNotSuccessfulTestCompatibilized($e);

			parent::onNotSuccessfulTest($e);
		}

	}
}
elseif ( version_compare($runner_version, '6.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 5
	 *
	 * @internal
	 * @codeCoverageIgnore
	 */
	abstract class AbstractPHPUnitCompatibilityTestCase extends TestCase
	{

		use TAbstractPHPUnitCompatibilityTestCase;

		/**
		 * @inheritDoc
		 */
		protected function onNotSuccessfulTest($e)
		{
			$this->onNotSuccessfulTestCompatibilized($e);

			parent::onNotSuccessfulTest($e);
		}

	}
}
