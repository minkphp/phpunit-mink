<?php
/**
 * CodingStandard_Sniffs_Array_ArraySniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Peter Philipp <peter.philipp@cando-image.com>
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * CodingStandard_Sniffs_Array_ArraySniff.
 *
 * Checks if the array's are styled in the Drupal way.
 * - Comma after the last array element
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Peter Philipp <peter.philipp@cando-image.com>
 * @link     http://pear.php.net/package/PHP_CodeSniffer
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
     * Checks if the last item in the array is closed with a comma.
     *
     * If the array is written on one line there must also be a space
     * after the comma.
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
        $arrayStart = $tokens[$stackPtr]['parenthesis_opener'];
        $arrayEnd   = $tokens[$arrayStart]['parenthesis_closer'];

        if ($arrayStart !== ($stackPtr + 1)) {
            $error = 'There must be no space between the Array keyword and the opening parenthesis';
            $phpcsFile->addError($error, $stackPtr, 'SpaceAfterKeyword');
        }

        // Check for empty arrays.
        $content = $phpcsFile->findNext(array(T_WHITESPACE), ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $phpcsFile->addError($error, $stackPtr, 'SpaceInEmptyArray');

                // We can return here because there is nothing else to check. All code
                // below can assume that the array is not empty.
                return;
            }
        }

        $lastItem = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($arrayEnd - 1), $stackPtr, true);

        // Empty array.
        if ($lastItem === $arrayStart) {
            return;
        }

        // Inline array.
        $isInlineArray = $tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line'];

        // Check if the last item in a multiline array has a "closing" comma.
        if ($tokens[$lastItem]['code'] !== T_COMMA && $isInlineArray === false) {
            $phpcsFile->addWarning('A comma should follow the last multiline array item. Found: '.$tokens[$lastItem]['content'], $lastItem);
            return;
        }

        if ($isInlineArray === true) {
            if ($tokens[$lastItem]['code'] === T_COMMA) {
                $phpcsFile->addWarning('Comma not allowed after last value in single-line array declaration', $lastItem);
                return;
            }

            // Inline array must not have spaces within parenthesis.
            if ($content !== ($arrayStart + 1)) {
                $error = 'Space found after opening parenthesis of Array';
                $phpcsFile->addError($error, $stackPtr, 'SpaceAfterOpen');
            }

            if ($lastItem !== ($arrayEnd - 1)) {
                $error = 'Space found before closing parenthesis of Array';
                $phpcsFile->addError($error, $stackPtr, 'SpaceAfterClose');
            }
        }

    }//end sniffItemClosings()


}//end class

?>
