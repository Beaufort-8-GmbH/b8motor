<?php
declare(strict_types = 1);

/***************************************************************
*  Copyright notice
*
*  (c) 2018 - 2022 Feng Lu <lu@beaufort8.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

namespace B8\B8motor\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use \TYPO3\CMS\IndexedSearch\ViewHelpers\PageBrowsingViewHelper;

/**
 * Page browser for indexed search, and only useful here, as the
 * regular pagebrowser
 * so this is a cleaner "pi_browsebox" but not a real page browser
 * functionality
 * @internal
 */
class PageBrowsingOverwriteViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var string
     */
    protected static $prefixId = 'tx_indexedsearch';

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('maximumNumberOfResultPages', 'int', '', true);
        $this->registerArgument('numberOfResults', 'int', '', true);
        $this->registerArgument('resultsPerPage', 'int', '', true);
        $this->registerArgument('currentPage', 'int', '', false, 0);
        $this->registerArgument('freeIndexUid', 'int', '');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $maximumNumberOfResultPages = $arguments['maximumNumberOfResultPages'];
        $numberOfResults = $arguments['numberOfResults'];
        $resultsPerPage = $arguments['resultsPerPage'];
        $currentPage = $arguments['currentPage'];
        $freeIndexUid = $arguments['freeIndexUid'];

        if ($resultsPerPage <= 0) {
            $resultsPerPage = 10;
        }
        $pageCount = (int)ceil($numberOfResults / $resultsPerPage);
        // only show the result browser if more than one page is needed
        if ($pageCount === 1) {
            return '';
        }

        // Check if $currentPage is in range
        $currentPage = MathUtility::forceIntegerInRange($currentPage, 0, $pageCount - 1);

        $content = '';

        $disableButton = '';

        // prev page
        // show on all pages after the 1st one
        if ($currentPage > -1) {
            $label = '<svg class="icon icon-arrow-small" height="19" viewBox="0 0 10 19" width="10" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#icon-arrow-small" x="0" y="0"></use></svg>';
            $content .= '<li class="pager">' . self::makecurrentPageSelector_link($label, $currentPage - 1, $freeIndexUid) . '</li>';
        }
        // Check if $maximumNumberOfResultPages is in range
        $maximumNumberOfResultPages = MathUtility::forceIntegerInRange($maximumNumberOfResultPages, 1, $pageCount, 10);
        // Assume $currentPage is in the middle and calculate the index limits of the result page listing
        $minPage = $currentPage - (int)floor($maximumNumberOfResultPages / 2);
        $maxPage = $minPage + $maximumNumberOfResultPages - 1;
        // Check if the indexes are within the page limits
        if ($minPage < 0) {
            $maxPage -= $minPage;
            $minPage = 0;
        } elseif ($maxPage >= $pageCount) {
            $minPage -= $maxPage - $pageCount + 1;
            $maxPage = $pageCount - 1;
        }
        $pageLabel = LocalizationUtility::translate('displayResults.page', 'IndexedSearch');
        for ($a = $minPage; $a <= $maxPage; $a++) {
            $label = trim($pageLabel . ' ' . ($a + 1));
            $label = self::makecurrentPageSelector_link($label, $a, $freeIndexUid);
            if ($a === $currentPage) {
                $content .= '<li class="active">' . $label . '</li>';
            } else {
                $content .= '<li>' . $label . '</li>';
            }
        }

        // Check if is first page
        if($currentPage == $minPage) {
            $disableButton = 'dataTable-pagination--first-page';
        }

        // Check if is last page
        if($currentPage == $maxPage) {
            $disableButton = 'dataTable-pagination--last-page';
        }

        // next link
        if ($currentPage < $pageCount) {
            $label = '<svg class="icon icon-arrow-small" height="19" viewBox="0 0 10 19" width="10" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#icon-arrow-small" x="0" y="0"></use></svg>';
            $content .= '<li class="pager">'. self::makecurrentPageSelector_link($label, ($currentPage + 1), $freeIndexUid) . '</li>';
        }

        if($numberOfResults != 0) {
            return '<ul class="dataTable-pagination dataTable-pagination--active '.$disableButton.'">' . $content . '</ul>';
        }
    }

    /**
     * Used to make the link for the result-browser.
     * Notice how the links must resubmit the form after setting the new currentPage-value in a hidden formfield.
     *
     * @param string $str String to wrap in <a> tag
     * @param int $p currentPage value
     * @param string $freeIndexUid List of integers pointing to free indexing configurations to search. -1 represents no filtering, 0 represents TYPO3 pages only, any number above zero is a uid of an indexing configuration!
     * @return string Input string wrapped in <a> tag with onclick event attribute set.
     */
    protected static function makecurrentPageSelector_link($str, $p, $freeIndexUid)
    {
        $onclick = 'document.getElementById(' . GeneralUtility::quoteJSvalue(self::$prefixId . '_pointer') . ').value=' . GeneralUtility::quoteJSvalue($p) . ';';
        if ($freeIndexUid !== null) {
            $onclick .= 'document.getElementById(' . GeneralUtility::quoteJSvalue(self::$prefixId . '_freeIndexUid') . ').value=' . GeneralUtility::quoteJSvalue($freeIndexUid) . ';';
        }
        $onclick .= 'document.getElementById(' . GeneralUtility::quoteJSvalue(self::$prefixId) . ').submit();return false;';

        if (strtolower($str) === 'weiter') {
            return '<button onclick="' . htmlspecialchars($onclick) . '" aria-label="'.LocalizationUtility::translate('indexed_search.pageNext', 'B8motor').'">' . LocalizationUtility::translate('indexed_search.pageNext', 'B8motor') . '</button>';
        } elseif (strtolower($str)==="zurück") {
            return '<button onclick="' . htmlspecialchars($onclick) . '" aria-label="'.LocalizationUtility::translate('indexed_search.pageBack', 'B8motor').'">' . LocalizationUtility::translate('indexed_search.pageBack', 'B8motor') . '</button>';
        } else {
            return '<button onclick="' . htmlspecialchars($onclick) . '">' . $str . '</button>';
        }
    }
}
