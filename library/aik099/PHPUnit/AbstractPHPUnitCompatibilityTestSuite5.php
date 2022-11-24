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


use PHPUnit\Framework\TestSuite;

if ( version_compare($runner_version, '5.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 4
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
	    public function run(\PHPUnit_Framework_TestResult $result = null)
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
elseif ( version_compare($runner_version, '6.0.0', '<') ) {
	/**
	 * Implementation for PHPUnit 5
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
		public function run(\PHPUnit_Framework_TestResult $result = null)
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
