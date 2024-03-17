<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */


namespace aik099\PHPUnit\MinkDriver;


abstract class AbstractDriverFactory implements IMinkDriverFactory
{

	/**
	 * Throws an exception with driver installation instructions.
	 *
	 * @param string $class_name Driver class name.
	 *
	 * @return void
	 * @throws \RuntimeException When driver isn't installed.
	 */
	protected function assertInstalled($class_name)
	{
		if ( !class_exists($class_name) ) {
			throw new \RuntimeException(
				sprintf(
					'The "%s" driver package is not installed. Please follow installation instructions at %s.',
					$this->getDriverName(),
					$this->getDriverPackageUrl()
				)
			);
		}
	}

}
