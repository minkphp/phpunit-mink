<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\RemoteCoverage;


/**
 * Class makes request to remote server and returns url.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class RemoteUrl
{

	/**
	 * Returns content of the page from given URL.
	 *
	 * @param string $url Page URL.
	 *
	 * @return string
	 */
	public function getPageContent($url)
	{
		return file_get_contents($url);
	}

}
