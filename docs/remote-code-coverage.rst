Remote Code Coverage
====================
Browser tests are executed on different machine, then one, where code coverage information is collected
(and tests are executed). To solve that problem this library uses remote coverage collection. Following
steps needs to be performed before using this feature:

On Remote Server
^^^^^^^^^^^^^^^^
This is web-server, where website used in tests is located.

#. Install `Xdebug <http://xdebug.org/>`_ PHP extension on web-server
#. Copy ``library/aik099/PHPUnit/RemoteCoverage/RemoteCoverageTool.php`` into web-server's DocumentRoot directory.
#. Include following code before your application bootstraps:

.. code-block:: php

    <?php

    require_once 'RemoteCoverageTool.php';
    \aik099\PHPUnit\RemoteCoverage\RemoteCoverageTool::init();

On Test Machine
^^^^^^^^^^^^^^^
This is machine, where PHPUnit tests are being executed.

Following code needs to be placed in the ``setUp`` method of the test case class (that extends ``BrowserTestCase``
class) to enable remote coverage information collection:

.. code-block:: php

    <?php

    // "host" should be replaced with web server's url
    $this->setRemoteCoverageScriptUrl('http://host/');

How This Works
^^^^^^^^^^^^^^
#. each test sets a special cookie on website under test
#. when cookie is present, then ``RemoteCoverageTool.php`` script collects coverage information and stores it on disk
#. once test finishes, then ``http://host/?rct_mode=output`` url is accessed on remote server, which in turn returns collected coverage information
#. remote coverage information is then joined with coverage information collected locally on test machine
