<?php
/**
 * CodingStandard_Sniffs_Formatting_SpaceOperatorSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Peter Philipp <peter.philipp@cando-image.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * CodingStandard_Sniffs_Formatting_SpaceOperatorSniff.
 *
 * Ensures there is a single space after a operator
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Peter Philipp <peter.philipp@cando-image.com>
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class CodingStandard_Sniffs_Formatting_SpaceOperatorSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
         $tokens = array_merge(
             PHP_CodeSniffer_Tokens::$assignmentTokens,
             PHP_CodeSniffer_Tokens::$equalityTokens,
             PHP_CodeSniffer_Tokens::$comparisonTokens
         );

         return $tokens;

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE
            || $tokens[($stackPtr + 1)]['content'] != ' '
        ) {
            $error = 'A operator statement must be followed by a single space';
            $phpcsFile->addError($error, $stackPtr);
        }
        if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE
            || $tokens[($stackPtr - 1)]['content'] != ' '
        ) {
            $error = 'There must be a single space before an operator statement';
            $phpcsFile->addError($error, $stackPtr);
        }

    }//end process()


}//end class

?>
