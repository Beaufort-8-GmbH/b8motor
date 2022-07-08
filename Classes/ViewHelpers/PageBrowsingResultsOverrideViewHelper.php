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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * renders the header of the results page
 * @internal
 */
class PageBrowsingResultsOverrideViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('numberOfResults', 'int', '', true);
        $this->registerArgument('resultsPerPage', 'int', '', true);
        $this->registerArgument('currentPage', 'int', '', false, 1);
    }

    /**
     * @param array $arguments
     * @param callable|\Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $numberOfResults = $arguments['numberOfResults'];
        $resultsPerPage = $arguments['resultsPerPage'];
        $currentPage = $arguments['currentPage'];

        $firstResultOnPage = $currentPage * $resultsPerPage + 1;
        $lastResultOnPage = $currentPage * $resultsPerPage + $resultsPerPage;
        $label = LocalizationUtility::translate('LLL:fileadmin/templates/ext/indexed_search/Language/locallang.xlf:displayResults');
        return sprintf($label, $firstResultOnPage, min([$numberOfResults, $lastResultOnPage]), $numberOfResults);
    }
}
