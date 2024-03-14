Getting Started
===============
Below you'll find all the needed information to find your way across the library.

Installation
^^^^^^^^^^^^
The library can be installed using Composer like so:

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
#. sub-class the test case class from the ``\aik099\PHPUnit\BrowserTestCase`` class (line 5)
#. define used browser configurations in the static ``$browsers`` property of that class (line 8-16)
#. access the `Mink`_ session by calling the ``$this->getSession()`` method in your test (line 21)
#. access browser configuration by calling the ``$this->getBrowser()`` method in your test (line 35)

.. literalinclude:: examples/getting-started/general_test.php
   :linenos:
   :emphasize-lines: 5,8-16,21,35

Selenium in the Cloud
^^^^^^^^^^^^^^^^^^^^^
When using Selenium-based solution for the automated testing in the cloud (e.g. `Sauce Labs`_ or `BrowserStack`_)
you'll need to specify the following settings:

* ``'type' => 'saucelabs'`` or ``'type' => 'browserstack'``
* ``'apiUsername' => '...'``
* ``'apiKey' => '...'``

instead of the ``host`` and ``port`` settings. In all other aspects everything will work the same as if all
tests were running locally.

.. literalinclude:: examples/getting-started/cloud_selenium_configs.php
   :linenos:
   :emphasize-lines: 11-13,20-22

Continuous Integration
^^^^^^^^^^^^^^^^^^^^^^
When the website under test isn't publicly accessible, then:

#. secure tunnel needs to be created from the website under test to the server, that runs the tests
#. the created tunnel identifier needs to specified in the ``PHPUNIT_MINK_TUNNEL_ID`` environment variable

.. note:: Before v2.1.0 the environment variable was called ``TRAVIS_JOB_NUMBER``.

How to Create a Tunnel
----------------------
* SauceLabs: https://docs.saucelabs.com/secure-connections/sauce-connect-5/
* BrowserStack: https://www.browserstack.com/docs/automate/selenium/getting-started/php/local-testing

.. _`Mink`: https://github.com/minkphp/Mink
.. _`Sauce Labs`: https://saucelabs.com/
.. _`BrowserStack`: http://www.browserstack.com/
