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

if ( version_compare($runner_version, '7.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 6
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
		protected function onNotSuccessfulTest(\Throwable $t)
		{
			$this->onNotSuccessfulTestCompatibilized($t);

			parent::onNotSuccessfulTest($t);
		}

	}
}
elseif ( version_compare($runner_version, '8.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 7
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
		protected function onNotSuccessfulTest(\Throwable $t)/* The :void return type declaration that should be here would cause a BC issue */
		{
			$this->onNotSuccessfulTestCompatibilized($t);

			parent::onNotSuccessfulTest($t);
		}

	}
}
else {
	/**
	 * Implementation for PHPUnit 8+
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
		protected function onNotSuccessfulTest(\Throwable $t): void
		{
			$this->onNotSuccessfulTestCompatibilized($t);

			parent::onNotSuccessfulTest($t);
		}

	}
}
