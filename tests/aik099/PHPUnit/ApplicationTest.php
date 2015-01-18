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


use aik099\PHPUnit\Application;
use Mockery as m;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * Application.
	 *
	 * @var Application
	 */
	private $_application;

	/**
	 * Creates container for testing.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_application = new Application();
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testInstanceIsShared()
	{
		$this->assertSame(Application::getInstance(), Application::getInstance());
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetTestSuiteFactory()
	{
		$this->assertInstanceOf(
			'aik099\\PHPUnit\\TestSuite\\TestSuiteFactory',
			$this->_application->getTestSuiteFactory()
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testGetObject()
	{
		$this->assertInstanceOf('aik099\\PHPUnit\\Application', $this->_application->getObject('application'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testReplaceObjectSuccess()
	{
		$object = new \stdClass();
		$this->_application->replaceObject('application', function () use ($object) {
			return $object;
		});

		$this->assertSame($object, $this->_application->getObject('application'));
	}

	/**
	 * Test description.
	 *
	 * @return void
	 * @expectedException \InvalidArgumentException
	 */
	public function testReplaceObjectFailure()
	{
		$this->_application->replaceObject('bad_service', function () {

		});
	}

}
