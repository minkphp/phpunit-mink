<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit;


use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{

	/**
	 * @after
	 */
	protected function verifyMockeryExpectations()
	{
		if ( !\class_exists('Mockery') ) {
			return;
		}

		// Add Mockery expectations to assertion count.
		$container = \Mockery::getContainer();

		if ( $container !== null ) {
			$this->addToAssertionCount($container->mockery_getExpectationCount());
		}

		// Verify Mockery expectations.
		\Mockery::close();
	}

}
