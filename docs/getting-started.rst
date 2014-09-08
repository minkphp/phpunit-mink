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
   :emphasize-lines: 5,8,20,34

Using "Sauce Labs"
^^^^^^^^^^^^^^^^^^
When using `Sauce Labs <https://saucelabs.com/>`_ account to perform Selenium server-based testing you need to
specify following settings:

* ``'type' => 'saucelabs'``
* ``'api_username' => '...'``
* ``'api_key' => '...'``

instead of ``host`` and ``port`` settings. In all other aspects everything will work the same as if all
tests were running locally.

.. literalinclude:: examples/getting-started/sauce_labs.php
   :linenos:
   :emphasize-lines: 11-13

.. _`Mink`: https://github.com/Behat/Mink
