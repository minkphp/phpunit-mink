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


use aik099\PHPUnit\TestSuite\TestSuiteBuilder;


/**
 * Main application class.
 *
 * @method \Mockery\Expectation shouldReceive
 */
class Application
{

	/**
	 * Dependency injection container.
	 *
	 * @var \Pimple
	 */
	protected $container;

	/**
	 * Returns instance of strategy manager.
	 *
	 * @param \Pimple $container Dependency injection container.
	 *
	 * @return self
	 */
	public static function getInstance(\Pimple $container = null)
	{
		static $instance = null;

		if ( null === $instance ) {
			$instance = new static($container);
		}

		return $instance;
	}

	/**
	 * Prevents direct instantiation.
	 *
	 * @param \Pimple $container Dependency injection container.
	 */
	private function __construct(\Pimple $container = null)
	{
		if ( !isset($container) ) {
			$container = new DIContainer();
		}

		$this->container = $container;
		$this->container->setApplication($this);
	}

	/**
	 * Returns test suite builder.
	 *
	 * @return TestSuiteBuilder
	 * @see    BrowserTestCase::suite()
	 */
	public function getTestSuiteBuilder()
	{
		return $this->getObject('test_suite_builder');
	}

	/**
	 * Returns object from the container.
	 *
	 * @param string $name Name of the object in the container.
	 *
	 * @return \stdClass
	 */
	public function getObject($name)
	{
		return $this->container[$name];
	}

	/**
	 * Prevents cloning.
	 *
	 * @return void
	 */
	private function __clone()
	{

	}

}
