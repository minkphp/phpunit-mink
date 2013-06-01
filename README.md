# phpunit-mink
Extension for PHPUnit, that allows to write tests, that are using Mink with ease.

### Usage

1. create subclass from `\aik099\PHPUnit\BrowserTestCase` class
2. define used browser configurations in static `$browsers` property in that class
3. use `$this->getSession()` method to get access to Mink session

[Continue to Examples](docs/PHPUnit.md)

## Using Composer

1. Define the dependencies in your ```composer.json```:
```json
{
	"require": {
		"aik099/phpunit-mink": "dev-master"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/aik099/phpunit-mink"
		},
	]
}
```

2. Install/update your vendors:
```bash
$ curl http://getcomposer.org/installer | php
$ php composer.phar install
```