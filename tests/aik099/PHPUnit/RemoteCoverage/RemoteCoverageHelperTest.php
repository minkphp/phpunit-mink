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


use aik099\PHPUnit\AbstractPHPUnitCompatibilityTestCase;
use aik099\PHPUnit\RemoteCoverage\RemoteCoverageHelper;
use aik099\PHPUnit\RemoteCoverage\RemoteUrl;
use Mockery as m;
use Mockery\MockInterface;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class RemoteCoverageHelperTest extends AbstractPHPUnitCompatibilityTestCase
{

	use ExpectException, AssertIsType;

	/**
	 * Remote URL.
	 *
	 * @var RemoteUrl|MockInterface
	 */
	private $_remoteUrl;

	/**
	 * Remote coverage helper.
	 *
	 * @var RemoteCoverageHelper
	 */
	private $_remoteCoverageHelper;

	/**
	 * Prepares test.
	 *
	 * @before
	 */
	protected function setUpTest()
	{
		$this->_remoteUrl = m::mock('aik099\\PHPUnit\\RemoteCoverage\\RemoteUrl');
		$this->_remoteCoverageHelper = new RemoteCoverageHelper($this->_remoteUrl);
	}

	/**
	 * Test description.
	 *
	 * @param string $coverage_script_url Url.
	 * @param string $test_id             Test ID.
	 *
	 * @return void
	 * @dataProvider createUrlErrorDataProvider
	 */
	public function testCreateUrlError($coverage_script_url, $test_id)
	{
		$this->expectException('InvalidArgumentException');

		$this->_remoteCoverageHelper->get($coverage_script_url, $test_id);
	}

	/**
	 * Returns url that end up badly.
	 *
	 * @return array
	 */
	public function createUrlErrorDataProvider()
	{
		return array(
			array('', 'test-id'),
			array('coverage-url', ''),
			array('', ''),
		);
	}

	/**
	 * Test description.
	 *
	 * @param string $coverage_script_url Coverage script URL.
	 * @param string $test_id             Test ID.
	 * @param string $expected_url        Expected URL to be queried.
	 *
	 * @return void
	 * @dataProvider createUrlDataProvider
	 */
	public function testCreateUrl($coverage_script_url, $test_id, $expected_url)
	{
		$this->_remoteUrl
			->shouldReceive('getPageContent')
			->with($expected_url)
			->once()
			->andReturn(false);

		$result = $this->_remoteCoverageHelper->get($coverage_script_url, $test_id);

		if ( \class_exists('\SebastianBergmann\CodeCoverage\RawCodeCoverageData') ) {
			$this->assertInstanceOf('\SebastianBergmann\CodeCoverage\RawCodeCoverageData', $result);
			$this->assertCount(0, $result->lineCoverage());
		}
		else {
			$this->assertIsArray($result);
			$this->assertCount(0, $result);
		}
	}

	/**
	 * Returns url that does valid call.
	 *
	 * @return array
	 */
	public function createUrlDataProvider()
	{
		return array(
			array('http://host', 'test-id', 'http://host?rct_mode=output&PHPUNIT_MINK_TEST_ID=test-id'),
			array('http://host?p1=v1', 'test-id', 'http://host?p1=v1&rct_mode=output&PHPUNIT_MINK_TEST_ID=test-id'),
		);
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testReturnedCoverageNotASerializedArray()
	{
		$this->expectException('RuntimeException');

		$this->_remoteUrl
			->shouldReceive('getPageContent')
			->once()
			->andReturn('');

		$this->_remoteCoverageHelper->get('A', 'B');
	}

	/**
	 * Test description.
	 *
	 * @return void
	 */
	public function testValidCoverageIsReturned()
	{
		$fixture_folder = __DIR__ . '/../Fixture';

		$this->_remoteUrl
			->shouldReceive('getPageContent')
			->once()
			->andReturn(file_get_contents($fixture_folder . '/coverage_data.txt'));

		$content = $this->_remoteCoverageHelper->get('A', 'B');
		$class_source_file = realpath($fixture_folder . '/DummyClass.php');

		$expected = array(
			3 => 1,
			6 => 1,
			7 => -2,
			11 => -1,
			12 => -2,
			14 => 1,
		);

		if ( \class_exists('\SebastianBergmann\CodeCoverage\RawCodeCoverageData') ) {
			$this->assertEquals(array($class_source_file => $expected), $content->lineCoverage());
		}
		else {
			$this->assertEquals($expected, $content[$class_source_file]);
		}
	}

}
