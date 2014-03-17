<?php
/**
 * CodingStandard_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff not found');
}

/**
 * CodingStandard_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * Ensures method names are correct depending on whether they are public
 * or private, and that functions are named correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class CodingStandard_Sniffs_NamingConventions_ValidFunctionNameSniff extends PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff
{

    protected $eventHandlerExclusions = array(
                                         'SetCustomQuery',
                                         'CheckPermission',
                                         'LoadItem',
                                         'ListPrepareQuery',
                                         'ItemPrepareQuery',
                                         'SetPagination',
                                         'SetSorting',
                                        );

    protected $tagProcessorExclusions = array('PrepareListElementParams');


    /**
     * Processes the tokens within the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     * @param int                  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = array($className.'::'.$methodName);

        // Is this a magic method. i.e., is prefixed with "__" ?
        if (preg_match('|^__|', $methodName) !== 0) {
            $magicPart = strtolower(substr($methodName, 2));
            if (in_array($magicPart, $this->magicMethods) === false) {
                 $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                 $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            }

            return;
        }

        // PHP4 constructors are allowed to break our rules.
        if ($methodName === $className) {
            return;
        }

        // PHP4 destructors are allowed to break our rules.
        if ($methodName === '_'.$className) {
            return;
        }

        $methodProps    = $phpcsFile->getMethodProperties($stackPtr);
        $isPublic       = ($methodProps['scope'] === 'private') ? false : true;
        $scope          = $methodProps['scope'];
        $scopeSpecified = $methodProps['scope_specified'];

        // If it's a private method, it must have an underscore on the front.
        if ($isPublic === false && $methodName{0} !== '_') {
            $error = 'Private method name "%s" must be prefixed with an underscore';
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $errorData);
            return;
        }

        // If it's not a private method, it must not have an underscore on the front.
        if ($isPublic === true && $scopeSpecified === true && $methodName{0} === '_') {
            $error = '%s method name "%s" must not be prefixed with an underscore';
            $data  = array(
                      ucfirst($scope),
                      $errorData[0],
                     );
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

        // If the scope was specified on the method, then the method must be
        // camel caps and an underscore should be checked for. If it wasn't
        // specified, treat it like a public method and remove the underscore
        // prefix if there is one because we cant determine if it is private or
        // public.
        $testMethodName = $methodName;
        if ($scopeSpecified === false && $methodName{0} === '_') {
            $testMethodName = substr($methodName, 1);
        }

        $methodParams = $phpcsFile->getMethodParameters($stackPtr);

        if (
            $this->isEventHandlerExclusion($className, $methodName, $methodParams) ||
            $this->isTagProcessorExclusion($className, $methodName, $methodParams)
        ) {
            return;
        }

        if (PHP_CodeSniffer::isCamelCaps($testMethodName, false, $isPublic, false) === false) {
            if ($scopeSpecified === true) {
                $error = '%s method name "%s" is not in camel caps format';
                $data  = array(
                          ucfirst($scope),
                          $errorData[0],
                         );
                $phpcsFile->addError($error, $stackPtr, 'ScopeNotCamelCaps', $data);
            } else {
                $error = 'Method name "%s" is not in camel caps format';
                $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $errorData);
            }

            return;
        }

    }//end processTokenWithinScope()


    /**
     * Determines if a method is an event in the event handler class.
     *
     * @param string $className    Class name.
     * @param string $methodName   Method name.
     * @param array  $methodParams Method parameters.
     *
     * @return bool
     */
    protected function isEventHandlerExclusion($className, $methodName, array $methodParams)
    {
        if (substr($className, -12) !== 'EventHandler') {
            // Not EventHandler class.
            return false;
        }

        $isEvent = substr($methodName, 0, 2) == 'On' && count($methodParams) === 1 && $methodParams[0]['name'] === '$event';

        return in_array($methodName, $this->eventHandlerExclusions) || $isEvent === true;

    }//end isEventHandlerExclusion()


    /**
     * Determines if a method is an tag in the tag processor class.
     *
     * @param string $className    Class name.
     * @param string $methodName   Method name.
     * @param array  $methodParams Method parameters.
     *
     * @return bool
     */
    protected function isTagProcessorExclusion($className, $methodName, array $methodParams)
    {
        if (substr($className, -12) !== 'TagProcessor') {
            // Not TagProcessor class.
            return false;
        }

        $isTag = count($methodParams) === 1 && $methodParams[0]['name'] === '$params';

        return in_array($methodName, $this->tagProcessorExclusions) || $isTag === true;

    }//end isTagProcessorExclusion()


}//end class

?>
