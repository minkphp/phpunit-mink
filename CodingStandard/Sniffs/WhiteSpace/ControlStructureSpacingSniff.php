<?php
/**
 * CodingStandard_Sniffs_WhiteSpace_ControlStructureSpacingSniff.
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

/**
 * CodingStandard_Sniffs_WhiteSpace_ControlStructureSpacingSniff.
 *
 * Checks that control structures have the correct spacing around brackets.
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
class CodingStandard_Sniffs_WhiteSpace_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                  );

    /**
     * How many spaces should follow the opening bracket.
     *
     * @var int
     */
    public $requiredSpacesAfterOpen = 1;

    /**
     * How many spaces should precede the closing bracket.
     *
     * @var int
     */
    public $requiredSpacesBeforeClose = 1;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_IF,
                T_WHILE,
                T_FOREACH,
                T_FOR,
                T_SWITCH,
                T_DO,
                T_ELSE,
                T_ELSEIF,
               );

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->requiredSpacesAfterOpen   = (int) $this->requiredSpacesAfterOpen;
        $this->requiredSpacesBeforeClose = (int) $this->requiredSpacesBeforeClose;
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            return;
        }

        $this->checkBracketSpacing($phpcsFile, $stackPtr);
        $this->checkContentInside($phpcsFile, $stackPtr);
        $this->checkLeadingContent($phpcsFile, $stackPtr);
        $this->checkTrailingContent($phpcsFile, $stackPtr);

    }//end process()


    /**
     * Checks bracket spacing.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function checkBracketSpacing(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['parenthesis_opener']) === false) {
            return;
        }

        $parenOpener    = $tokens[$stackPtr]['parenthesis_opener'];
        $parenCloser    = $tokens[$stackPtr]['parenthesis_closer'];
        $spaceAfterOpen = 0;
        if ($tokens[($parenOpener + 1)]['code'] === T_WHITESPACE) {
            $spaceAfterOpen = strlen($tokens[($parenOpener + 1)]['content']);
        }

        if ($spaceAfterOpen !== $this->requiredSpacesAfterOpen) {
            $error = 'Expected %s spaces after opening bracket; %s found';
            $data  = array(
                      $this->requiredSpacesAfterOpen,
                      $spaceAfterOpen,
                     );
            $phpcsFile->addError($error, ($parenOpener + 1), 'SpacingAfterOpenBrace', $data);
        }

        if ($tokens[$parenOpener]['line'] === $tokens[$parenCloser]['line']) {
            $spaceBeforeClose = 0;
            if ($tokens[($parenCloser - 1)]['code'] === T_WHITESPACE) {
                $spaceBeforeClose = strlen($tokens[($parenCloser - 1)]['content']);
            }

            if ($spaceBeforeClose !== $this->requiredSpacesBeforeClose) {
                $error = 'Expected %s spaces before closing bracket; %s found';
                $data  = array(
                          $this->requiredSpacesBeforeClose,
                          $spaceBeforeClose,
                         );
                $phpcsFile->addError($error, ($parenCloser - 1), 'SpaceBeforeCloseBrace', $data);
            }
        }

    }//end checkBracketSpacing()


    /**
     * Checks content inside.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function checkContentInside(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens      = $phpcsFile->getTokens();
        $scopeOpener = $tokens[$stackPtr]['scope_opener'];
        $scopeCloser = $tokens[$stackPtr]['scope_closer'];

        $firstContent = $phpcsFile->findNext(
            T_WHITESPACE,
            ($scopeOpener + 1),
            null,
            true
        );

        if ($tokens[$firstContent]['line'] !== ($tokens[$scopeOpener]['line'] + 1)) {
            $error = 'Blank line found at start of control structure';
            $phpcsFile->addError($error, $scopeOpener, 'SpacingBeforeOpen');
        }

        $lastContent = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($scopeCloser - 1),
            null,
            true
        );

        if ($tokens[$lastContent]['line'] !== ($tokens[$scopeCloser]['line'] - 1)) {
            $error = 'Blank line found at end of control structure';
            $phpcsFile->addError($error, $scopeCloser, 'SpacingAfterClose');
        }

    }//end checkContentInside()


    /**
     * Checks leading content.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function checkLeadingContent(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $leadingContent = $phpcsFile->findPrevious(
            PHP_CodeSniffer_Tokens::$emptyTokens,
            ($stackPtr - 1),
            null,
            true
        );

        if ($tokens[$leadingContent]['code'] === T_OPEN_TAG) {
            // At the beginning of the script or embedded code.
            return;
        }

        if ($tokens[$leadingContent]['code'] === T_OPEN_CURLY_BRACKET) {
            // Another control structure's opening brace.
            if (isset($tokens[$leadingContent]['scope_condition']) === true) {
                $owner = $tokens[$leadingContent]['scope_condition'];
                if ($tokens[$owner]['code'] === T_FUNCTION) {
                    // The previous content is the opening brace of a function
                    // so normal function rules apply and we can ignore it.
                    return;
                }
            }

            if ($tokens[$leadingContent]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
                $error = 'Blank line found before control structure';
                $phpcsFile->addError($error, $stackPtr, 'LineBeforeOpen');
            }
        } else if ($tokens[$leadingContent]['code'] !== T_CLOSE_CURLY_BRACKET
            && $tokens[$leadingContent]['line'] === ($tokens[$stackPtr]['line'] - 1)
        ) {
            $error = 'No blank line found before control structure';
            $phpcsFile->addError($error, $stackPtr, 'NoLineBeforeOpen');
        }//end if

    }//end checkLeadingContent()


    /**
     * Checks trailing content.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function checkTrailingContent(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens      = $phpcsFile->getTokens();
        $scopeCloser = $tokens[$stackPtr]['scope_closer'];

        $trailingContent = $phpcsFile->findNext(
            PHP_CodeSniffer_Tokens::$emptyTokens,
            ($scopeCloser + 1),
            null,
            true
        );

        // If this token is closing a CASE or DEFAULT, we don't need the
        // blank line after this control structure.
        if (isset($tokens[$trailingContent]['scope_condition']) === true) {
            $condition = $tokens[$trailingContent]['scope_condition'];
            if ($tokens[$condition]['code'] === T_CASE
                || $tokens[$condition]['code'] === T_DEFAULT
            ) {
                return;
            }
        }

        if ($tokens[$trailingContent]['code'] === T_CLOSE_TAG) {
            // At the end of the script or embedded code.
            return;
        }

        if ($tokens[$trailingContent]['code'] === T_CLOSE_CURLY_BRACKET) {
            // Another control structure's closing brace.
            if (isset($tokens[$trailingContent]['scope_condition']) === true) {
                $owner = $tokens[$trailingContent]['scope_condition'];
                if ($tokens[$owner]['code'] === T_FUNCTION) {
                    // The next content is the closing brace of a function
                    // so normal function rules apply and we can ignore it.
                    return;
                }
            }

            if ($tokens[$trailingContent]['line'] !== ($tokens[$scopeCloser]['line'] + 1)) {
                $error = 'Blank line found after control structure';
                $phpcsFile->addError($error, $scopeCloser, 'LineAfterClose');
            }
        } else if ($tokens[$trailingContent]['code'] !== T_ELSE
            && $tokens[$trailingContent]['code'] !== T_ELSEIF
            && $tokens[$trailingContent]['line'] === ($tokens[$scopeCloser]['line'] + 1)
        ) {
            $error = 'No blank line found after control structure';
            $phpcsFile->addError($error, $scopeCloser, 'NoLineAfterClose');
        }//end if

    }//end checkTrailingContent()


}//end class

?>
