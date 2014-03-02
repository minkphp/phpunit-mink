<?php
/**
 * CodingStandard_Sniffs_Formatting_ItemAssignmentSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Peter Philipp <peter.philipp@cando-image.com>
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * CodingStandard_Sniffs_Formatting_ItemAssignmentSniff.
 *
 * Checks if the item assignment operator (=>) has
 * - a space before and after
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Peter Philipp <peter.philipp@cando-image.com>
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class CodingStandard_Sniffs_Formatting_ItemAssignmentSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_DOUBLE_ARROW);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $this->sniffElementItemAssignmentOperator($phpcsFile, $stackPtr, $tokens);

    }//end process()


    /**
     * Checks if there are spaces before and after the Assignment operators in the array
     * Enter description here ...
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     * @param array                $tokens    Tokens.
     *
     * @return void
     */
    protected function sniffElementItemAssignmentOperator(PHP_CodeSniffer_File $phpcsFile, $stackPtr, array $tokens)
    {
        if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
            $phpcsFile->addError('A whitespace must prefix the item assignment operator =>', $stackPtr);
        }

        if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
            $phpcsFile->addError('A whitespace must follow to the item assignment operator =>', $stackPtr);
        }

    }//end sniffElementItemAssignmentOperator()


    /**
     * Checks if the last item in the array is closed with a comma
     * If the array is written on one line there must also be a space
     * after the comma
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     * @param array                $tokens    Tokens.
     *
     * @return void
     */
    protected function sniffItemClosings(PHP_CodeSniffer_File $phpcsFile, $stackPtr, array $tokens)
    {
        $lastItem = $phpcsFile->findPrevious(
            array(T_WHITESPACE),
            ($tokens[$stackPtr]['parenthesis_closer'] - 1),
            $stackPtr,
            true
        );

        // Empty array.
        if ($lastItem === $tokens[$stackPtr]['parenthesis_opener']) {
            return;
        }

        // Check if the last item in the array has a "closing" comma.
        if ($tokens[$lastItem]['code'] !== T_COMMA) {
            $phpcsFile->addWarning('A comma followed by a whitespace should follow the last array item. Found: ' . $tokens[$lastItem]['content'], $lastItem);

            return;
        }

        // If the closing parenthesis is on the
        // same line as the last item there has to be a whitespace
        // after the comma.
        if ($tokens[$lastItem]['line'] === $tokens[$tokens[$stackPtr]['parenthesis_closer']]['line']
            && $tokens[($lastItem + 1)]['code'] !== T_WHITESPACE
        ) {
            $phpcsFile->addWarning('After the last comma in an array must be a whitespace', $lastItem);

            return;
        }

    }//end sniffItemClosings()


}//end class

?>
