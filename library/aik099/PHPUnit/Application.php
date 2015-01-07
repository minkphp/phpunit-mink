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


use aik099\PHPUnit\TestSuite\TestSuiteFactory;

/**
 * Main application class.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 */
class Application
{

	/**
	 * Dependency injection container.
	 *
	 * @var DIContainer
	 */
	protected $container;

	/**
	 * Returns instance of strategy manager.
	 *
	 * @param DIContainer $container Dependency injection container.
	 *
	 * @return self
	 */
	public static function getInstance(DIContainer $container = null)
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
	 * @param DIContainer $container Dependency injection container.
	 */
	public function __construct(DIContainer $container = null)
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
	 * @return TestSuiteFactory
	 * @see    BrowserTestCase::suite()
	 */
	public function getTestSuiteFactory()
	{
		return $this->getObject('test_suite_factory');
	}

	/**
	 * Returns object from the container.
	 *
	 * @param string $service_id Name of the object in the container.
	 *
	 * @return \stdClass
	 */
	public function getObject($service_id)
	{
		return $this->container[$service_id];
	}

	/**
	 * Replaces object in the container.
	 *
	 * @param string   $service_id Name of the object in the container.
	 * @param callable $callable   The callable that will return the object.
	 * @param boolean  $is_factory The callable should be considered as a factory.
	 *
	 * @return callable Previous service version.
	 * @throws \InvalidArgumentException When attempt is made to replace non-existing service.
	 */
	public function replaceObject($service_id, $callable, $is_factory = false)
	{
		if ( !isset($this->container[$service_id]) ) {
			throw new \InvalidArgumentException('Service "' . $service_id . '" not found');
		}

		$backup = $this->container->raw($service_id);
		unset($this->container[$service_id]);
		$this->container[$service_id] = $is_factory ? $this->container->factory($callable) : $callable;

		return $backup;
	}

	/**
	 * Prevents cloning.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	private function __clone()
	{

	}

}
