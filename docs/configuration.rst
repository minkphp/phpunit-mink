Configuring Browser
===================
The browser needs to be configured in a test case before being able to access `Mink`_ session.
All possible ways of browser configuration are described below.

Per Test Configuration
^^^^^^^^^^^^^^^^^^^^^^
It is possible to configure browser individually for each test within a test case by creating
an instance of ``\aik099\PHPUnit\BrowserConfiguration\BrowserConfiguration`` class in ``setUp``
method in of test case class and setting it via ``setBrowser`` method.

.. literalinclude:: examples/configuration/config_via_setup_method.php
   :linenos:
   :emphasize-lines: 11,16,25,34-38,41

Per Test Case Configuration
^^^^^^^^^^^^^^^^^^^^^^^^^^^
In case, when all tests in a test case share same browser configuration it's easier to specify it via
static ``$browsers`` property (array, where each item represents a single browser
configuration) in that test case class.

.. literalinclude:: examples/configuration/config_via_browsers_property.php
   :linenos:
   :emphasize-lines: 8,9,16

.. note:: When several browser configurations are specified in ``$browsers`` array, then each test
          in a test case will be executed against each of browser configurations.

Browser Session Sharing
^^^^^^^^^^^^^^^^^^^^^^^
As a benefit of shared (per test case) browser configuration, that was described above is an ability
to not only share browser configuration, that is used to create `Mink`_ session, but to actually share
created sessions between all tests in a single test case. This can be done by adding ``sessionStrategy``
option (line 14) to the browser configuration.

.. literalinclude:: examples/configuration/per_test_case_browser_config.php
       :linenos:
       :emphasize-lines: 15

Selecting the Mink Driver
^^^^^^^^^^^^^^^^^^^^^^^^^
With the help of ``driver`` and ``driverOptions`` browser configuration settings (since v2.1.0) it's possible to
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
``driver``               Mink driver name (defaults to ``selenium2``, since v2.1.0)
``driverOptions``        Mink driver specific options (since v2.1.0)
``host``                 host, where driver's server is located (defaults to ``localhost``)
``port``                 port, on which driver's server is listening for incoming connections (determined by driver)
``timeout``              connection timeout of the server in seconds ('selenium2' driver only, defaults to ``60``)
``browserName``          name of browser to use (e.g. ``firefox``, ``chrome``, etc., defaults to ``firefox``)
``desiredCapabilities``  parameters, that allow to fine-tune browser and other 'selenium2' driver options (e.g. 'tags',
                         'project', 'os', 'version')
``baseUrl``              base url of website, that is tested
``sessionStrategy``      used session strategy (defaults to ``isolated``)
``type``                 type of configuration (defaults to ``default``, but can also be ``saucelabs`` or ``browserstack``)
``apiUsername``          API username of used service (applicable to 'saucelabs' and 'browserstack' browser configurations)
``apiKey``               API key of used service (applicable to 'saucelabs' and 'browserstack' browser configurations)
=======================  ==================================================================================================

There are also corresponding setters (e.g. ``setHost``) and getters (e.g. ``getHost``) for each of mentioned
above settings, that allow to individually change them from ``setUp`` method before test has started.

.. _`Mink`: https://github.com/Behat/Mink
