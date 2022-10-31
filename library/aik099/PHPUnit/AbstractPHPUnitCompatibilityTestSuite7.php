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


use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;

if ( version_compare($runner_version, '7.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 6
	 *
	 * @internal
	 * @codeCoverageIgnore
	 */
	abstract class AbstractPHPUnitCompatibilityTestSuite extends TestSuite
	{

		use TAbstractPHPUnitCompatibilityTestSuite;

		/**
		 * @inheritDoc
		 */
		public function run(TestResult $result = null)
		{
			return $this->runCompatibilized($result);
		}

		/**
		 * @inheritDoc
		 */
		protected function tearDown()
		{
			$this->tearDownCompatibilized();
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
	abstract class AbstractPHPUnitCompatibilityTestSuite extends TestSuite
	{

		use TAbstractPHPUnitCompatibilityTestSuite;

		/**
		 * @inheritDoc
		 */
		public function run(TestResult $result = null): TestResult
		{
			return $this->runCompatibilized($result);
		}

		/**
		 * @inheritDoc
		 */
		protected function tearDown(): void
		{
			$this->tearDownCompatibilized();
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
	abstract class AbstractPHPUnitCompatibilityTestSuite extends TestSuite
	{

		use TAbstractPHPUnitCompatibilityTestSuite;

		/**
		 * @inheritDoc
		 */
		public function run(TestResult $result = null): TestResult
		{
			return $this->runCompatibilized($result);
		}

		/**
		 * For PHPUnit < 8.2.0.
		 *
		 * @inheritDoc
		 */
		protected function tearDown(): void
		{
			$this->tearDownCompatibilized();
		}

	}
}
