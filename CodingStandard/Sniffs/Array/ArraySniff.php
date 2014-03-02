<?php
/**
 * CodingStandard_Sniffs_Array_ArraySniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Peter Philipp <peter.philipp@cando-image.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * CodingStandard_Sniffs_Array_ArraySniff.
 *
 * Checks if the array's are styled in the Drupal way.
 * - Comma after the last array element
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Peter Philipp <peter.philipp@cando-image.com>
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class CodingStandard_Sniffs_Array_ArraySniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_ARRAY);

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
        $this->sniffItemClosings($phpcsFile, $stackPtr, $tokens);
    }//end process()

    /**
     * Checks if the last item in the array is closed with a comma
     * If the array is written on one line there must also be a space
     * after the comma
     *
     * @param $tokens
     */
    function sniffItemClosings(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        $lastItem = $phpcsFile->findPrevious(
            array(T_WHITESPACE),
            $tokens[$stackPtr]['parenthesis_closer']-1,
            $stackPtr,
            true
        );

        //empty array
        if ($lastItem == $tokens[$stackPtr]['parenthesis_opener']) {
            return;
        }

        //Inline array
        $isInlineArray = $tokens[$tokens[$stackPtr]['parenthesis_opener']]['line'] == $tokens[$tokens[$stackPtr]['parenthesis_closer']]['line'];

        //Check if the last item in a multiline array has a "closing" comma.
        if ($tokens[$lastItem]['code'] !== T_COMMA && !$isInlineArray) {
            $phpcsFile->addWarning('A comma should follow the last multiline array item. Found: ' . $tokens[$lastItem]['content'], $lastItem);
            return;
        }

        if ($tokens[$lastItem]['code'] == T_COMMA && $isInlineArray) {
            $phpcsFile->addWarning('Last item of an inline array must not followed by a comma', $lastItem);
            return;
        }
    }


}//end class

?>
