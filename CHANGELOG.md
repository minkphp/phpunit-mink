# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- Specify failed PHPUnit assertion text to the BrowserStack ("reason" field)/SauceLabs("custom-data" field) test.
- Added the `$auto_create` parameter to the `BrowserTestCase::getSession` method, which allows to verify is session is already started.
- Added the `ISessionStrategy::isFreshSession` method to indicate fact, that previous `ISessionStrategy::session` call have created a new session instead of reusing a previously created one. Can be used to perform a login once per a test case class. 

### Changed
- Bumped minimum PHP version to 5.6.
- Changed default OS from "Windows 7" to "Windows 10" for BrowserStack/SauceLabs browser configurations.
- Allow using self-signed/invalid SSL certificates during testing on the SauceLabs by default.
- Rewritten library object communication mechanism (the event dispatcher is no longer used). Update any custom session strategy/browser configuration implementations.
- Reduce memory consumption by rewriting `SessionStrategyFactory` and `SessionStrategyManager` classes.
- (Not a BC break) Some public methods of the `BrowserTestCase` class are protected now. Affected methods: `setRemoteCoverageScriptUrl`, `setBrowser`, `getBrowser`, `setSessionStrategy`, `getSessionStrategy`, `getCollectCodeCoverageInformation`, `getRemoteCodeCoverageInformation`.
- (Not a BC break) Some protected properties of the `BrowserTestCase` class are private now. Affected properties: `sessionStrategyManager`, `remoteCoverageHelper`, `sessionStrategy`.
- Bumped minimal required `Behat/Mink` version to 1.8 (needed after `SessionProxy` class removal).

### Fixed
- Don't set remote code coverage collection cookies, when the remote code coverage script URL isn't specified.
- The `BrowserTestCase::onTestSuiteEnded` method was called for tests, excluded through the `--filter` option of the PHPUnit.

## [2.3.0] - 2022-11-24
### Changed
- Bumped minimum PHPUnit version to 4.8.35 or 5.4.3.
- Added support for PHPUnit 6.x, PHPUnit 7.x, PHPUnit 8.x and PHPUnit 9.x versions.
- Use namespaced class versions of PHPUnit.
- Bumped minimum PHP version to 5.4.7.
- Test case configuration method renamed from "BrowserTestCase::setUp" into "BrowserTestCase::setUpTest".

### Fixed
- Fixed "PHP Strict standards" notice when used with PHPUnit 5+.
- Fixed issue with BrowserStack, that caused PHPUnit test result not being reported into the BrowserStack due their API changes.

## [2.2.0] - 2016-06-26
### Added
- Added support for Guzzle 6 in `goutte` driver.

### Changed
- Start sessions only, when somebody requests them.
- Allow using PHPUnit 4.x or PHPUnit 5.x and Symfony 3.0.
- Dependency on Pimple is removed, which allowed library to be used on projects using any of Pimple version (even 1.0) themselves.

### Fixed
...

## [2.1.1] - 2015-08-01
### Fixed
- Session sharing (for tests in same test case) wasn't working when test suite consisted from tests using both session strategies (isolated and shared).

## [2.1.0] - 2015-05-06
### Added
- Complete integration with BrowserStack (includes tunnel creation).
- Tunnel identifier can be specified through `PHPUNIT_MINK_TUNNEL_ID`, when for Sauce Labs or BrowserStack browser configurations are used.
- Allow specifying Mink driver to use within browser configuration (the new `driver` parameter).

### Changed
- Allow library to be used in projects with either Pimple 2.x or Pimple 3.x installed.
- The tunnel isn't automatically created, when using Sauce Labs or BrowserStack browser configuration and running test suite on Travis CI.

### Fixed
- The Sauce Labs and BrowserStack unit tests were executed even, when their credentials weren't specified in `phpunit.xml` (affects contributors only).

## [2.0.1] - 2014-11-27
### Changed
- Remote code coverage collection is now disabled by default.

### Fixed
- Attempt to use `@dataProvider` annotation ended up in exception.

## [2.0.0] - 2014-08-09
### Added
- Added support for using custom browser configuration (the `BrowserConfigurationFactory::register` method).
- Adding BrowserStack testing service support (experimental).
- Allow running testing using "Sauce Labs" and "BrowserStack" on Travis CI.
- Allow using SauceConnect (secure tunnel creation to the Sauce Labs servers) on Travis CI, when Sauce Labs browser configuration is used.
- Added support for HHVM.

### Changed
- Changed default OS for Sauce Labs/BrowserStack from "Windows XP" to "Windows 7".

### Fixed
- Sessions were stopped prematurely, when test suite consisted of tests with different session strategies (e.g. one isolated and one shared).

## [1.1.0] - 2014-03-22
### Added
- Added `BrowserTestCase::createBrowserConfiguration` method for creating instance of browser configuration class based on given parameters.

### Changed
- Use DIC (dependency injection container) to organize interactions between library modules.
- When unknown parameters are specified during browser configuration creation an exception is thrown.
- The `SauceLabsBrowserConfiguration` class now would throw an exception, when supplied driver instance isn't of `Selenium2Driver` class.
- The remote code coverage code made more reusable/testable through usage of OOP approach.
- Allow using both PHPUnit 3.x and PHPUnit 4.x versions.

## [1.0.1] - 2013-11-12
### Changed
- Use official Mockery repository with protected method mocking support.

## [1.0.0] - 2013-07-13
### Added
- Initial release.

[Unreleased]: https://github.com/minkphp/phpunit-mink/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/minkphp/phpunit-mink/compare/v2.1.1...v2.2.0
[2.1.1]: https://github.com/minkphp/phpunit-mink/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/minkphp/phpunit-mink/compare/v2.0.1...v2.1.0
[2.0.1]: https://github.com/minkphp/phpunit-mink/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/minkphp/phpunit-mink/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/minkphp/phpunit-mink/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/minkphp/phpunit-mink/compare/v1.0.0...v1.0.1

