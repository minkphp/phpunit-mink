Configuring Browser
===================
The browser needs to be configured in a test case before being able to access the `Mink`_ session.
All possible ways of the browser configuration are described below.

Per Test Configuration
^^^^^^^^^^^^^^^^^^^^^^
It is possible to configure browser individually for each test within a test case by creating
an instance of the ``\aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration`` class in the
``setUpTest`` method of test case class and setting it through the ``setBrowser`` method.

.. literalinclude:: examples/configuration/config_via_setup_method.php
   :linenos:
   :emphasize-lines: 14,19,28,37-41,44

Per Test Case Configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^
In case, when all tests in a test case share same browser configuration it's easier to specify it via
the static ``$browsers`` property (array, where each element represents a single browser configuration)
in that test case class.

.. literalinclude:: examples/configuration/config_via_browsers_property.php
   :linenos:
   :emphasize-lines: 8,9,16

.. note:: When several browser configurations are specified in the ``$browsers`` array, then each test
          in a test case will be executed against each of the browser configurations.

Browser Session Sharing
^^^^^^^^^^^^^^^^^^^^^^^
As a benefit of the shared (per test case) browser configuration, that was described above is an ability
to not only share the browser configuration, that is used to create `Mink`_ session, but to actually share
created sessions between all tests in a single test case. This can be done by adding the ``sessionStrategy``
option (line 15) to the browser configuration.

.. literalinclude:: examples/configuration/per_test_case_browser_config.php
       :linenos:
       :emphasize-lines: 15,26,48

Selecting the Mink Driver
^^^^^^^^^^^^^^^^^^^^^^^^^
With the help of the ``driver`` and the ``driverOptions`` browser configuration settings (since v2.1.0) it's possible to
specify which `Mink`_ driver to use. This file demonstrates how to use each driver:

.. literalinclude:: examples/configuration/driver_showcase.php
       :linenos:
       :emphasize-lines: 10,20,32,40

Configuration Options
^^^^^^^^^^^^^^^^^^^^^
Each browser configuration consists of the following settings (all optional):

=======================  ==================================================================================================
Name                     Description
=======================  ==================================================================================================
``driver``               Mink driver name (defaults to the ``selenium2``, since v2.1.0)
``driverOptions``        Mink driver specific options (since v2.1.0)
``host``                 host, where driver's server is located (defaults to the ``localhost``)
``port``                 port, on which driver's server is listening for the incoming connections (determined by the driver)
``timeout``              connection timeout of the server in seconds ('selenium2' driver only, defaults to ``60``)
``browserName``          name of the browser to use (e.g. ``firefox``, ``chrome``, etc., defaults to the ``firefox``)
``desiredCapabilities``  parameters, that allow to fine-tune browser and other ``selenium2`` driver options (e.g. ``tags``,
                         ``project``, ``os``, ``version``)
``baseUrl``              base url of the website, that is tested
``sessionStrategy``      used session strategy (defaults to ``isolated``)
``type``                 type of the configuration (defaults to ``default``, but also can be ``saucelabs`` or ``browserstack``)
``apiUsername``          API username of the used service (applicable to the ``saucelabs`` and ``browserstack`` browser configurations)
``apiKey``               API key of the used service (applicable to ``saucelabs`` and ``browserstack`` browser configurations)
=======================  ==================================================================================================

There are also corresponding setters (e.g. ``setHost``) and getters (e.g. ``getHost``) for each of the mentioned
above settings, that allow to individually change them from the ``setUpTest`` method before test has started.

.. _`Mink`: https://github.com/minkphp/Mink
