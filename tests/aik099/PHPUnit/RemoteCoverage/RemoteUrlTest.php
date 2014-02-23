<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\RemoteCoverage;


use aik099\PHPUnit\RemoteCoverage\RemoteUrl;

class RemoteUrlTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetPageContent()
	{
		$file = __DIR__ . '/../Fixture/coverage_data.txt';

		$remote_url = new RemoteUrl();
		$this->assertEquals(file_get_contents($file), $remote_url->getPageContent($file));
	}

}
