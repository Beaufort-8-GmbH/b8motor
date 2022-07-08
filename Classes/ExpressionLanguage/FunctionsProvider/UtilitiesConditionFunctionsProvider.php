<?php
namespace B8\B8motor\ExpressionLanguage\FunctionsProvider;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/***************************************************************
*  Copyright notice
*
*  (c) 2020 Feng Lu <lu@beaufort8.de>
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
class UtilitiesConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions(): array
    {
        return [
            $this->getExtensionLoadedFunction(),
        ];
    }

    /**
     * get extension loaded
     *
     * @return  ExpressionFunction
     */
    protected function getExtensionLoadedFunction(): ExpressionFunction
    {
        return new ExpressionFunction('loaded', function () {
            // Not implemented, we only use the evaluator
        }, function ($arguments, $extKey) {
            // var_dump(ExtensionManagementUtility::isLoaded($extKey), $extKey);exit;
            return ExtensionManagementUtility::isLoaded($extKey);
        });
    }
}