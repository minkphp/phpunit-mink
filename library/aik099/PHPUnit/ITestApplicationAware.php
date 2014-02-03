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


/**
 * Interface to indicate that test case or test suite class understands TestApplication.
 */
interface ITestApplicationAware
{

	/**
	 * Sets application.
	 *
	 * @param TestApplication $application The application.
	 *
	 * @return void
	 */
	public function setApplication(TestApplication $application);

}
