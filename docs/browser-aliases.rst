Browser Aliases
===============
All previous examples demonstrate various ways how browser configuration can be defined, but they all have
same downside - server connection details stay hard-coded in test case classes. This could become very
problematic if:

* same test cases needs to be executed on different servers (e.g. each developer runs them on his own machine)
* due change in server connection details each test case class needs to be changed

To solve this problem a browser aliases were introduced. Basically a browser alias is predefined browser
configuration, that is available in the test case by it's alias. Here is how it can be used:

#. create base test case class, by extending ``BrowserTestCase`` class in the project
   with ``getBrowserAliases`` method in it
#. the ``getBrowserAliases`` method will return an associative array of a browser configurations (array key acts as alias name)
#. in any place, where browser configuration is defined use ``'alias' => 'alias_name_here'`` instead of actual browser configuration
#. feel free to override any part of configuration defined in alias

.. note:: Nested aliases are also supported.

.. literalinclude:: examples/browser_aliases.php
   :linenos:
