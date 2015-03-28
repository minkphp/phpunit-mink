Getting Started
===============
Below you'll find all needed information to find your way across the library.

Installation
^^^^^^^^^^^^
Library can be installed using Composer like so:

1. define the dependencies in your ``composer.json``:

.. code-block:: json

    {
        "require": {
            "aik099/phpunit-mink": "~2.0"
        }
    }

2. install/update your vendors:

.. code-block:: bash

    $ curl http://getcomposer.org/installer | php
    $ php composer.phar install

Basic Usage
^^^^^^^^^^^
#. sub-class test case class from ``\aik099\PHPUnit\BrowserTestCase`` class (line 5)
#. define used browser configurations in static ``$browsers`` property of that class (line 8-21)
#. access `Mink`_ session by calling ``$this->getSession()`` method in your test (line 26)
#. access browser configuration by calling ``$this->getBrowser()`` method in your test (line 40)

.. literalinclude:: examples/getting-started/general_test.php
   :linenos:
   :emphasize-lines: 5,8,21,35

Selenium in Cloud
^^^^^^^^^^^^^^^^^
When using Selenium-based solution for automated testing in the cloud (e.g. `Sauce Labs`_ or `BrowserStack`_) you need to
specify following settings:

* ``'type' => 'saucelabs'`` or ``'type' => 'browserstack'``
* ``'apiUsername' => '...'``
* ``'apiKey' => '...'``

instead of ``host`` and ``port`` settings. In all other aspects everything will work the same as if all
tests were running locally.

.. literalinclude:: examples/getting-started/cloud_selenium_configs.php
   :linenos:
   :emphasize-lines: 11-13,19-21

Continuous Integration
^^^^^^^^^^^^^^^^^^^^^^
When website under test isn't publicly accessible, then:

#. secure tunnel needs to be created from website under test to server, that runs the tests
#. created tunnel identifier needs to specified in the ``PHPUNIT_MINK_TUNNEL_ID`` environment variable

.. note:: Before v2.1.0 the environment variable was called ``TRAVIS_JOB_NUMBER``.

How to Create a Tunnel
----------------------
* SauceLabs: https://docs.saucelabs.com/reference/sauce-connect/
* BrowserStack: http://www.browserstack.com/automate/php#setting-local-tunnel

.. _`Mink`: https://github.com/Behat/Mink
.. _`Sauce Labs`: https://saucelabs.com/
.. _`BrowserStack`: http://www.browserstack.com/
