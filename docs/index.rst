PHPUnit-Mink Documentation
==========================
This library is an extension for `PHPUnit <https://github.com/sebastianbergmann/phpunit>`_, that allows to write tests
with help of `Mink`_.

Overview
--------
This library allows to perform following things:

* use `Mink`_ for browser session control
* each test in a test case can use independent browser session
* all tests in a test case can share browser session between them
* Selenium server connection details are decoupled from tests using them
* perform individual browser configuration for each test in a test case
* support for `Sauce Labs <https://saucelabs.com/>`_
* remote code coverage collection

Each mentioned above features is described in more detail below.

Service Integrations
--------------------
|SauceLabs|_ |BrowserStack|_

.. |SauceLabs| image:: /assets/images/saucelabs_logo.png
.. _SauceLabs: https://saucelabs.com/

.. |BrowserStack| image:: /assets/images/browserstack_logo.png
.. _BrowserStack: http://www.browserstack.com/

.. _`Mink`: https://github.com/Behat/Mink

.. toctree::
   :maxdepth: 2

   getting-started
   configuration
   browser-aliases
   remote-code-coverage
