<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace aik099\PHPUnit\Session;


use Behat\Mink\Session;

class SessionProxy extends Session
{

	/**
	 * Visit specified URL.
	 *
	 * @param string $url Url of the page.
	 *
	 * @return void
	 */
	public function visit($url)
	{
		if ( !$this->isStarted() ) {
			$this->start();
		}

		parent::visit($url);
	}

}
