<?php
/**
 * This file is part of the phpunit-mink library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/phpunit-mink
 */

namespace tests\aik099\PHPUnit\Session;


use aik099\PHPUnit\Session\ISessionStrategyFactory;
use aik099\PHPUnit\Session\SessionStrategyFactory;
use tests\aik099\PHPUnit\TestCase\ApplicationAwareTestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class SessionStrategyFactoryTest extends ApplicationAwareTestCase
{

	use ExpectException;

	/**
	 * Session factory.
	 *
	 * @var SessionStrategyFactory
	 */
	private $_factory;

	/**
	 * @before
	 */
	protected function setUpTest()
	{
		parent::setUpTest();

		$this->_factory = new SessionStrategyFactory();
		$this->_factory->setApplication($this->application);
	}

	/**
	 * Test description.
	 *
	 * @param string $strategy_type Strategy type.
	 * @param string $service_id    Service ID.
	 *
	 * @return void
	 * @dataProvider createStrategyDataProvider
	 */
	public function testCreateStrategySuccess($strategy_type, $service_id)
	{
		$expected = 'OK';
		$this->expectFactoryCall($service_id, $expected);
		$this->assertEquals($expected, $this->_factory->createStrategy($strategy_type));
	}

	/**
	 * Returns possible strategies.
	 *
	 * @return array
	 */
	public function createStrategyDataProvider()
	{
		return array(
			array(ISessionStrategyFactory::TYPE_ISOLATED, 'isolated_session_strategy'),
			array(ISessionStrategyFactory::TYPE_SHARED, 'shared_session_strategy'),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testCreateStrategyFailure()
	{
		$this->expectException('InvalidArgumentException');

		$this->_factory->createStrategy('wrong');
	}

}
